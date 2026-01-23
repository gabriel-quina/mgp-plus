<?php

namespace App\Http\Controllers\Schools\Classrooms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schools\Classrooms\StoreLessonRequest;
use App\Models\{Classroom, Lesson, LessonAttendance, School, Teacher, User};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Schema};

class LessonController extends Controller
{
    private function resolveTeacherForUser(User $user): ?Teacher
    {
        // teachers.cpf
        if (Schema::hasTable('teachers') && Schema::hasColumn('teachers', 'cpf') && ! empty($user->cpf)) {
            $cpf = preg_replace('/\D+/', '', (string) $user->cpf) ?: null;
            if ($cpf) {
                $t = Teacher::query()->where('cpf', $cpf)->first();
                if ($t) {
                    return $t;
                }
            }
        }

        // teachers.email
        if (Schema::hasTable('teachers') && Schema::hasColumn('teachers', 'email') && ! empty($user->email)) {
            $t = Teacher::query()->where('email', $user->email)->first();
            if ($t) {
                return $t;
            }
        }

        return null;
    }

    private function hasSchoolAccess(User $user, School $school): bool
    {
        if ($user->is_master) {
            return true;
        }

        return $user->schoolRoleAssignments()
            ->where('school_id', $school->id)
            ->exists();
    }

    /**
     * Regra atual:
     * - master: pode (se não tiver Teacher vinculado, escolhe um Teacher)
     * - não-master: precisa ter acesso à escola + estar vinculado a Teacher
     *
     * Retorna: [Teacher|null $teacher, bool $teacherLocked, \Illuminate\Support\Collection|null $teachers]
     */
    private function lessonLaunchContext(School $school): array
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user) {
            abort(403, 'Não autenticado.');
        }

        if (! $this->hasSchoolAccess($user, $school)) {
            abort(403, 'Sem acesso a esta escola.');
        }

        $teacher = $this->resolveTeacherForUser($user);

        // Usuário normal: tem que ser professor (vinculado)
        if (! $user->is_master) {
            if (! $teacher) {
                abort(403, 'Usuário não está vinculado a um professor (Teacher).');
            }

            return [$teacher, true, null];
        }

        // Master: se tiver teacher vinculado, trava; senão, permite escolher teacher
        if ($teacher) {
            return [$teacher, true, null];
        }

        $teachers = Teacher::query()->orderBy('name')->get();

        return [null, false, $teachers];
    }

    public function index(School $school, Classroom $classroom)
    {
        $classroom->load(['school', 'schoolWorkshop.workshop', 'gradeLevels']);

        $lessons = $classroom->lessons()
            ->with('teacher')
            ->withCount('attendances')
            ->orderByDesc('taught_at')
            ->orderByDesc('created_at')
            ->paginate(30);

        /** @var User $user */
        $user = Auth::user();

        $teacher = $user ? $this->resolveTeacherForUser($user) : null;
        $hasSchoolAccess = $user ? $this->hasSchoolAccess($user, $school) : false;

        // ✅ sem canByRole: qualquer professor com acesso à escola pode lançar
        $canLaunch = $user && $hasSchoolAccess && ($user->is_master || (bool) $teacher);

        return view('schools.lessons.index', compact('school', 'classroom', 'lessons', 'canLaunch'));
    }

    public function create(Request $request, School $school, Classroom $classroom)
    {
        [$teacher, $teacherLocked, $teachers] = $this->lessonLaunchContext($school);

        $classroom->load(['school', 'schoolWorkshop.workshop', 'gradeLevels']);

        $taughtAt = Carbon::parse($request->input('taught_at', now()->toDateString()))->startOfDay();

        // Mantém seu padrão atual (00:00 do dia)
        $roster = $classroom->rosterAt($taughtAt->copy()->endOfDay());

        return view('schools.lessons.create', [
            'school' => $school,
            'classroom' => $classroom,
            'taughtAt' => $taughtAt,
            'roster' => $roster,
            'teacher' => $teacher,
            'teacherLocked' => $teacherLocked,
            'teachers' => $teachers,
        ]);
    }

    public function store(StoreLessonRequest $request, School $school, Classroom $classroom)
    {
        [$teacher, $teacherLocked, $teachers] = $this->lessonLaunchContext($school);

        $data = $request->validated();
        $taughtAt = Carbon::parse($data['taught_at'])->startOfDay();

        $roster = $classroom->rosterAt($taughtAt->copy()->endOfDay());
        $allowedEnrollmentIds = $roster->pluck('id')->map(fn ($id) => (int) $id)->all();

        $attendances = $data['attendances'] ?? [];
        $payloadEnrollmentIds = collect($attendances)->keys()->map(fn ($id) => (int) $id)->all();

        $diff = array_diff($payloadEnrollmentIds, $allowedEnrollmentIds);
        if (! empty($diff)) {
            return back()
                ->withErrors(['attendances' => 'Há alunos no lançamento que não pertencem à turma nesta data.'])
                ->withInput();
        }

        if (count($allowedEnrollmentIds) > 0) {
            $missing = array_diff($allowedEnrollmentIds, $payloadEnrollmentIds);
            if (! empty($missing)) {
                return back()
                    ->withErrors(['attendances' => 'Você precisa lançar a presença para todos os alunos da turma.'])
                    ->withInput();
            }
        }

        // Resolve teacher_id (NUNCA NULL)
        $teacherIdToUse = null;

        if ($teacherLocked) {
            $teacherIdToUse = $teacher?->id;
        } else {
            $teacherIdToUse = ! empty($data['teacher_id']) ? (int) $data['teacher_id'] : null;

            if (! $teacherIdToUse) {
                $teachersList = $teachers ?? Teacher::query()->orderBy('name')->get();
                if ($teachersList->count() === 1) {
                    $teacherIdToUse = (int) $teachersList->first()->id;
                }
            }
        }

        if (! $teacherIdToUse) {
            return back()
                ->withErrors(['teacher_id' => 'Selecione um professor para lançar a aula.'])
                ->withInput();
        }

        return DB::transaction(function () use ($classroom, $school, $teacherIdToUse, $taughtAt, $data, $attendances) {
            $lesson = $classroom->lessons()->create([
                'teacher_id' => $teacherIdToUse,
                'taught_at' => $taughtAt->toDateString(),
                'topic' => $data['topic'] ?? null,
                'notes' => $data['notes'] ?? null,
                'is_locked' => false,
            ]);

            if (! empty($attendances)) {
                $now = now();
                $rows = [];

                foreach ($attendances as $enrollmentId => $att) {
                    $status = $att['status'];
                    $present = $status === 'present';
                    $justification = $present ? null : ($att['justification'] ?? null);

                    $rows[] = [
                        'lesson_id' => $lesson->id,
                        'student_enrollment_id' => (int) $enrollmentId,
                        'present' => $present,
                        'justification' => $justification,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                LessonAttendance::upsert(
                    $rows,
                    ['lesson_id', 'student_enrollment_id'],
                    ['present', 'justification', 'updated_at']
                );
            }

            return redirect()
                ->route('schools.classrooms.lessons.show', [$school, $classroom, $lesson])
                ->with('success', 'Aula lançada com sucesso.');
        });
    }

    public function show(School $school, Classroom $classroom, Lesson $lesson)
    {
        abort_unless((int) $lesson->classroom_id === (int) $classroom->id, 404);

        $classroom->load(['school', 'schoolWorkshop.workshop', 'gradeLevels']);

        $lesson->load([
            'teacher',
            'attendances.enrollment.student',
            'attendances.enrollment.gradeLevel',
        ]);

        $roster = $classroom->rosterAt($lesson->taught_at->copy()->endOfDay());
        $attendanceByEnrollment = $lesson->attendances->keyBy('student_enrollment_id');

        return view('schools.lessons.show', compact(
            'school',
            'classroom',
            'lesson',
            'roster',
            'attendanceByEnrollment'
        ));
    }
}

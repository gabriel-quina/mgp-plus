<?php

namespace App\Http\Controllers\Schools;

use App\Http\Controllers\Controller;
use App\Models\{GradeLevel, School, Student, StudentEnrollment};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\{Rule, ValidationException};

class SchoolEnrollmentController extends Controller
{
    protected function allowedStatuses(): array
    {
        return StudentEnrollment::allowedStatuses();
    }

    protected function statusLabels(): array
    {
        return [
            StudentEnrollment::STATUS_PRE_ENROLLED => 'Pré-matrícula',
            StudentEnrollment::STATUS_ENROLLED => 'Matriculado',
            StudentEnrollment::STATUS_ACTIVE => 'Cursando',
            StudentEnrollment::STATUS_COMPLETED => 'Concluído',
            StudentEnrollment::STATUS_FAILED => 'Reprovado',
            StudentEnrollment::STATUS_TRANSFERRED => 'Transferido',
            StudentEnrollment::STATUS_DROPPED => 'Desistente',
            StudentEnrollment::STATUS_SUSPENDED => 'Suspenso',
        ];
    }

    public function index(School $school, Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $yrParam = $request->query('year');
        $yr = is_numeric($yrParam) ? (int) $yrParam : (int) now()->year;

        $sh = $request->query('shift');
        $sh = in_array($sh, ['morning', 'afternoon', 'evening'], true) ? $sh : null;

        $st = $request->query('status');
        $st = in_array($st, $this->allowedStatuses(), true) ? $st : null;

        $query = StudentEnrollment::query()
            ->where('student_enrollments.school_id', $school->id)
            ->leftJoin('students', 'students.id', '=', 'student_enrollments.student_id')
            ->leftJoin('grade_levels', 'grade_levels.id', '=', 'student_enrollments.grade_level_id')
            ->select('student_enrollments.*')
            ->with(['student', 'gradeLevel', 'school', 'originSchool'])
            ->where('student_enrollments.academic_year', $yr);

        if ($q !== '') {
            $cpfDigits = preg_replace('/\D+/', '', $q);
            $query->where(function ($qq) use ($q, $cpfDigits) {
                $qq->where('students.name', 'like', "%{$q}%")
                    ->orWhere('students.social_name', 'like', "%{$q}%")
                    ->orWhere('students.email', 'like', "%{$q}%");
                if ($cpfDigits !== '') {
                    $qq->orWhere('students.cpf', 'like', "%{$cpfDigits}%");
                }
            });
        }

        if ($sh) {
            $query->where('student_enrollments.shift', $sh);
        }
        if ($st) {
            $query->where('student_enrollments.status', $st);
        }

        $query
            ->orderBy('grade_levels.sequence')
            ->orderBy('grade_levels.name')
            ->orderBy('students.name');

        $enrollments = $query->paginate(20)->withQueryString();

        return view('schools.enrollments.index', [
            'school' => $school,
            'schoolNav' => $school,
            'enrollments' => $enrollments,
            'q' => $q,
            'yr' => $yr,
            'sh' => $sh,
            'st' => $st,
            'allowedStatuses' => $this->allowedStatuses(),
            'statusLabels' => $this->statusLabels(),
        ]);
    }

    public function show(School $school, StudentEnrollment $enrollment)
    {
        abort_unless((int) $enrollment->school_id === (int) $school->id, 404);

        $enrollment->load(['student', 'gradeLevel', 'school', 'originSchool']);

        return view('schools.enrollments.show', [
            'school' => $school,
            'schoolNav' => $school,
            'enrollment' => $enrollment,
            'statusLabels' => $this->statusLabels(),
        ]);
    }

    public function create(School $school)
    {
        $gradeLevels = GradeLevel::orderBy('order')->orderBy('name')->pluck('name', 'id');

        return view('schools.enrollments.create', [
            'school' => $school,
            'schoolNav' => $school,
            'gradeLevels' => $gradeLevels,
            'statusLabels' => $this->statusLabels(),
        ]);
    }

    public function store(School $school, Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'grade_level_id' => ['required', 'integer', 'exists:grade_levels,id'],
            'academic_year' => ['required', 'integer', 'min:1900', 'max:9999'],
            'shift' => ['nullable', Rule::in(['morning', 'afternoon', 'evening'])],
            'started_at' => ['nullable', 'date'],
            'status' => ['nullable', Rule::in([StudentEnrollment::STATUS_PRE_ENROLLED, StudentEnrollment::STATUS_ENROLLED])],
        ]);

        DB::transaction(function () use ($data, $school) {
            $student = Student::query()->whereKey($data['student_id'])->lockForUpdate()->firstOrFail();

            // Bloqueio: não pode estar cursando (active) em outra escola
            $active = StudentEnrollment::query()
                ->where('student_id', $student->id)
                ->where('status', StudentEnrollment::STATUS_ACTIVE)
                ->lockForUpdate()
                ->first();

            if ($active && (int) $active->school_id !== (int) $school->id) {
                $activeSchoolName = School::query()->whereKey($active->school_id)->value('name') ?? 'outra escola';
                throw ValidationException::withMessages([
                    'student_id' => "Aluno com matrícula cursando em {$activeSchoolName}. Encerrar/transferir antes.",
                ]);
            }

            // Evitar duplicidade no mesmo ano para a mesma escola
            $existsSameYear = StudentEnrollment::query()
                ->where('student_id', $student->id)
                ->where('school_id', $school->id)
                ->where('academic_year', (int) $data['academic_year'])
                ->exists();

            if ($existsSameYear) {
                throw ValidationException::withMessages([
                    'academic_year' => 'Já existe matrícula deste aluno nesta escola para este ano letivo.',
                ]);
            }

            StudentEnrollment::create([
                'student_id' => $student->id,
                'school_id' => $school->id,
                'grade_level_id' => $data['grade_level_id'],
                'academic_year' => (int) $data['academic_year'],
                'shift' => $data['shift'] ?? 'morning',

                // default: matriculado antes das aulas
                'status' => $data['status'] ?? StudentEnrollment::STATUS_ENROLLED,

                'started_at' => $data['started_at'] ?? null,
                'ended_at' => null,

                'transfer_scope' => StudentEnrollment::SCOPE_FIRST,
                'origin_school_id' => null,
            ]);
        });

        return redirect()
            ->route('schools.enrollments.index', $school)
            ->with('success', 'Matrícula criada.');
    }

    public function edit(School $school, StudentEnrollment $enrollment)
    {
        abort_unless((int) $enrollment->school_id === (int) $school->id, 404);

        $enrollment->load(['student', 'gradeLevel', 'school', 'originSchool']);

        return view('schools.enrollments.edit', [
            'school' => $school,
            'schoolNav' => $school,
            'enrollment' => $enrollment,
            'allowedStatuses' => $this->allowedStatuses(),
            'statusLabels' => $this->statusLabels(),
        ]);
    }

    public function update(School $school, StudentEnrollment $enrollment, Request $request)
    {
        abort_unless((int) $enrollment->school_id === (int) $school->id, 404);

        $data = $request->validate([
            'status' => ['required', Rule::in($this->allowedStatuses())],
            'started_at' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date'],
        ]);

        $newStatus = $data['status'];

        $update = [
            'status' => $newStatus,
            'started_at' => $data['started_at'] ?? $enrollment->started_at,
            'ended_at' => $data['ended_at'] ?? $enrollment->ended_at,
        ];

        if ($newStatus === StudentEnrollment::STATUS_ACTIVE && empty($update['started_at'])) {
            $update['started_at'] = now()->toDateString();
        }

        if (in_array($newStatus, [
            StudentEnrollment::STATUS_COMPLETED,
            StudentEnrollment::STATUS_FAILED,
            StudentEnrollment::STATUS_TRANSFERRED,
            StudentEnrollment::STATUS_DROPPED,
        ], true) && empty($update['ended_at'])) {
            $update['ended_at'] = now()->toDateString();
        }

        $enrollment->update($update);

        return redirect()
            ->route('schools.enrollments.index', $school)
            ->with('success', 'Matrícula atualizada.');
    }

    public function confirm(School $school, StudentEnrollment $enrollment)
    {
        abort_unless((int) $enrollment->school_id === (int) $school->id, 404);

        if ($enrollment->status !== StudentEnrollment::STATUS_PRE_ENROLLED) {
            return back()->withErrors('A matrícula não está em pré-matrícula.');
        }

        $enrollment->update(['status' => StudentEnrollment::STATUS_ENROLLED]);

        return back()->with('success', 'Pré-matrícula efetivada como matriculado.');
    }

    public function startCourse(School $school, StudentEnrollment $enrollment)
    {
        abort_unless((int) $enrollment->school_id === (int) $school->id, 404);

        if (! in_array($enrollment->status, [StudentEnrollment::STATUS_ENROLLED, StudentEnrollment::STATUS_PRE_ENROLLED], true)) {
            return back()->withErrors('Somente pré/matriculados podem iniciar como cursando.');
        }

        $enrollment->update([
            'status' => StudentEnrollment::STATUS_ACTIVE,
            'started_at' => $enrollment->started_at ?: now()->toDateString(),
        ]);

        return back()->with('success', 'Aluno marcado como cursando.');
    }

    public function generatePreEnrollments(School $school, Request $request)
    {
        $data = $request->validate([
            'from_year' => ['nullable', 'integer', 'min:1900', 'max:9999'],
        ]);

        $fromYear = (int) ($data['from_year'] ?? now()->year);
        $toYear = $fromYear + 1;

        DB::transaction(function () use ($school, $fromYear, $toYear) {
            $base = StudentEnrollment::query()
                ->where('school_id', $school->id)
                ->where('academic_year', $fromYear)
                ->whereIn('status', [
                    StudentEnrollment::STATUS_ENROLLED,
                    StudentEnrollment::STATUS_ACTIVE,
                    StudentEnrollment::STATUS_COMPLETED,
                ])
                ->lockForUpdate()
                ->get();

            foreach ($base as $enr) {
                $already = StudentEnrollment::query()
                    ->where('student_id', $enr->student_id)
                    ->where('school_id', $school->id)
                    ->where('academic_year', $toYear)
                    ->exists();

                if ($already) {
                    continue;
                }

                StudentEnrollment::create([
                    'student_id' => $enr->student_id,
                    'school_id' => $school->id,
                    'grade_level_id' => $enr->grade_level_id,
                    'academic_year' => $toYear,
                    'shift' => $enr->shift ?? 'morning',
                    'status' => StudentEnrollment::STATUS_PRE_ENROLLED,

                    'transfer_scope' => StudentEnrollment::SCOPE_INTERNAL,
                    'origin_school_id' => $school->id,

                    'started_at' => null,
                    'ended_at' => null,
                ]);
            }
        });

        return back()->with('success', "Pré-matrículas geradas para {$toYear} (quando não existiam).");
    }
}

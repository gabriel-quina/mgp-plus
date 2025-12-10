<?php

namespace App\Http\Controllers\Classrooms;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Lesson;
use App\Models\LessonAttendance;
use App\Models\StudentEnrollment;
use App\Models\Workshop;
use App\Models\WorkshopAllocation;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    /**
     * Tela de lançamento de aula + grade de presença.
     * Rota: GET /turmas/{classroom}/oficinas/{workshop}/aulas/criar
     */
    public function create(Classroom $classroom, Workshop $workshop)
    {
        // Garante que a oficina está vinculada a ESSA turma/subturma
        $classroom->loadMissing('school', 'gradeLevels', 'workshops');

        $workshopForClass = $classroom->workshops->firstWhere('id', $workshop->id);
        abort_if(! $workshopForClass, 404);

        // Pega os alunos do grupo certo (PAI x Subturma)
        $enrollments = $this->resolveEnrollments($classroom, $workshopForClass);

        $pageTitle = 'Lançar aula / presença';
        $headerTitle = $classroom->name;

        $contextLine = sprintf(
            'Turma: <strong>%s</strong> · Escola: <strong>%s</strong> · Ano letivo: <strong>%d</strong> · Turno: <strong>%s</strong>',
            e($classroom->name),
            e(optional($classroom->school)->name ?? '—'),
            $classroom->academic_year,
            $classroom->shift ?? '—',
        );

        $workshopLine = 'Oficina: <strong>'.e($workshopForClass->name).'</strong>';

        return view('lessons.create', [
            'classroom' => $classroom,
            'workshop' => $workshopForClass,
            'enrollments' => $enrollments,
            'pageTitle' => $pageTitle,
            'headerTitle' => $headerTitle,
            'contextLine' => $contextLine,
            'workshopLine' => $workshopLine,
        ]);
    }

    public function index(Classroom $classroom, Workshop $workshop)
    {
        // Garante que a oficina pertence a esse classroom (PAI ou Subturma)
        $classroom->loadMissing('school', 'gradeLevels', 'workshops');

        $workshopForClass = $classroom->workshops->firstWhere('id', $workshop->id);
        abort_if(! $workshopForClass, 404);

        // Lista de aulas desse grupo + oficina, mais contagem de presenças
        $lessons = Lesson::query()
            ->where('classroom_id', $classroom->id)
            ->where('workshop_id', $workshopForClass->id)
            ->withCount('attendances') // total_count = attendances_count
            ->withCount([
                'attendances as present_count' => function ($q) {
                    $q->where('present', true);
                },
            ])
            ->orderByDesc('taught_at')
            ->orderByDesc('id')
            ->paginate(10);

        // URL de voltar: PAI → oficina da turma; Subturma → tela da subturma
        if ($classroom->parent_classroom_id) {
            $backUrl = route('subclassrooms.show', [
                'parent' => $classroom->parent_classroom_id,
                'classroom' => $classroom->id,
            ]);
        } else {
            $backUrl = route('classrooms.workshops.show', [$classroom, $workshopForClass]);
        }

        return view('lessons.index', [
            'classroom' => $classroom,
            'workshop' => $workshopForClass,
            'lessons' => $lessons,
            'backUrl' => $backUrl,
        ]);
    }

    /**
     * Grava aula + presenças.
     * Rota: POST /turmas/{classroom}/oficinas/{workshop}/aulas
     */
    public function store(Request $request, Classroom $classroom, Workshop $workshop)
    {
        $classroom->loadMissing('school', 'gradeLevels', 'workshops');

        $workshopForClass = $classroom->workshops->firstWhere('id', $workshop->id);
        abort_if(! $workshopForClass, 404);

        $data = $request->validate([
            'taught_at' => ['required', 'date'],
            'topic' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'attendance' => ['nullable', 'array'], // attendance[enrollment_id] => "1"
        ]);

        $lesson = Lesson::create([
            'classroom_id' => $classroom->id,
            'workshop_id' => $workshopForClass->id,
            'taught_at' => $data['taught_at'],
            'topic' => $data['topic'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        // Mesma lógica de grupo usada na tela
        $enrollments = $this->resolveEnrollments($classroom, $workshopForClass);

        $presentIds = collect($data['attendance'] ?? [])
            ->keys()
            ->map(fn ($id) => (int) $id);

        $now = now();
        $rows = [];

        foreach ($enrollments as $enrollment) {
            $rows[] = [
                'lesson_id' => $lesson->id,
                'student_enrollment_id' => $enrollment->id,
                'present' => $presentIds->contains($enrollment->id),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        LessonAttendance::insert($rows);

        return redirect()
            ->route('classrooms.lessons.show', [$classroom, $workshopForClass, $lesson])
            ->with('status', 'Aula lançada com sucesso!');
    }

    /**
     * Tela de presença da aula.
     * Rota: GET /turmas/{classroom}/oficinas/{workshop}/aulas/{lesson}
     */
    public function show(Classroom $classroom, Workshop $workshop, Lesson $lesson)
    {
        abort_if(
            $lesson->classroom_id !== $classroom->id ||
            $lesson->workshop_id !== $workshop->id,
            404
        );

        $lesson->load([
            'classroom.school',
            'workshop',
            'attendances.enrollment.student',
            'attendances.enrollment.gradeLevel',
        ]);

        $attendances = $lesson->attendances
            ->sortBy(fn ($att) => mb_strtolower($att->enrollment->student->name));

        // Voltar: se é subturma → volta para subturma; se é PAI → volta para oficina da turma
        if ($classroom->parent_classroom_id) {
            // classroom = SUBTURMA; precisamos do ID do PAI
            $backUrl = route('subclassrooms.show', [
                'parent' => $classroom->parent_classroom_id,
                'classroom' => $classroom->id,
            ]);
        } else {
            $backUrl = route('classrooms.workshops.show', [$classroom, $workshop]);
        }

        return view('lessons.show', [
            'lesson' => $lesson,
            'attendances' => $attendances,
            'classroom' => $classroom,
            'workshop' => $workshop,
            'backUrl' => $backUrl,
        ]);
    }

    /**
     * Resolve o grupo de alunos da aula:
     * - Turma PAI: todos elegíveis (eligibleEnrollments)
     * - Subturma: apenas alocados via WorkshopAllocation (child_classroom_id + workshop_id)
     */
    protected function resolveEnrollments(Classroom $classroom, Workshop $workshop)
    {
        // TURMA PAI → usa turma inteira como grupo da oficina
        if (is_null($classroom->parent_classroom_id)) {
            if (! method_exists($classroom, 'eligibleEnrollments')) {
                return collect();
            }

            return $classroom->eligibleEnrollments()
                ->with(['student', 'gradeLevel'])
                ->get()
                ->sortBy(fn ($e) => mb_strtolower(optional($e->student)->name ?? ''))
                ->values();
        }

        // SUBTURMA → usa WorkshopAllocation (child_classroom_id + workshop_id)
        $allocatedIds = WorkshopAllocation::query()
            ->where('child_classroom_id', $classroom->id)
            ->where('workshop_id', $workshop->id)
            ->pluck('student_enrollment_id');

        if ($allocatedIds->isEmpty()) {
            return collect();
        }

        return StudentEnrollment::query()
            ->with(['student', 'gradeLevel', 'school'])
            ->join('students', 'students.id', '=', 'student_enrollments.student_id')
            ->whereIn('student_enrollments.id', $allocatedIds)
            ->orderBy('students.name')
            ->select('student_enrollments.*')
            ->get();
    }
}

<?php

namespace App\Http\Controllers\Classrooms;

use App\Http\Controllers\Controller;
use App\Models\{Classroom, Lesson, LessonAttendance, School, StudentEnrollment, Workshop, WorkshopAllocation};
use Illuminate\Http\Request;

class LessonController extends Controller
{
    /**
     * Tela de lançamento de aula + grade de presença.
     * Rota nova: GET /escolas/{school}/grupos/{classroom}/oficinas/{workshop}/aulas/criar
     * name: schools.lessons.create
     */
    public function create(School $school, Classroom $classroom, Workshop $workshop)
    {
        // Coerência de escopo
        abort_unless((int) $classroom->school_id === (int) $school->id, 404);

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
            'school' => $school,
            'classroom' => $classroom,
            'workshop' => $workshopForClass,
            'enrollments' => $enrollments,
            'pageTitle' => $pageTitle,
            'headerTitle' => $headerTitle,
            'contextLine' => $contextLine,
            'workshopLine' => $workshopLine,
        ]);
    }

    /**
     * Lista aulas do grupo + oficina.
     * Rota nova: GET /escolas/{school}/grupos/{classroom}/oficinas/{workshop}/aulas
     * name: schools.lessons.index
     */
    public function index(School $school, Classroom $classroom, Workshop $workshop)
    {
        abort_unless((int) $classroom->school_id === (int) $school->id, 404);

        $classroom->loadMissing('school', 'gradeLevels', 'workshops');

        $workshopForClass = $classroom->workshops->firstWhere('id', $workshop->id);
        abort_if(! $workshopForClass, 404);

        $lessons = Lesson::query()
            ->where('classroom_id', $classroom->id)
            ->where('workshop_id', $workshopForClass->id)
            ->withCount('attendances')
            ->withCount([
                'attendances as present_count' => function ($q) {
                    $q->where('present', true);
                },
            ])
            ->orderByDesc('taught_at')
            ->orderByDesc('id')
            ->paginate(10);

        // Voltar: contexto escola (grupo)
        $backUrl = route('schools.classrooms.show', [
            'school' => $school->id,
            'classroom' => $classroom->id,
        ]);

        return view('lessons.index', [
            'school' => $school,
            'classroom' => $classroom,
            'workshop' => $workshopForClass,
            'lessons' => $lessons,
            'backUrl' => $backUrl,
        ]);
    }

    /**
     * Grava aula + presenças.
     * Rota nova: POST /escolas/{school}/grupos/{classroom}/oficinas/{workshop}/aulas
     * name: schools.lessons.store
     */
    public function store(Request $request, School $school, Classroom $classroom, Workshop $workshop)
    {
        abort_unless((int) $classroom->school_id === (int) $school->id, 404);

        $classroom->loadMissing('school', 'gradeLevels', 'workshops');

        $workshopForClass = $classroom->workshops->firstWhere('id', $workshop->id);
        abort_if(! $workshopForClass, 404);

        $data = $request->validate([
            'taught_at' => ['required', 'date'],
            'topic' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'attendance' => ['nullable', 'array'],
        ]);

        $lesson = Lesson::create([
            'classroom_id' => $classroom->id,
            'workshop_id' => $workshopForClass->id,
            'taught_at' => $data['taught_at'],
            'topic' => $data['topic'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

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
            ->route('schools.lessons.show', [
                'school' => $school->id,
                'classroom' => $classroom->id,
                'workshop' => $workshopForClass->id,
                'lesson' => $lesson->id,
            ])
            ->with('status', 'Aula lançada com sucesso!');
    }

    /**
     * Tela de presença da aula.
     * Rota nova: GET /escolas/{school}/grupos/{classroom}/oficinas/{workshop}/aulas/{lesson}
     * name: schools.lessons.show
     */
    public function show(School $school, Classroom $classroom, Workshop $workshop, Lesson $lesson)
    {
        abort_unless((int) $classroom->school_id === (int) $school->id, 404);

        $classroom->loadMissing('workshops');

        $workshopForClass = $classroom->workshops->firstWhere('id', $workshop->id);
        abort_if(! $workshopForClass, 404);

        abort_if(
            $lesson->classroom_id !== $classroom->id ||
            $lesson->workshop_id !== $workshopForClass->id,
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

        $backUrl = route('schools.lessons.index', [
            'school' => $school->id,
            'classroom' => $classroom->id,
            'workshop' => $workshopForClass->id,
        ]);

        return view('lessons.show', [
            'school' => $school,
            'lesson' => $lesson,
            'attendances' => $attendances,
            'classroom' => $classroom,
            'workshop' => $workshopForClass,
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

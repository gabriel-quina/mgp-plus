<?php

namespace App\Http\Controllers\Schools\Classrooms;

use App\Http\Controllers\Controller;
use App\Models\{Classroom, Lesson, LessonAttendance, School};
use Illuminate\Http\Request;

class LessonController extends Controller
{
    /**
     * Tela de lançamento de aula + grade de presença.
     * Rota nova: GET /escolas/{school}/grupos/{classroom}/aulas/criar
     * name: schools.lessons.create
     */
    public function create(School $school, Classroom $classroom)
    {
        // Coerência de escopo
        abort_unless((int) $classroom->school_id === (int) $school->id, 404);

        $enrollments = $classroom->rosterAt(now());

        $pageTitle = 'Lançar aula / presença';
        $headerTitle = $classroom->name;

        $contextLine = sprintf(
            'Turma: <strong>%s</strong> · Escola: <strong>%s</strong> · Ano letivo: <strong>%d</strong> · Turno: <strong>%s</strong>',
            e($classroom->name),
            e(optional($classroom->school)->name ?? '—'),
            $classroom->academic_year_id,
            $classroom->shift ?? '—',
        );

        return view('lessons.create', [
            'school' => $school,
            'classroom' => $classroom,
            'enrollments' => $enrollments,
            'pageTitle' => $pageTitle,
            'headerTitle' => $headerTitle,
            'contextLine' => $contextLine,
        ]);
    }

    /**
     * Lista aulas do grupo + oficina.
     * Rota nova: GET /escolas/{school}/grupos/{classroom}/aulas
     * name: schools.lessons.index
     */
    public function index(School $school, Classroom $classroom)
    {
        abort_unless((int) $classroom->school_id === (int) $school->id, 404);

        $lessons = Lesson::query()
            ->where('classroom_id', $classroom->id)
            ->withCount('attendances')
            ->withCount([
                'attendances as present_count' => function ($q) {
                    $q->where('present', true);
                },
            ])
            ->orderByDesc('lesson_at')
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
            'lessons' => $lessons,
            'backUrl' => $backUrl,
        ]);
    }

    /**
     * Grava aula + presenças.
     * Rota nova: POST /escolas/{school}/grupos/{classroom}/aulas
     * name: schools.lessons.store
     */
    public function store(Request $request, School $school, Classroom $classroom)
    {
        abort_unless((int) $classroom->school_id === (int) $school->id, 404);

        $data = $request->validate([
            'lesson_at' => ['required', 'date'],
            'topic' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'attendance' => ['nullable', 'array'],
        ]);

        $lesson = Lesson::create([
            'classroom_id' => $classroom->id,
            'lesson_at' => $data['lesson_at'],
            'topic' => $data['topic'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        $enrollments = $classroom->rosterAt(\Carbon\Carbon::parse($data['lesson_at']));

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
                'lesson' => $lesson->id,
            ])
            ->with('status', 'Aula lançada com sucesso!');
    }

    /**
     * Tela de presença da aula.
     * Rota nova: GET /escolas/{school}/grupos/{classroom}/aulas/{lesson}
     * name: schools.lessons.show
     */
    public function show(School $school, Classroom $classroom, Lesson $lesson)
    {
        abort_unless((int) $classroom->school_id === (int) $school->id, 404);

        abort_if(
            $lesson->classroom_id !== $classroom->id,
            404
        );

        $lesson->load([
            'classroom.school',
            'attendances.enrollment.student',
            'attendances.enrollment.gradeLevel',
        ]);

        $attendances = $lesson->attendances
            ->sortBy(fn ($att) => mb_strtolower($att->enrollment->student->name));

        $backUrl = route('schools.lessons.index', [
            'school' => $school->id,
            'classroom' => $classroom->id,
        ]);

        return view('lessons.show', [
            'school' => $school,
            'lesson' => $lesson,
            'attendances' => $attendances,
            'classroom' => $classroom,
            'backUrl' => $backUrl,
        ]);
    }
}

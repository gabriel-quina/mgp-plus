<?php

namespace App\Http\Controllers\Classrooms;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentGrade;
use App\Models\Classroom;
use App\Models\StudentEnrollment;
use App\Models\Workshop;
use App\Models\WorkshopAllocation;
use App\Services\AssessmentStatsService;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    public function __construct(
        protected AssessmentStatsService $statsService,
    ) {}

    /**
     * Lista avaliações de um grupo (Turma PAI ou Subturma) + oficina.
     */
    public function index(Classroom $classroom, Workshop $workshop)
    {
        $classroom->loadMissing('school', 'gradeLevels', 'workshops');

        $workshopForClass = $classroom->workshops->firstWhere('id', $workshop->id);
        abort_if(! $workshopForClass, 404);

        $assessments = Assessment::query()
            ->where('classroom_id', $classroom->id)
            ->where('workshop_id', $workshopForClass->id)
            ->withCount('grades')
            ->orderByDesc('due_at')
            ->orderByDesc('id')
            ->paginate(10);

        if ($classroom->parent_classroom_id) {
            $backUrl = route('subclassrooms.show', [
                'parent' => $classroom->parent_classroom_id,
                'classroom' => $classroom->id,
            ]);
        } else {
            $backUrl = route('classrooms.workshops.show', [$classroom, $workshopForClass]);
        }

        return view('assessments.index', [
            'classroom' => $classroom,
            'workshop' => $workshopForClass,
            'assessments' => $assessments,
            'backUrl' => $backUrl,
        ]);
    }

    /**
     * Tela de criação + lançamento de notas de uma avaliação.
     */
    public function create(Classroom $classroom, Workshop $workshop)
    {
        $classroom->loadMissing('school', 'gradeLevels', 'workshops');

        $workshopForClass = $classroom->workshops->firstWhere('id', $workshop->id);
        abort_if(! $workshopForClass, 404);

        $enrollments = $this->resolveEnrollments($classroom, $workshopForClass);

        return view('assessments.create', [
            'classroom' => $classroom,
            'workshop' => $workshopForClass,
            'enrollments' => $enrollments,
        ]);
    }

    /**
     * Salva avaliação + notas.
     */
    public function store(Request $request, Classroom $classroom, Workshop $workshop)
    {
        $classroom->loadMissing('school', 'gradeLevels', 'workshops');

        $workshopForClass = $classroom->workshops->firstWhere('id', $workshop->id);
        abort_if(! $workshopForClass, 404);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'scale_type' => ['required', 'in:points,concept'],

            // 0–100 agora
            'max_points' => ['required', 'numeric', 'min:0', 'max:100'],

            'grades_points' => ['nullable', 'array'],
            'grades_points.*' => ['nullable', 'numeric', 'min:0', 'max:100'],

            'grades_concept' => ['nullable', 'array'],
            'grades_concept.*' => ['nullable', 'in:ruim,regular,bom,muito_bom,excelente'],
        ]);

        $assessment = Assessment::create([
            'classroom_id' => $classroom->id,
            'workshop_id' => $workshopForClass->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'due_at' => $data['due_at'] ?? null,
            'scale_type' => $data['scale_type'],
            'max_points' => $data['max_points'],
        ]);

        $enrollments = $this->resolveEnrollments($classroom, $workshopForClass);

        $pointsInput = collect($data['grades_points'] ?? []);
        $conceptInput = collect($data['grades_concept'] ?? []);

        // Se re-lançar, apaga as antigas
        $assessment->grades()->delete();

        $now = now();
        $rows = [];

        foreach ($enrollments as $enrollment) {
            if ($data['scale_type'] === 'points') {
                $rawPoints = $pointsInput->get($enrollment->id);

                if ($rawPoints === null || $rawPoints === '') {
                    continue;
                }

                $rows[] = [
                    'assessment_id' => $assessment->id,
                    'student_enrollment_id' => $enrollment->id,
                    'score_points' => (float) $rawPoints,
                    'score_concept' => null,
                    'notes' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            } else {
                $concept = $conceptInput->get($enrollment->id);

                if ($concept === null || $concept === '') {
                    continue;
                }

                $rows[] = [
                    'assessment_id' => $assessment->id,
                    'student_enrollment_id' => $enrollment->id,
                    'score_points' => null, // sem conversão pra pontos
                    'score_concept' => $concept,
                    'notes' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (! empty($rows)) {
            AssessmentGrade::insert($rows);
        }

        return redirect()
            ->route('classrooms.assessments.show', [$classroom, $workshopForClass, $assessment])
            ->with('status', 'Avaliação lançada com sucesso!');
    }

    /**
     * Mostra os detalhes de uma avaliação (com notas).
     */
    public function show(Classroom $classroom, Workshop $workshop, Assessment $assessment)
    {
        abort_if(
            $assessment->classroom_id !== $classroom->id ||
            $assessment->workshop_id !== $workshop->id,
            404
        );

        $assessment->load([
            'classroom.school',
            'workshop',
            'grades.enrollment.student',
            'grades.enrollment.gradeLevel',
        ]);

        // Stats via serviço
        $stats = $this->statsService->forAssessment($assessment);

        // Ordena as notas por nome do aluno
        $grades = $assessment->grades
            ->sortBy(fn ($g) => mb_strtolower($g->enrollment->student->name));

        $backUrl = route('classrooms.assessments.index', [$classroom, $workshop]);

        return view('assessments.show', [
            'assessment' => $assessment,
            'grades' => $grades,
            'classroom' => $classroom,
            'workshop' => $workshop,
            'backUrl' => $backUrl,
            'numericStats' => $stats['numeric'],
            'conceptStats' => $stats['concept'],
        ]);
    }

    /**
     * Mesmo critério de grupo do LessonController:
     * - Turma PAI: eligibleEnrollments
     * - Subturma: WorkshopAllocation (child_classroom_id + workshop_id)
     */
    protected function resolveEnrollments(Classroom $classroom, Workshop $workshop)
    {
        // TURMA PAI → turma inteira elegível
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

        // SUBTURMA → alocados via WorkshopAllocation
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

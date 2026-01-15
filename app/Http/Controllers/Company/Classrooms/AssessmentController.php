<?php

namespace App\Http\Controllers\Classrooms;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentGrade;
use App\Models\Classroom;
use App\Models\School;
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
     *
     * Rota nova:
     * GET /escolas/{school}/grupos/{classroom}/oficinas/{workshop}/avaliacoes
     * name: schools.assessments.index
     */
    public function index(School $school, Classroom $classroom, Workshop $workshop)
    {
        // Coerência de escopo: classroom precisa ser da escola da URL
        abort_unless((int) $classroom->school_id === (int) $school->id, 404);

        $classroom->loadMissing('school', 'gradeLevels', 'workshops');

        // Oficina vinculada a ESSA turma (pivot/classroom_workshop)
        $workshopForClass = $classroom->workshops->firstWhere('id', $workshop->id);
        abort_if(! $workshopForClass, 404);

        $assessments = Assessment::query()
            ->where('classroom_id', $classroom->id)
            ->where('workshop_id', $workshopForClass->id)
            ->withCount('grades')
            ->orderByDesc('due_at')
            ->orderByDesc('id')
            ->paginate(10);

        // Back dentro do escopo escola (limpo e consistente)
        $backUrl = route('schools.classrooms.show', [
            'school' => $school->id,
            'classroom' => $classroom->id,
        ]);

        return view('assessments.index', [
            'classroom' => $classroom,
            'workshop' => $workshopForClass,
            'assessments' => $assessments,
            'backUrl' => $backUrl,
            'school' => $school,
        ]);
    }

    /**
     * Tela de criação + lançamento de notas de uma avaliação.
     *
     * GET /escolas/{school}/grupos/{classroom}/oficinas/{workshop}/avaliacoes/criar
     * name: schools.assessments.create
     */
    public function create(School $school, Classroom $classroom, Workshop $workshop)
    {
        abort_unless((int) $classroom->school_id === (int) $school->id, 404);

        $classroom->loadMissing('school', 'gradeLevels', 'workshops');

        $workshopForClass = $classroom->workshops->firstWhere('id', $workshop->id);
        abort_if(! $workshopForClass, 404);

        $enrollments = $this->resolveEnrollments($classroom, $workshopForClass);

        return view('assessments.create', [
            'classroom' => $classroom,
            'workshop' => $workshopForClass,
            'enrollments' => $enrollments,
            'school' => $school,
        ]);
    }

    /**
     * Salva avaliação + notas.
     *
     * POST /escolas/{school}/grupos/{classroom}/oficinas/{workshop}/avaliacoes
     * name: schools.assessments.store
     */
    public function store(Request $request, School $school, Classroom $classroom, Workshop $workshop)
    {
        abort_unless((int) $classroom->school_id === (int) $school->id, 404);

        $classroom->loadMissing('school', 'gradeLevels', 'workshops');

        $workshopForClass = $classroom->workshops->firstWhere('id', $workshop->id);
        abort_if(! $workshopForClass, 404);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'scale_type' => ['required', 'in:points,concept'],

            // 0–100
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
                    'score_points' => null,
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
            ->route('schools.assessments.show', [
                'school' => $school->id,
                'classroom' => $classroom->id,
                'workshop' => $workshopForClass->id,
                'assessment' => $assessment->id,
            ])
            ->with('status', 'Avaliação lançada com sucesso!');
    }

    /**
     * Mostra os detalhes de uma avaliação (com notas).
     *
     * GET /escolas/{school}/grupos/{classroom}/oficinas/{workshop}/avaliacoes/{assessment}
     * name: schools.assessments.show
     */
    public function show(School $school, Classroom $classroom, Workshop $workshop, Assessment $assessment)
    {
        abort_unless((int) $classroom->school_id === (int) $school->id, 404);

        // Garante que a oficina pertence à turma
        $classroom->loadMissing('workshops');
        $workshopForClass = $classroom->workshops->firstWhere('id', $workshop->id);
        abort_if(! $workshopForClass, 404);

        abort_if(
            $assessment->classroom_id !== $classroom->id ||
            $assessment->workshop_id !== $workshopForClass->id,
            404
        );

        $assessment->load([
            'classroom.school',
            'workshop',
            'grades.enrollment.student',
            'grades.enrollment.gradeLevel',
        ]);

        $stats = $this->statsService->forAssessment($assessment);

        $grades = $assessment->grades
            ->sortBy(fn ($g) => mb_strtolower($g->enrollment->student->name));

        $backUrl = route('schools.assessments.index', [
            'school' => $school->id,
            'classroom' => $classroom->id,
            'workshop' => $workshopForClass->id,
        ]);

        return view('assessments.show', [
            'assessment' => $assessment,
            'grades' => $grades,
            'classroom' => $classroom,
            'workshop' => $workshopForClass,
            'backUrl' => $backUrl,
            'numericStats' => $stats['numeric'],
            'conceptStats' => $stats['concept'],
            'school' => $school,
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


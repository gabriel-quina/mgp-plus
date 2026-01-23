<?php

namespace App\Http\Controllers\Schools\Classrooms;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentGrade;
use App\Models\Classroom;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AssessmentController extends Controller
{
    private function resolveTeacherForUser(User $user): ?Teacher
    {
        if (Schema::hasTable('teachers') && Schema::hasColumn('teachers', 'cpf') && !empty($user->cpf)) {
            $cpf = preg_replace('/\D+/', '', (string) $user->cpf) ?: null;
            if ($cpf) {
                $t = Teacher::query()->where('cpf', $cpf)->first();
                if ($t) return $t;
            }
        }

        if (Schema::hasTable('teachers') && Schema::hasColumn('teachers', 'email') && !empty($user->email)) {
            $t = Teacher::query()->where('email', $user->email)->first();
            if ($t) return $t;
        }

        return null;
    }

    private function hasSchoolAccess(User $user, School $school): bool
    {
        if ($user->is_master) return true;

        return $user->schoolRoleAssignments()
            ->where('school_id', $school->id)
            ->exists();
    }

    /**
     * Regra atual (sem permissions específicas):
     * - master: pode
     * - não-master: precisa ter acesso à escola + estar vinculado a Teacher
     */
    private function authorizeAssessmentLaunch(School $school): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            abort(403, 'Não autenticado.');
        }

        if (! $this->hasSchoolAccess($user, $school)) {
            abort(403, 'Sem acesso a esta escola.');
        }

        if (! $user->is_master) {
            $teacher = $this->resolveTeacherForUser($user);
            if (! $teacher) {
                abort(403, 'Usuário não está vinculado a um professor (Teacher).');
            }
        }
    }

    public function index(School $school, Classroom $classroom)
    {
        $this->authorizeAssessmentLaunch($school);

        $classroom->loadMissing(['school', 'gradeLevels', 'schoolWorkshop.workshop']);

        $assessments = $classroom->assessments()
            ->withCount('grades')
            ->orderByDesc('due_at')
            ->orderByDesc('created_at')
            ->paginate(30);

        $user = Auth::user();
        $teacher = $user ? $this->resolveTeacherForUser($user) : null;

        $canLaunch = $user && $this->hasSchoolAccess($user, $school) && ($user->is_master || (bool) $teacher);

        return view('schools.assessments.index', compact('school', 'classroom', 'assessments', 'canLaunch'));
    }

    public function create(Request $request, School $school, Classroom $classroom)
    {
        $this->authorizeAssessmentLaunch($school);

        $classroom->loadMissing(['school', 'gradeLevels', 'schoolWorkshop.workshop']);

        $dueAt = Carbon::parse($request->input('due_at', now()->toDateString()))->startOfDay();

        // Convenção importante: roster endOfDay (evita "sumir aluno" em transferência no mesmo dia)
        $roster = $classroom->rosterAt($dueAt->copy()->endOfDay());

        return view('schools.assessments.create', [
            'school' => $school,
            'classroom' => $classroom,
            'dueAt' => $dueAt,
            'roster' => $roster,
        ]);
    }

    public function store(Request $request, School $school, Classroom $classroom)
    {
        $this->authorizeAssessmentLaunch($school);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_at' => ['required', 'date'],
            'scale_type' => ['required', 'in:points,concept'],
            'max_points' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'grades_points' => ['array'],
            'grades_points.*' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'grades_concept' => ['array'],
            'grades_concept.*' => ['nullable', 'string'],
        ]);

        $dueAt = Carbon::parse($data['due_at'])->startOfDay();
        $scale = $data['scale_type'];

        $roster = $classroom->rosterAt($dueAt->copy()->endOfDay());
        $allowedEnrollmentIds = $roster->pluck('id')->map(fn ($id) => (int) $id)->all();

        /** @var array<int|string, mixed> $gradesPoints */
        $gradesPoints = is_array($data['grades_points'] ?? null) ? $data['grades_points'] : [];
        /** @var array<int|string, mixed> $gradesConcept */
        $gradesConcept = is_array($data['grades_concept'] ?? null) ? $data['grades_concept'] : [];

        // ids presentes no payload (pontos ou conceito)
        $payloadIds = collect($gradesPoints)->keys()
            ->merge(collect($gradesConcept)->keys())
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        // bloqueia nota para aluno fora do roster
        $diff = array_diff($payloadIds, $allowedEnrollmentIds);
        if (! empty($diff)) {
            return back()
                ->withErrors(['grades' => 'Há notas para alunos que não pertencem ao grupo nesta data.'])
                ->withInput();
        }

        // max_points (para concept mantém um default seguro)
        $maxPoints = isset($data['max_points']) && $data['max_points'] !== ''
            ? (float) $data['max_points']
            : 100.0;

        // validação: points não pode ultrapassar max_points
        if ($scale === 'points') {
            foreach ($gradesPoints as $enrollmentId => $points) {
                if ($points === null || $points === '') continue;
                if ((float) $points > $maxPoints) {
                    return back()
                        ->withErrors(['grades_points' => "Há nota acima do máximo definido ({$maxPoints})."])
                        ->withInput();
                }
            }
        }

        // validação: concept deve estar na lista
        if ($scale === 'concept') {
            foreach ($gradesConcept as $enrollmentId => $concept) {
                $c = trim((string) $concept);
                if ($c === '') continue;
                if (! in_array($c, AssessmentGrade::CONCEPTS, true)) {
                    return back()
                        ->withErrors(['grades_concept' => 'Há um conceito inválido.'])
                        ->withInput();
                }
            }
        }

        return DB::transaction(function () use ($classroom, $school, $data, $dueAt, $scale, $maxPoints, $gradesPoints, $gradesConcept, $allowedEnrollmentIds) {
            $assessment = $classroom->assessments()->create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'due_at' => $dueAt->toDateString(),
                'scale_type' => $scale,
                'max_points' => $maxPoints,
            ]);

            $now = now();
            $rows = [];

            // percorre roster (não o payload) para consistência
            foreach ($allowedEnrollmentIds as $enrollmentId) {
                $enrollmentId = (int) $enrollmentId;

                $scorePoints = null;
                $scoreConcept = null;

                if ($scale === 'points') {
                    $v = $gradesPoints[$enrollmentId] ?? null;
                    $v = ($v === '' ? null : $v);
                    $scorePoints = ($v === null ? null : (float) $v);
                } else {
                    $v = $gradesConcept[$enrollmentId] ?? null;
                    $v = trim((string) $v);
                    $scoreConcept = ($v === '' ? null : $v);
                }

                // grava apenas se tiver nota
                if ($scorePoints === null && $scoreConcept === null) {
                    continue;
                }

                $rows[] = [
                    'assessment_id' => $assessment->id,
                    'student_enrollment_id' => $enrollmentId,
                    'score_points' => $scorePoints,
                    'score_concept' => $scoreConcept,
                    'notes' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (! empty($rows)) {
                AssessmentGrade::upsert(
                    $rows,
                    ['assessment_id', 'student_enrollment_id'],
                    ['score_points', 'score_concept', 'notes', 'updated_at']
                );
            }

            return redirect()
                ->route('schools.classrooms.assessments.show', [$school, $classroom, $assessment])
                ->with('success', 'Avaliação salva com sucesso.');
        });
    }

    public function show(School $school, Classroom $classroom, Assessment $assessment)
    {
        $this->authorizeAssessmentLaunch($school);

        abort_unless((int) $assessment->classroom_id === (int) $classroom->id, 404);

        $classroom->loadMissing(['school', 'gradeLevels', 'schoolWorkshop.workshop']);
        $assessment->load(['grades.enrollment.student', 'grades.enrollment.gradeLevel']);

        $dueAt = $assessment->due_at ? Carbon::parse($assessment->due_at)->startOfDay() : now()->startOfDay();

        $roster = $classroom->rosterAt($dueAt->copy()->endOfDay());
        $gradesByEnrollment = $assessment->grades->keyBy('student_enrollment_id');

        $numericStats = null;
        $conceptStats = null;

        if ($assessment->scale_type === 'points') {
            $vals = $assessment->grades
                ->pluck('score_points')
                ->filter(fn ($v) => $v !== null)
                ->map(fn ($v) => (float) $v)
                ->values();

            if ($vals->count() > 0) {
                $numericStats = [
                    'avg' => $vals->avg(),
                    'max' => $vals->max(),
                    'min' => $vals->min(),
                    'count' => $vals->count(),
                    'max_points' => (float) ($assessment->max_points ?? 100.0),
                ];
            }
        } else {
            $dist = [];
            foreach (AssessmentGrade::CONCEPTS as $c) $dist[$c] = 0;
            $dist[null] = 0;

            foreach ($roster as $en) {
                $g = $gradesByEnrollment[$en->id] ?? null;
                $c = $g?->score_concept ? (string) $g->score_concept : null;
                if (! array_key_exists($c, $dist)) $dist[$c] = 0;
                $dist[$c]++;
            }

            $conceptStats = ['distribution' => $dist];
        }

        return view('schools.assessments.show', compact(
            'school',
            'classroom',
            'assessment',
            'roster',
            'gradesByEnrollment',
            'numericStats',
            'conceptStats'
        ));
    }
}


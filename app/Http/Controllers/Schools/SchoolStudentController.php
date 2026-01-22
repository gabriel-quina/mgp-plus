<?php

namespace App\Http\Controllers\Schools;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schools\SchoolStudentIndexRequest;
use App\Http\Requests\Schools\Students\StoreStudentRequest;
use App\Http\Requests\Schools\Students\UpdateStudentRequest;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Services\GradeLevelStudentReportService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SchoolStudentController extends Controller
{
    public function index(SchoolStudentIndexRequest $request, School $school)
    {
        $search = $request->input('q');
        $gradeLevelId = $request->integer('grade_level');
        $gradeLevelFilter = $gradeLevelId ? GradeLevel::query()->find($gradeLevelId) : null;
        $cols = $request->input('cols');
        $showAvg = $gradeLevelId ? (is_array($cols) ? in_array('avg', $cols, true) : true) : false;
        $showAtt = $gradeLevelId ? (is_array($cols) ? in_array('att', $cols, true) : true) : false;

        $baseQuery = StudentEnrollment::query()
            ->where('school_id', $school->id)
            ->when($search, function ($q) use ($search) {
                $q->whereHas('student', fn ($qq) => $qq->where('name', 'like', "%{$search}%"));
            })
            ->when($gradeLevelId, function ($q) use ($gradeLevelId) {
                $q->where('grade_level_id', $gradeLevelId);
            });

        $latestEnrollmentIds = (clone $baseQuery)
            ->selectRaw('MAX(id) as id')
            ->groupBy('student_id');

        $enrollments = StudentEnrollment::query()
            ->whereIn('id', $latestEnrollmentIds)
            ->with(['student', 'gradeLevel'])
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $studentMetrics = collect();
        if ($gradeLevelFilter && ($showAvg || $showAtt)) {
            $reportService = app(GradeLevelStudentReportService::class);
            $report = $reportService->forSchoolAndGrade($school, $gradeLevelFilter);

            $studentMetrics = $report->mapWithKeys(function ($row) {
                $student = is_array($row) ? ($row['student'] ?? null) : ($row->student ?? null);
                $enrollment = is_array($row) ? ($row['enrollment'] ?? null) : ($row->enrollment ?? null);
                $studentId = $student?->id ?? $enrollment?->student_id;

                if (! $studentId) {
                    return [];
                }

                return [
                    $studentId => [
                        'avg' => is_array($row) ? ($row['avg_points'] ?? null) : ($row->avg_points ?? null),
                        'att' => is_array($row) ? ($row['freq_pct'] ?? null) : ($row->freq_pct ?? null),
                    ],
                ];
            });

            $pageStudentIds = $enrollments->pluck('student_id')->filter()->all();
            if (! empty($pageStudentIds)) {
                $studentMetrics = $studentMetrics->only($pageStudentIds);
            }
        }

        $clearFilterUrl = route('schools.students.index', $school);
        if (! empty($search)) {
            $clearFilterUrl .= '?' . http_build_query(['q' => $search]);
        }

        return view('schools.students.index', [
            'schoolNav' => $school,
            'school' => $school,
            'enrollments' => $enrollments,
            'search' => $search,
            'gradeLevelFilter' => $gradeLevelFilter,
            'gradeLevelId' => $gradeLevelId,
            'showAvg' => $showAvg,
            'showAtt' => $showAtt,
            'studentMetrics' => $studentMetrics,
            'clearFilterUrl' => $clearFilterUrl,
        ]);
    }

    public function create(School $school)
    {
        $gradeLevels = GradeLevel::orderBy('sequence')->orderBy('name')->pluck('name', 'id');

        return view('schools.students.create', [
            'schoolNav' => $school,
            'school' => $school,
            'gradeLevels' => $gradeLevels,
        ]);
    }

    public function store(StoreStudentRequest $request, School $school)
    {
        $data = $request->validated();

        $s = $data['student'] ?? [];
        $e = $data['enrollment'] ?? [];

        DB::transaction(function () use ($s, $e, $school) {
            $cpf = trim((string) ($s['cpf'] ?? ''));
            $cpf = $cpf !== '' ? $cpf : null;

            $email = trim((string) ($s['email'] ?? ''));
            $email = $email !== '' ? $email : null;

            $student = null;

            if (! empty($cpf)) {
                $student = Student::query()
                    ->where('cpf', $cpf)
                    ->lockForUpdate()
                    ->first();
            }

            $student = $student ?? new Student;

            if (! empty($email)) {
                $emailConflict = Student::query()
                    ->where('email', $email)
                    ->when($student->exists, fn ($q) => $q->where('id', '!=', $student->id))
                    ->exists();

                if ($emailConflict) {
                    throw ValidationException::withMessages([
                        'student.email' => 'Este e-mail já está em uso por outro aluno.',
                    ]);
                }
            }

            $student->fill([
                'name' => $s['name'] ?? null,
                'cpf' => $cpf,
                'email' => $email,
                'birthdate' => $s['birthdate'] ?? null,
                'race_color' => $s['race_color'] ?? null,
                'has_disability' => (bool) ($s['has_disability'] ?? false),
                'disability_types' => $s['disability_type_ids'] ?? [],
                'disability_details' => $s['disability_details'] ?? null,
                'allergies' => $s['allergies'] ?? null,
                'emergency_contact_name' => $s['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $s['emergency_contact_phone'] ?? null,
            ])->save();

            // Regra: NÃO pode estar ativo em duas escolas
            $activeEnrollment = StudentEnrollment::query()
                ->where('student_id', $student->id)
                ->where('status', StudentEnrollment::STATUS_ACTIVE)
                ->lockForUpdate()
                ->first();

            if ($activeEnrollment) {
                if ((int) $activeEnrollment->school_id === (int) $school->id) {
                    throw ValidationException::withMessages([
                        'student.cpf' => 'Este aluno já possui matrícula ativa nesta escola.',
                    ]);
                }

                $activeSchoolName = School::query()
                    ->whereKey($activeEnrollment->school_id)
                    ->value('name') ?? 'outra escola';

                throw ValidationException::withMessages([
                    'student.cpf' => "Este aluno já possui matrícula ativa em {$activeSchoolName}. Encerre a matrícula antes de matricular nesta escola.",
                ]);
            }

            StudentEnrollment::create([
                'student_id' => $student->id,
                'school_id' => $school->id,
                'grade_level_id' => $e['grade_level_id'],
                'academic_year' => $e['academic_year'],
                'shift' => $e['shift'] ?? 'morning',
                'status' => StudentEnrollment::STATUS_ENROLLED,
                'transfer_scope' => 'first',
                'origin_school_id' => null,
                'started_at' => $e['started_at'] ?? now()->toDateString(),
                'ended_at' => null,
            ]);
        });

        return redirect()
            ->route('schools.students.index', $school)
            ->with('success', 'Aluno cadastrado e matrícula criada para esta escola.');
    }

    public function show(School $school, Student $student)
    {
        $enrollments = $student->enrollments()
            ->where('school_id', $school->id)
            ->with(['gradeLevel'])
            ->orderByDesc('academic_year')
            ->orderByDesc('id')
            ->get();

        abort_if($enrollments->isEmpty(), 404);

        $currentEnrollment = $enrollments->firstWhere('status', StudentEnrollment::STATUS_ACTIVE)
            ?? $enrollments->firstWhere('status', StudentEnrollment::STATUS_ENROLLED)
            ?? $enrollments->firstWhere('status', StudentEnrollment::STATUS_PRE_ENROLLED)
            ?? $enrollments->first();

        return view('schools.students.show', [
            'school' => $school,
            'schoolNav' => $school,
            'student' => $student,
            'enrollments' => $enrollments,
            'currentEnrollment' => $currentEnrollment,
        ]);
    }

    public function edit(School $school, Student $student)
    {
        abort_unless(
            $student->enrollments()->where('school_id', $school->id)->exists(),
            404
        );

        return view('schools.students.edit', [
            'school' => $school,
            'schoolNav' => $school,
            'student' => $student,
        ]);
    }

    public function update(UpdateStudentRequest $request, School $school, Student $student)
    {
        abort_unless(
            $student->enrollments()->where('school_id', $school->id)->exists(),
            404
        );

        $s = ($request->validated()['student'] ?? []);

        $cpf = trim((string) ($s['cpf'] ?? ''));
        $cpf = $cpf !== '' ? $cpf : null;

        $email = trim((string) ($s['email'] ?? ''));
        $email = $email !== '' ? $email : null;

        if (! empty($email)) {
            $emailConflict = Student::query()
                ->where('email', $email)
                ->where('id', '!=', $student->id)
                ->exists();

            if ($emailConflict) {
                throw ValidationException::withMessages([
                    'student.email' => 'Este e-mail já está em uso por outro aluno.',
                ]);
            }
        }

        $student->fill([
            'name' => $s['name'] ?? $student->name,
            'cpf' => $cpf,
            'email' => $email,
            'birthdate' => $s['birthdate'] ?? null,
            'race_color' => $s['race_color'] ?? null,
            'has_disability' => (bool) ($s['has_disability'] ?? false),
            'disability_types' => $s['disability_type_ids'] ?? [],
            'disability_details' => $s['disability_details'] ?? null,
            'allergies' => $s['allergies'] ?? null,
            'emergency_contact_name' => $s['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $s['emergency_contact_phone'] ?? null,
        ])->save();

        return redirect()
            ->route('schools.students.show', [$school, $student])
            ->with('success', 'Aluno atualizado.');
    }
}


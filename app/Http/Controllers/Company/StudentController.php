<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;

use App\Http\Requests\{StoreStudentRequest, UpdateStudentRequest};
use App\Models\{City, GradeLevel, School, State, Student, StudentEnrollment};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::query()
            ->with(['currentEnrollment.school.city'])
            ->latest('id')
            ->paginate(20);

        return view('students.index', compact('students'));
    }

    public function create()
    {
        $gradeLevels = GradeLevel::orderBy('order')->orderBy('name')->pluck('name', 'id');
        $states = State::orderBy('name')->pluck('name', 'id'); // <- IDs

        return view('students.create', compact('gradeLevels', 'states'));
    }

    public function store(StoreStudentRequest $request)
    {
        $data = $request->validated();
        $s = $data['student'];
        $e = $data['enrollment'];

        DB::transaction(function () use ($s, $e) {
            // 1) Student (reaproveita por CPF se existir)
            $student = Student::query()
                ->when(! empty($s['cpf']), fn ($q) => $q->where('cpf', $s['cpf']))
                ->first() ?? new Student;

            $student->fill([
                'name' => $s['name'],
                'social_name' => $s['social_name'] ?? null,
                'cpf' => $s['cpf'] ?? null,
                'email' => $s['email'] ?? null,
                // coluna é birthdate; form manda student[birth_date]
                'birthdate' => $s['birthdate'] ?? null,
                'race_color' => $s['race_color'] ?? null,
                'has_disability' => (bool) ($s['has_disability'] ?? false),
                'disability_types' => $s['disability_type_ids'] ?? null,
                'disability_details' => $s['disability_details'] ?? null,
                'allergies' => $s['allergies'] ?? null,
                'emergency_contact_name' => $s['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $s['emergency_contact_phone'] ?? null,
            ])->save();

            // 2) DESTINO
            $destSchool = School::findOrFail($e['destination_school_id']);

            // 3) ORIGEM (dependente do escopo) — mesma função utilitária que você já tem
            $originSchoolId = $this->resolveOriginSchoolId(
                $e['transfer_scope'],
                $e,
                $destSchool
            );

            // 4) Episódio inicial (modelagem por episódios)
            StudentEnrollment::create([
                'student_id' => $student->id,
                'school_id' => $destSchool->id,
                'grade_level_id' => $e['grade_level_id'],
                'academic_year' => $e['academic_year'],
                'shift' => $e['shift'] ?? 'morning',
                'status' => StudentEnrollment::STATUS_ACTIVE,
                'transfer_scope' => $e['transfer_scope'],
                'origin_school_id' => $originSchoolId,
                'started_at' => $e['started_at'] ?? now()->toDateString(),
                'ended_at' => null,
            ]);
        });

        return redirect()->route('students.index')->with('success', 'Aluno cadastrado com matrícula criada.');
    }

    public function show(Student $student)
    {
        $student->load(['currentEnrollment.school.city', 'enrollments.school.city', 'enrollments.originSchool', 'enrollments.gradeLevel']);

        return view('students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        return view('students.edit', compact('student'));
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $s = $request->validated()['student'];

        $student->fill([
            'name' => $s['name'],
            'social_name' => $s['social_name'] ?? null,
            'cpf' => $s['cpf'] ?? null,
            'email' => $s['email'] ?? null,
            'birthdate' => $s['birthdate'] ?? null,
            'race_color' => $s['race_color'] ?? null,
            'has_disability' => (bool) ($s['has_disability'] ?? false),
            'disability_types' => $s['disability_type_ids'] ?? null,
            'disability_details' => $s['disability_details'] ?? null,
            'allergies' => $s['allergies'] ?? null,
            'emergency_contact_name' => $s['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $s['emergency_contact_phone'] ?? null,
        ])->save();

        // NADA de enrollment aqui — edição de matrícula é em StudentEnrollmentController
        return redirect()->route('students.show', $student)->with('success', 'Aluno atualizado.');
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()->route('students.index')->with('success', 'Aluno removido.');
    }

    /* ================= Privados ================= */

    protected function validateStore(Request $request): array
    {
        return $request->validate([
            // STUDENT
            'student.name' => ['required', 'string', 'max:120'],
            'student.social_name' => ['nullable', 'string', 'max:120'],
            'student.cpf' => ['nullable', 'string', 'max:20', 'unique:students,cpf'],
            'student.email' => ['nullable', 'email', 'unique:students,email'],
            'student.birth_date' => ['nullable', 'date'],
            'student.race_color' => ['nullable', 'string', 'max:20'],
            'student.has_disability' => ['nullable', 'boolean'],
            'student.disability_type_ids' => ['nullable', 'array'],
            'student.disability_type_ids.*' => ['integer'],
            'student.disability_details' => ['nullable', 'string'],
            'student.allergies' => ['nullable', 'string'],
            'student.emergency_contact_name' => ['nullable', 'string', 'max:120'],
            'student.emergency_contact_phone' => ['nullable', 'string', 'max:32'],

            // ENROLLMENT
            'enrollment.destination_school_id' => ['required', 'integer', 'exists:schools,id'],
            'enrollment.academic_year' => ['required', 'integer', 'min:1900', 'max:9999'],
            'enrollment.grade_level_id' => ['required', 'integer', 'exists:grade_levels,id'], // <- corrigido
            'enrollment.shift' => ['nullable', Rule::in(['morning', 'afternoon', 'evening'])],
            'enrollment.started_at' => ['nullable', 'date'],
            'enrollment.transfer_scope' => ['required', Rule::in(['first', 'internal', 'external'])],

            // ORIGEM
            'enrollment.origin_school_id' => ['nullable', 'integer', 'exists:schools,id'],
            'enrollment.origin_school_name' => ['nullable', 'string', 'max:150'],
            'enrollment.origin_city_name' => ['nullable', 'string', 'max:120'],
            'enrollment.origin_state_code' => ['nullable', 'string', 'size:2'],
        ]);
    }

    protected function resolveOriginSchoolId(string $scope, array $enr, School $destSchool): ?int
    {
        if (! empty($enr['origin_school_id'])) {
            return (int) $enr['origin_school_id'];
        }
        if ($scope === 'first') {
            return null;
        }

        $name = trim((string) ($enr['origin_school_name'] ?? ''));
        if ($name === '') {
            return null;
        }

        if ($scope === 'internal') {
            return $this->findOrCreateHistoricalSchool($name, $destSchool->city_id)->id;
        }

        // -------- EXTERNA: usa state_id padronizado --------
        $cityName = trim((string) ($enr['origin_city_name'] ?? ''));
        $stateId = (int) ($enr['origin_state_id'] ?? 0);
        if ($cityName === '' || $stateId <= 0) {
            return null; // validação já cobre, é só blindagem
        }

        $city = $this->findOrCreateCityByNameStateId($cityName, $stateId);

        return $this->findOrCreateHistoricalSchool($name, $city->id)->id;
    }

    protected function findOrCreateCityByNameStateId(string $cityName, int $stateId): City
    {
        $name = trim($cityName);

        $city = City::query()
            ->where('state_id', $stateId)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        return $city ?: City::create([
            'state_id' => $stateId,
            'name' => $name,
        ]);
    }

    protected function findOrCreateHistoricalSchool(string $name, int $cityId): School
    {
        $norm = preg_replace('/\s+/', ' ', mb_strtolower(trim($name)));

        $existing = School::query()
            ->where('city_id', $cityId)
            ->where('is_historical', true)
            ->whereRaw('LOWER(TRIM(REPLACE(name, "  ", " "))) = ?', [$norm])
            ->first();

        return $existing ?: School::create([
            'city_id' => $cityId,
            'name' => $name,
            'is_historical' => true,
        ]);
    }

    protected function findOrCreateCityByNameUf(string $cityName, string $stateCode): City
    {
        $name = trim($cityName);
        $uf = strtoupper(trim($stateCode));

        // 1) valida/acha o estado pelo UF (já seedado)
        $state = State::where('uf', $uf)->firstOrFail();

        // 2) busca cidade por nome+state_id (case-insensitive)
        $city = City::query()
            ->where('state_id', $state->id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        // 3) cria se não existir
        return $city ?: City::create([
            'state_id' => $state->id,
            'name' => $name,
        ]);
    }
}

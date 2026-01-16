<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;

use App\Models\City;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StudentEnrollmentController extends Controller
{
    /** Listagem global de matrículas (com filtros do index.blade) */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        // ano padrão sempre = ano corrente
        $yrParam = $request->query('year');
        $yr = is_numeric($yrParam) ? (int) $yrParam : (int) now()->year;

        $sc = $request->integer('school_id') ?: null;

        $sh = $request->query('shift');
        $sh = in_array($sh, ['morning', 'afternoon', 'evening'], true) ? $sh : null;

        $st = $request->query('status');
        $st = in_array($st, ['active', 'completed', 'failed', 'transferred', 'dropped', 'suspended'], true) ? $st : null;

        // Vamos ordenar por nomes de tabelas relacionadas: juntamos pra ordenar,
        // mas selecionamos apenas student_enrollments.* pra hidratar o model certinho.
        $query = StudentEnrollment::query()
            ->leftJoin('students', 'students.id', '=', 'student_enrollments.student_id')
            ->leftJoin('schools', 'schools.id', '=', 'student_enrollments.school_id')
            ->leftJoin('grade_levels', 'grade_levels.id', '=', 'student_enrollments.grade_level_id')
            ->select('student_enrollments.*')
            ->with(['student', 'school', 'gradeLevel']);

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

        // filtros
        $query->where('student_enrollments.academic_year', $yr); // sempre aplica ano corrente por padrão
        if ($sc) {
            $query->where('student_enrollments.school_id', $sc);
        }
        if ($sh) {
            $query->where('student_enrollments.shift', $sh);
        }
        if ($st) {
            $query->where('student_enrollments.status', $st);
        }

        // ordenação: Escola → Ano escolar → Nome
        $query->orderBy('schools.name')
            ->orderBy('grade_levels.sequence')   // se sua tabela tiver a coluna "order"
            ->orderBy('grade_levels.name')    // fallback/empate
            ->orderBy('students.name');

        $enrollments = $query
            ->paginate(20)
            ->withQueryString();

        $schoolsForFilter = School::orderBy('name')->pluck('name', 'id');

        return view('enrollments.index', compact(
            'enrollments',
            'q', 'yr', 'sc', 'sh', 'st',
            'schoolsForFilter'
        ));
    }

    /** Cria novo episódio (transferência / próxima matrícula) — top-level */
    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'school_id' => ['required', 'integer', 'exists:schools,id'],        // DESTINO
            'grade_level_id' => ['required', 'integer', 'exists:grade_levels,id'],
            'academic_year' => ['required', 'integer', 'min:1900', 'max:9999'],
            'shift' => ['nullable', Rule::in(['morning', 'afternoon', 'evening'])],
            'started_at' => ['nullable', 'date'],
            'transfer_scope' => ['required', Rule::in(['first', 'internal', 'external'])],

            // ORIGEM
            'origin_school_id' => ['nullable', 'integer', 'exists:schools,id'],
            'origin_school_name' => ['nullable', 'string', 'max:150'],
            'origin_city_name' => ['nullable', 'string', 'max:120'],
            'origin_state_id' => ['nullable', 'integer', 'exists:states,id'], // padrão por ID
        ]);

        DB::transaction(function () use ($data) {
            $student = Student::findOrFail($data['student_id']);
            $dest = School::findOrFail($data['school_id']);

            // Fecha episódio ativo quando não for "first"
            $current = $student->currentEnrollment()->first();
            if ($current && ($data['transfer_scope'] !== StudentEnrollment::SCOPE_FIRST)) {
                $current->update([
                    'status' => StudentEnrollment::STATUS_TRANSFERRED,
                    'ended_at' => $data['started_at'] ?? now()->toDateString(),
                ]);
            }

            $originId = $this->resolveOriginSchoolId($data['transfer_scope'], $data, $dest);

            StudentEnrollment::create([
                'student_id' => $student->id,
                'school_id' => $dest->id,
                'grade_level_id' => $data['grade_level_id'],
                'academic_year' => $data['academic_year'],
                'shift' => $data['shift'] ?? 'morning',
                'status' => StudentEnrollment::STATUS_ACTIVE,
                'transfer_scope' => $data['transfer_scope'],
                'origin_school_id' => $originId,
                'started_at' => $data['started_at'] ?? now()->toDateString(),
                'ended_at' => null,
            ]);
        });

        return redirect()->route('enrollments.index')->with('success', 'Matrícula criada.');
    }

    /** Atualiza status/datas (encerrar, suspender, etc.) */
    public function update(Request $request, StudentEnrollment $enrollment)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in([
                StudentEnrollment::STATUS_ACTIVE,
                StudentEnrollment::STATUS_COMPLETED,
                StudentEnrollment::STATUS_FAILED,
                StudentEnrollment::STATUS_TRANSFERRED,
                StudentEnrollment::STATUS_DROPPED,
                StudentEnrollment::STATUS_SUSPENDED,
            ])],
            'ended_at' => ['nullable', 'date'],
        ]);

        $enrollment->update($data);

        return back()->with('success', 'Episódio atualizado.');
    }

    public function destroy(StudentEnrollment $enrollment)
    {
        $enrollment->delete();

        return back()->with('success', 'Episódio removido.');
    }

    /* ================= Privados ================= */

    protected function resolveOriginSchoolId(string $scope, array $enr, School $destSchool): ?int
    {
        if (! empty($enr['origin_school_id'])) {
            return (int) $enr['origin_school_id'];
        }
        if ($scope === StudentEnrollment::SCOPE_FIRST || $scope === 'first') {
            return null;
        }

        $name = trim((string) ($enr['origin_school_name'] ?? ''));
        if ($name === '') {
            return null;
        }

        if ($scope === StudentEnrollment::SCOPE_INTERNAL || $scope === 'internal') {
            return $this->findOrCreateHistoricalSchool($name, $destSchool->city_id)->id;
        }

        // EXTERNA: precisa de city + state_id (padronizado)
        $cityName = trim((string) ($enr['origin_city_name'] ?? ''));
        $stateId = (int) ($enr['origin_state_id'] ?? 0);
        if ($cityName === '' || $stateId <= 0) {
            return null; // validação já cobre, blindagem extra
        }

        $city = $this->findOrCreateCityByNameStateId($cityName, $stateId);

        return $this->findOrCreateHistoricalSchool($name, $city->id)->id;
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
}

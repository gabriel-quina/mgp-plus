<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\School;
use App\Models\StudentEnrollment;
use App\Services\Schools\Queries\GetSchoolGradeLevelCounts;
use App\Models\Workshop;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SchoolController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $schools = School::query()
            ->with(['city.state'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhereHas('city', function ($c) use ($q) {
                            $c->where('name', 'like', "%{$q}%")
                                ->orWhereHas('state', function ($s) use ($q) {
                                    $s->where('name', 'like', "%{$q}%")
                                        ->orWhere('uf', 'like', "%{$q}%");
                                });
                        });
                });
            })
            ->orderBy('name')
            ->paginate(15);

        return view('schools.index', compact('schools', 'q'));
    }

    public function create()
    {
        $cities = City::orderBy('name')->pluck('name', 'id');
        $workshops = Workshop::orderBy('name')->pluck('name', 'id');

        return view('schools.create', compact('cities', 'workshops'));
    }

    public function store(Request $request)
    {
        $data = $this->validateSchool($request);
        $school = School::create($data);

        // vincula oficinas (se vierem)
        $ids = $request->input('workshop_ids', []);
        $school->workshops()->sync($ids);

        return redirect()
            ->route('schools.index')
            ->with('success', 'Escola criada com sucesso.');
    }

    public function show(School $school)
    {
        // Carrega tudo que interessa pra uma visão segmentada dessa escola
        $currentAcademicYear = (int) now()->year;

        $school->load([
            'city.state',
            // Turmas operacionais da escola
            'classrooms' => function ($q) use ($currentAcademicYear) {
                $q->where('academic_year_id', $currentAcademicYear)
                    ->where('status', 'active')
                    ->with(['workshop'])
                    ->orderBy('group_number');
            },
            // Oficinas vinculadas à escola
            'workshops',
        ])->loadCount([
            // counts prontos pra usar nos cards
            'classrooms as classrooms_count' => function ($q) use ($currentAcademicYear) {
                $q->where('academic_year_id', $currentAcademicYear)
                    ->where('status', 'active');
            },
            'workshops as workshops_count',
            'enrollments as enrollments_count' => function ($q) use ($currentAcademicYear) {
                $q->select(DB::raw('count(distinct student_id)'))
                    ->where('academic_year', $currentAcademicYear)
                    ->whereIn('status', [
                        StudentEnrollment::STATUS_ENROLLED,
                        StudentEnrollment::STATUS_ACTIVE,
                    ])
                    ->whereNull('ended_at');
            },
        ]);

        $gradeLevelsWithStudents = (new GetSchoolGradeLevelCounts())->execute($school, $currentAcademicYear);

        // Se você tiver relação de matrículas na escola, pode somar aqui depois:
        // ->loadCount('enrollments as enrollments_count');

        return view('schools.show', [
            'school' => $school,
            'schoolNav' => $school,
            'gradeLevelsWithStudents' => $gradeLevelsWithStudents,
        ]);
    }

    public function edit(School $school)
    {
        $cities = City::orderBy('name')->pluck('name', 'id');
        $workshops = Workshop::orderBy('name')->pluck('name', 'id');
        $school->loadMissing('workshops'); // para pré-marcar

        return view('schools.edit', compact('school', 'cities', 'workshops'));
    }

    public function update(Request $request, School $school)
    {
        $data = $this->validateSchool($request, $school->id);
        $school->update($data);

        $ids = $request->input('workshop_ids', []);
        $school->workshops()->sync($ids);

        return redirect()->route('schools.show', $school)
            ->with('success', 'Escola atualizada com sucesso.');
    }

    public function destroy(School $school)
    {
        try {
            $school->delete();

            return redirect()->route('schools.index')
                ->with('success', 'Escola removida.');
        } catch (QueryException $e) {
            report($e);

            return redirect()->route('schools.index')
                ->withErrors('Não foi possível remover a escola. Verifique vínculos existentes.');
        }
    }

    /**
     * API de busca (typeahead) usada no cadastro/matrícula do aluno.
     * GET /api/escolas/buscar?q=...
     * Retorna: [{ id, name, city_name, state_uf, is_historical }]
     */
    public function search(Request $request)
    {
        $q = (string) $request->query('q', '');
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $cityId = $request->integer('city_id'); // <- filtro opcional

        $schools = School::query()
            ->with(['city.state'])
            ->when($cityId, fn ($qq) => $qq->where('city_id', $cityId))
            ->where('name', 'LIKE', '%'.$q.'%')
            ->orderBy('is_historical') // normais primeiro
            ->orderBy('name')
            ->limit(10)
            ->get();

        return response()->json(
            $schools->map(function (School $s) {
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'city_id' => $s->city_id,                 // <- NOVO
                    'city_name' => $s->city?->name,
                    'state_uf' => $s->city?->state?->uf,
                    'is_historical' => (bool) $s->is_historical,
                ];
            })
        );
    }

    private function validateSchool(Request $request, $id = null): array
    {
        return $request->validate(
            [
                'city_id' => ['required', 'exists:cities,id'],
                'name' => [
                    'required',
                    'string',
                    'max:150',
                    Rule::unique('schools', 'name')
                        ->ignore($id) // permite atualizar mantendo o mesmo nome
                        ->where(fn ($q) => $q->where('city_id', $request->input('city_id'))),
                ],
                'street' => ['nullable', 'string', 'max:150'],
                'number' => ['nullable', 'string', 'max:20'],
                'neighborhood' => ['nullable', 'string', 'max:120'],
                'complement' => ['nullable', 'string', 'max:120'],
                // Aceita 12345678 ou 12345-678; o Model normaliza para dígitos
                'cep' => ['nullable', 'regex:/^\d{5}-?\d{3}$/'],

                // recebe o array de oficinas selecionadas
                'workshop_ids' => ['nullable', 'array'],
                'workshop_ids.*' => ['integer', 'exists:workshops,id'],
            ],
            [
                'city_id.required' => 'Selecione a cidade.',
                'city_id.exists' => 'Cidade inválida.',
                'cep.regex' => 'CEP deve ser 12345-678 ou 12345678.',
            ],
            [
                'city_id' => 'Cidade',
                'name' => 'Nome da escola',
                'street' => 'Logradouro',
                'number' => 'Número',
                'neighborhood' => 'Bairro',
                'complement' => 'Complemento',
                'cep' => 'CEP',
            ]
        );
    }
}

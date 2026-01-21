<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\School;
use App\Models\SchoolWorkshop;
use App\Models\StudentEnrollment;
use App\Models\Workshop;
use App\Services\Schools\Queries\GetSchoolGradeLevelCounts;
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

        return view('company.schools.index', compact('schools', 'q'));
    }

    public function create()
    {
        $cities = City::orderBy('name')->pluck('name', 'id');
        $workshops = Workshop::orderBy('name')->pluck('name', 'id');

        return view('company.schools.create', compact('cities', 'workshops'));
    }

    public function store(Request $request)
    {
        $data = $this->validateSchool($request);

        DB::transaction(function () use ($request, $data, &$school) {
            $school = School::create($data);
            $this->syncSchoolWorkshops($school, (array) $request->input('workshop_ids', []));
        });

        return redirect()
            ->route('admin.schools.index')
            ->with('success', 'Escola criada com sucesso.');
    }

    public function show(School $school)
    {
        $currentAcademicYear = (int) now()->year;

        $school->load([
            'city.state',
            'classrooms' => function ($q) use ($currentAcademicYear) {
                $q->where('academic_year', $currentAcademicYear)
                    ->with(['gradeLevels'])
                    ->orderBy('shift')
                    ->orderBy('grades_signature')
                    ->orderBy('group_number');
            },
            'schoolWorkshops.workshop',
        ])->loadCount([
            'classrooms as classrooms_count' => function ($q) use ($currentAcademicYear) {
                $q->where('academic_year', $currentAcademicYear);
            },
            'schoolWorkshops as workshops_count',
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

        return view('company.schools.show', [
            'school' => $school,
            'schoolNav' => $school,
            'gradeLevelsWithStudents' => $gradeLevelsWithStudents,
        ]);
    }

    public function edit(School $school)
    {
        $cities = City::orderBy('name')->pluck('name', 'id');
        $workshops = Workshop::orderBy('name')->pluck('name', 'id');

        $school->loadMissing('schoolWorkshops');

        return view('company.schools.edit', compact('school', 'cities', 'workshops'));
    }

    public function update(Request $request, School $school)
    {
        $data = $this->validateSchool($request, $school->id);

        DB::transaction(function () use ($request, $school, $data) {
            $school->update($data);
            $this->syncSchoolWorkshops($school, (array) $request->input('workshop_ids', []));
        });

        return redirect()
            ->route('admin.schools.show', $school)
            ->with('success', 'Escola atualizada com sucesso.');
    }

    public function destroy(School $school)
    {
        try {
            $school->delete();

            return redirect()
                ->route('admin.schools.index')
                ->with('success', 'Escola removida.');
        } catch (QueryException $e) {
            report($e);

            return redirect()
                ->route('admin.schools.index')
                ->withErrors('Não foi possível remover a escola. Verifique vínculos existentes.');
        }
    }

    public function search(Request $request)
    {
        $q = (string) $request->query('q', '');
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $cityId = $request->integer('city_id');

        $schools = School::query()
            ->with(['city.state'])
            ->when($cityId, fn ($qq) => $qq->where('city_id', $cityId))
            ->where('name', 'LIKE', '%'.$q.'%')
            ->orderBy('is_historical')
            ->orderBy('name')
            ->limit(10)
            ->get();

        return response()->json(
            $schools->map(function (School $s) {
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'city_id' => $s->city_id,
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
                        ->ignore($id)
                        ->where(fn ($q) => $q->where('city_id', $request->input('city_id'))),
                ],
                'street' => ['nullable', 'string', 'max:150'],
                'number' => ['nullable', 'string', 'max:20'],
                'neighborhood' => ['nullable', 'string', 'max:120'],
                'complement' => ['nullable', 'string', 'max:120'],
                'cep' => ['nullable', 'regex:/^\d{5}-?\d{3}$/'],

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

    private function syncSchoolWorkshops(School $school, array $workshopIds): void
    {
        $workshopIds = collect($workshopIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $today = now()->toDateString();

        $existing = $school->schoolWorkshops()->get()->keyBy('workshop_id');

        foreach ($workshopIds as $workshopId) {
            $row = $existing->get($workshopId);

            if ($row) {
                if ($row->status !== SchoolWorkshop::STATUS_ACTIVE) {
                    $row->status = SchoolWorkshop::STATUS_ACTIVE;
                    if (! $row->starts_at) {
                        $row->starts_at = $today;
                    }
                    $row->save();
                }
                continue;
            }

            $school->schoolWorkshops()->create([
                'workshop_id' => $workshopId,
                'starts_at' => $today,
                'ends_at' => null,
                'status' => SchoolWorkshop::STATUS_ACTIVE,
            ]);
        }

        foreach ($existing as $workshopId => $row) {
            if (! $workshopIds->contains((int) $workshopId) && $row->status === SchoolWorkshop::STATUS_ACTIVE) {
                $row->status = SchoolWorkshop::STATUS_INACTIVE;
                $row->save();
            }
        }
    }
}


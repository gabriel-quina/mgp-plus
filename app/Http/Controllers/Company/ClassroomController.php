<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClassroomRequest;
use App\Http\Requests\UpdateClassroomRequest;
use App\Models\Classroom;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\Workshop;
use Illuminate\Database\QueryException;

class ClassroomController extends Controller
{
    public function index()
    {
        $q  = request('q', '');
        $yr = request('year');
        $sh = request('shift');

        $classroomsQuery = Classroom::query()
            ->with(['school', 'gradeLevels']);

        if ($q !== '') {
            $classroomsQuery->where('name', 'like', "%{$q}%");
        }

        if ($yr) {
            $classroomsQuery->where('academic_year', (int) $yr);
        }

        if ($sh) {
            $classroomsQuery->where('shift', $sh);
        }

        $classrooms = $classroomsQuery
            ->orderBy('academic_year', 'desc')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $classrooms->getCollection()->transform(function ($classroom) {
            $classroom->total_all_students = $classroom->eligibleEnrollments()->count();
            return $classroom;
        });

        return view('company.classrooms.index', compact('classrooms', 'q', 'yr', 'sh'));
    }

    public function create()
    {
        return view('company.classrooms.create', [
            'schools'      => School::orderBy('name')->pluck('name', 'id'),
            'gradeLevels'  => GradeLevel::orderBy('sequence')->orderBy('name')->pluck('name', 'id'),
            'workshops'    => Workshop::orderBy('name')->pluck('name', 'id'),
            'defaultYear'  => (int) date('Y'),
        ]);
    }

    public function store(StoreClassroomRequest $request)
    {
        $data = $request->validated();

        $classroom = Classroom::create([
            'school_id'       => (int) $data['school_id'],
            'name'            => $data['name'],
            'shift'           => $data['shift'],
            'is_active'       => $request->boolean('is_active'),
            'academic_year'   => (int) $data['academic_year'],
            'grade_level_key' => $data['grade_level_key'],
        ]);

        $classroom->gradeLevels()->sync($data['grade_level_ids']);
        $classroom->workshops()->sync($this->buildWorkshopSyncPayload($data['workshops'] ?? []));

        return redirect()
            ->route('classrooms.show', $classroom)
            ->with('success', 'Turma criada com anos e oficinas vinculadas.');
    }

    public function edit(Classroom $classroom)
    {
        $classroom->load(['gradeLevels', 'workshops']);

        return view('company.classrooms.edit', [
            'classroom'         => $classroom,
            'schools'           => School::orderBy('name')->pluck('name', 'id'),
            'gradeLevels'       => GradeLevel::orderBy('sequence')->orderBy('name')->pluck('name', 'id'),
            'workshops'         => Workshop::orderBy('name')->pluck('name', 'id'),
            'selectedGrades'    => $classroom->gradeLevels->pluck('id')->all(),
            'existingWorkshops' => $classroom->workshops->map(fn ($w) => [
                'id' => $w->id,
                'max_students' => $w->pivot->max_students,
            ])->values()->all(),
        ]);
    }

    public function update(UpdateClassroomRequest $request, Classroom $classroom)
    {
        $data = $request->validated();

        $classroom->update([
            'school_id'       => (int) $data['school_id'],
            'name'            => $data['name'],
            'shift'           => $data['shift'],
            'is_active'       => $request->boolean('is_active'),
            'academic_year'   => (int) $data['academic_year'],
            'grade_level_key' => $data['grade_level_key'],
        ]);

        $classroom->gradeLevels()->sync($data['grade_level_ids']);
        $classroom->workshops()->sync($this->buildWorkshopSyncPayload($data['workshops'] ?? []));

        return redirect()
            ->route('classrooms.show', $classroom)
            ->with('success', 'Turma atualizada com sucesso.');
    }

    public function destroy(Classroom $classroom)
    {
        try {
            $classroom->delete();

            return redirect()
                ->route('classrooms.index')
                ->with('success', 'Turma excluída com sucesso.');
        } catch (QueryException $e) {
            report($e);

            return back()->withErrors([
                'general' => 'Não foi possível excluir a turma (existem vínculos dependentes).',
            ])->withInput();
        }
    }

    private function buildWorkshopSyncPayload(array $workshops): array
    {
        $out = [];

        foreach ($workshops as $row) {
            if (! empty($row['id'])) {
                $out[(int) $row['id']] = [
                    'max_students' => (isset($row['max_students']) && $row['max_students'] !== '')
                        ? (int) $row['max_students']
                        : null,
                ];
            }
        }

        return $out;
    }
}


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
        $q = request('q', '');
        $yr = request('year');
        $sh = request('shift');

        $classroomsQuery = Classroom::query()
            ->with(['school', 'workshop']);

        if ($q !== '') {
            $classroomsQuery->where('grades_signature', 'like', "%{$q}%");
        }

        if ($yr) {
            $classroomsQuery->where('academic_year_id', (int) $yr);
        }

        if ($sh) {
            $classroomsQuery->where('shift', $sh);
        }

        $classrooms = $classroomsQuery
            ->orderBy('academic_year_id', 'desc')
            ->orderBy('group_number')
            ->paginate(20)
            ->withQueryString();

        return view('classrooms.index', compact('classrooms', 'q', 'yr', 'sh'));
    }

    public function create()
    {
        return view('classrooms.create', [
            'schools' => School::orderBy('name')->pluck('name', 'id'),
            'gradeLevels' => GradeLevel::orderBy('sequence')->orderBy('name')->pluck('name', 'id'),
            'workshops' => Workshop::orderBy('name')->pluck('name', 'id'),
            'defaultYear' => (int) date('Y'),
        ]);
    }

    public function store(StoreClassroomRequest $request)
    {
        $data = $request->validated();

        $classroom = Classroom::create([
            'school_id' => (int) $data['school_id'],
            'academic_year_id' => (int) $data['academic_year_id'],
            'shift' => $data['shift'],
            'workshop_id' => (int) $data['workshop_id'],
            'grade_level_ids' => $this->normalizeGradeLevels($data['grade_level_ids']),
            'grades_signature' => $this->buildGradesSignature($data['grade_level_ids']),
            'group_number' => (int) $data['group_number'],
            'capacity_hint' => $data['capacity_hint'] !== null ? (int) $data['capacity_hint'] : null,
            'status' => $data['status'],
        ]);

        return redirect()
            ->route('classrooms.show', $classroom)
            ->with('success', 'Turma criada com anos e oficinas vinculadas.');
    }

    public function show(Classroom $classroom)
    {
        $classroom->load(['school', 'workshop']);

        return view('classrooms.show', [
            'classroom' => $classroom,
        ]);
    }

    public function edit(Classroom $classroom)
    {
        $classroom->load(['workshop']);

        return view('classrooms.edit', [
            'classroom' => $classroom,
            'schools' => School::orderBy('name')->pluck('name', 'id'),
            'gradeLevels' => GradeLevel::orderBy('sequence')->orderBy('name')->pluck('name', 'id'),
            'workshops' => Workshop::orderBy('name')->pluck('name', 'id'),
            'selectedGrades' => $classroom->grade_level_ids ?? [],
        ]);
    }

    public function update(UpdateClassroomRequest $request, Classroom $classroom)
    {
        $data = $request->validated();

        $classroom->update([
            'school_id' => (int) $data['school_id'],
            'academic_year_id' => (int) $data['academic_year_id'],
            'shift' => $data['shift'],
            'workshop_id' => (int) $data['workshop_id'],
            'grade_level_ids' => $this->normalizeGradeLevels($data['grade_level_ids']),
            'grades_signature' => $this->buildGradesSignature($data['grade_level_ids']),
            'group_number' => (int) $data['group_number'],
            'capacity_hint' => $data['capacity_hint'] !== null ? (int) $data['capacity_hint'] : null,
            'status' => $data['status'],
        ]);

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

    private function normalizeGradeLevels(array $gradeLevelIds): array
    {
        return collect($gradeLevelIds)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function buildGradesSignature(array $gradeLevelIds): string
    {
        return implode(',', $this->normalizeGradeLevels($gradeLevelIds));
    }
}

<?php

namespace App\Http\Controllers\Schools;

use App\Http\Controllers\Controller;

use App\Http\Requests\StoreClassroomRequest;
use App\Http\Requests\UpdateClassroomRequest;
use App\Models\Classroom;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\SchoolWorkshop;
use Illuminate\Database\QueryException;

class ClassroomController extends Controller
{
    public function index()
    {
        $q = request('q', '');
        $yr = request('year');
        $sh = request('shift');

        $classroomsQuery = Classroom::query()
            ->with(['school', 'gradeLevels'])
            ->whereNull('parent_classroom_id');

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

        return view('classrooms.index', compact('classrooms', 'q', 'yr', 'sh'));
    }

    public function create()
    {
        $schoolWorkshops = SchoolWorkshop::query()
            ->with(['school', 'workshop'])
            ->orderBy('school_id')
            ->orderBy('workshop_id')
            ->get()
            ->mapWithKeys(fn ($contract) => [
                $contract->id => $contract->school->name.' — '.$contract->workshop->name,
            ]);

        return view('classrooms.create', [
            'schools' => School::orderBy('name')->pluck('name', 'id'),
            'parentClassrooms' => Classroom::orderBy('name')->pluck('name', 'id'),
            'gradeLevels' => GradeLevel::orderBy('sequence')->orderBy('name')->pluck('name', 'id'),
            'schoolWorkshops' => $schoolWorkshops,
            'defaultYear' => (int) date('Y'),
        ]);
    }

    public function store(StoreClassroomRequest $request)
    {
        $data = $request->validated();

        $classroom = Classroom::create([
            'school_id' => (int) $data['school_id'],
            'parent_classroom_id' => $data['parent_classroom_id'] ?? null,
            'school_workshop_id' => (int) $data['school_workshop_id'],
            'name' => $data['name'],
            'shift' => $data['shift'],
            'is_active' => $request->boolean('is_active'),
            'academic_year' => (int) $data['academic_year'],
            'grade_level_key' => $data['grade_level_key'],
        ]);

        $classroom->gradeLevels()->sync($data['grade_level_ids']);

        return redirect()
            ->route('classrooms.show', $classroom)
            ->with('success', 'Turma criada com anos e oficina vinculada.');
    }

    public function edit(Classroom $classroom)
    {
        $classroom->load(['gradeLevels', 'schoolWorkshop.workshop', 'schoolWorkshop.school']);

        $schoolWorkshops = SchoolWorkshop::query()
            ->with(['school', 'workshop'])
            ->orderBy('school_id')
            ->orderBy('workshop_id')
            ->get()
            ->mapWithKeys(fn ($contract) => [
                $contract->id => $contract->school->name.' — '.$contract->workshop->name,
            ]);

        return view('classrooms.edit', [
            'classroom' => $classroom,
            'schools' => School::orderBy('name')->pluck('name', 'id'),
            'parentClassrooms' => Classroom::whereKeyNot($classroom->id)->orderBy('name')->pluck('name', 'id'),
            'gradeLevels' => GradeLevel::orderBy('sequence')->orderBy('name')->pluck('name', 'id'),
            'schoolWorkshops' => $schoolWorkshops,
            'selectedGrades' => $classroom->gradeLevels->pluck('id')->all(),
            'selectedSchoolWorkshop' => $classroom->school_workshop_id,
        ]);
    }

    public function update(UpdateClassroomRequest $request, Classroom $classroom)
    {
        $data = $request->validated();

        $classroom->update([
            'school_id' => (int) $data['school_id'],
            'parent_classroom_id' => $data['parent_classroom_id'] ?? null,
            'school_workshop_id' => (int) $data['school_workshop_id'],
            'name' => $data['name'],
            'shift' => $data['shift'],
            'is_active' => $request->boolean('is_active'),
            'academic_year' => (int) $data['academic_year'],
            'grade_level_key' => $data['grade_level_key'],
        ]);

        $classroom->gradeLevels()->sync($data['grade_level_ids']);

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

}

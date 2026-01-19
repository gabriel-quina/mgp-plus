<?php

namespace App\Http\Controllers\Schools;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClassroomRequest;
use App\Http\Requests\UpdateClassroomRequest;
use App\Models\Classroom;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\StudentEnrollment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SchoolClassroomController extends Controller
{
    public function index(School $school, Request $request)
    {
        $q = $request->get('q', '');
        $yr = $request->get('year');
        $sh = $request->get('shift');

        $classroomsQuery = Classroom::query()
            ->with(['workshop'])
            ->where('school_id', $school->id);

        if ($q !== '') {
            $classroomsQuery->where(function ($query) use ($q) {
                $query->where('grades_signature', 'like', "%{$q}%")
                    ->orWhereHas('workshop', function ($workshopQuery) use ($q) {
                        $workshopQuery->where('name', 'like', "%{$q}%");
                    });
            });
        }

        if ($yr) {
            $classroomsQuery->where('academic_year_id', (int) $yr);
        }

        if ($sh) {
            $classroomsQuery->where('shift', $sh);
        }

        // Ordenação que ajuda a leitura quando mistura base + grupos
        $classrooms = $classroomsQuery
            ->orderBy('academic_year_id', 'desc')
            ->orderBy('group_number')
            ->paginate(20)
            ->withQueryString();

        return view('schools.classrooms.index', [
            'school' => $school,
            'schoolNav' => $school,
            'classrooms' => $classrooms,
            'q' => $q,
            'yr' => $yr,
            'sh' => $sh,
        ]);
    }

    public function show(School $school, Classroom $classroom)
    {
        $atParam = request('at');
        $at = $atParam ? Carbon::parse($atParam) : now();

        $roster = $classroom->rosterAt($at);

        return view('schools.classrooms.show', [
            'school' => $school,
            'schoolNav' => $school,
            'classroom' => $classroom,
            'roster' => $roster,
            'rosterAt' => $at,
        ]);
    }

    public function create(School $school)
    {
        return view('schools.classrooms.create', [
            'school' => $school,
            'schoolNav' => $school,
            'schools' => [$school->id => $school->name],
            'gradeLevels' => $this->gradeLevelsWithEnrollments($school),
            'workshops' => $school->workshops()
                ->select('workshops.id', 'workshops.name')
                ->orderBy('workshops.name')
                ->pluck('workshops.name', 'workshops.id'),
            'defaultYear' => (int) date('Y'),
        ]);
    }

    public function store(School $school, StoreClassroomRequest $request)
    {
        $data = $request->validated();
        $data['school_id'] = $school->id;

        $this->validateGradeLevels($data['grade_level_ids'], $school);

        $gradeLevelIds = Classroom::normalizeGradeLevelIds($data['grade_level_ids']);
        $gradesSignature = Classroom::buildGradesSignature($data['grade_level_ids']);

        $classroom = Classroom::create([
            'school_id' => $data['school_id'],
            'academic_year_id' => (int) $data['academic_year_id'],
            'shift' => $data['shift'],
            'workshop_id' => (int) $data['workshop_id'],
            'grade_level_ids' => $gradeLevelIds,
            'grades_signature' => $gradesSignature,
            'group_number' => (int) $data['group_number'],
            'capacity_hint' => $data['capacity_hint'] !== null ? (int) $data['capacity_hint'] : null,
            'status' => $data['status'],
        ]);

        return redirect()
            ->route('schools.classrooms.show', [$school, $classroom])
            ->with('success', 'Grupo criado com sucesso.');
    }

    public function edit(School $school, Classroom $classroom)
    {
        return view('schools.classrooms.edit', [
            'school' => $school,
            'schoolNav' => $school,
            'classroom' => $classroom,
            'schools' => [$school->id => $school->name],
            'gradeLevels' => $this->gradeLevelsWithEnrollments($school),
            'workshops' => $school->workshops()
                ->select('workshops.id', 'workshops.name')
                ->orderBy('workshops.name')
                ->pluck('workshops.name', 'workshops.id'),
            'selectedGrades' => $classroom->grade_level_ids ?? [],
            'lockAcademicFields' => $classroom->hasAcademicData(),
        ]);
    }

    public function update(School $school, Classroom $classroom, UpdateClassroomRequest $request)
    {
        $data = $request->validated();

        $this->validateGradeLevels($data['grade_level_ids'], $school);

        $normalizedGrades = Classroom::normalizeGradeLevelIds($data['grade_level_ids']);

        $this->ensureEditableAcademicFields($classroom, $normalizedGrades, $data);

        $classroom->update([
            'school_id' => $school->id,
            'academic_year_id' => (int) $data['academic_year_id'],
            'shift' => $data['shift'],
            'workshop_id' => (int) $data['workshop_id'],
            'grade_level_ids' => $normalizedGrades,
            'grades_signature' => Classroom::buildGradesSignature($data['grade_level_ids']),
            'group_number' => (int) $data['group_number'],
            'capacity_hint' => $data['capacity_hint'] !== null ? (int) $data['capacity_hint'] : null,
            'status' => $data['status'],
        ]);

        return redirect()
            ->route('schools.classrooms.show', [$school, $classroom])
            ->with('success', 'Grupo atualizado com sucesso.');
    }

    public function destroy(School $school, Classroom $classroom)
    {
        if ($classroom->hasAcademicData()) {
            return back()->withErrors([
                'general' => 'Não é possível excluir grupos com aulas ou avaliações registradas.',
            ]);
        }

        $classroom->delete();

        return redirect()
            ->route('schools.classrooms.index', $school)
            ->with('success', 'Grupo excluído com sucesso.');
    }

    private function gradeLevelsWithEnrollments(School $school)
    {
        $gradeIds = StudentEnrollment::query()
            ->where('school_id', $school->id)
            ->whereIn('status', StudentEnrollment::ongoingStatuses())
            ->whereNull('ended_at')
            ->whereNotNull('grade_level_id')
            ->distinct()
            ->pluck('grade_level_id');

        return GradeLevel::query()
            ->whereIn('id', $gradeIds)
            ->orderBy('sequence')
            ->orderBy('name')
            ->pluck('name', 'id');
    }

    private function validateGradeLevels(array $gradeLevelIds, School $school): void
    {
        $allowed = $this->gradeLevelsWithEnrollments($school)->keys()->all();
        $invalid = array_diff($gradeLevelIds, $allowed);

        if (! empty($invalid)) {
            throw ValidationException::withMessages([
                'grade_level_ids' => 'Selecione apenas anos com alunos matriculados nesta escola.',
            ]);
        }
    }

    private function ensureEditableAcademicFields(Classroom $classroom, array $normalizedGrades, array $data): void
    {
        if (! $classroom->hasAcademicData()) {
            return;
        }

        $originalGrades = Classroom::normalizeGradeLevelIds($classroom->grade_level_ids ?? []);
        $blockedChanges = [
            'grade_level_ids' => $originalGrades !== $normalizedGrades,
            'workshop_id' => (int) $data['workshop_id'] !== (int) $classroom->workshop_id,
            'shift' => (string) $data['shift'] !== (string) $classroom->shift,
            'academic_year_id' => (int) $data['academic_year_id'] !== (int) $classroom->academic_year_id,
            'group_number' => (int) $data['group_number'] !== (int) $classroom->group_number,
        ];

        $errors = [];
        foreach ($blockedChanges as $field => $blocked) {
            if ($blocked) {
                $errors[$field] = 'Este campo não pode ser alterado após registros acadêmicos.';
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }
}

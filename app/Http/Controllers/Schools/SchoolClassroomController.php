<?php

namespace App\Http\Controllers\Schools;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClassroomRequest;
use App\Models\Classroom;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\StudentEnrollment;
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
        // Segurança básica sem mexer nas rotas
        abort_if((int) $classroom->school_id !== (int) $school->id, 404);

        // Reaproveita suas telas MASTER já prontas
        return redirect()->route('classrooms.show', $classroom);
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

        $gradeLevelIds = collect($data['grade_level_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->sort()
            ->values()
            ->all();
        $gradesSignature = implode(',', $gradeLevelIds);

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
}

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
            ->with(['gradeLevels', 'groupSet.gradeLevels']) // segue o padrão do seu index MASTER
            ->where('school_id', $school->id);

        if ($q !== '') {
            $classroomsQuery->where('name', 'like', "%{$q}%");
        }

        if ($yr) {
            $classroomsQuery->where('academic_year', (int) $yr);
        }

        if ($sh) {
            $classroomsQuery->where('shift', $sh);
        }

        // Ordenação que ajuda a leitura quando mistura base + grupos
        $classrooms = $classroomsQuery
            ->orderByRaw('parent_classroom_id is null desc')
            ->orderBy('academic_year', 'desc')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        // Mesmo “enriquecimento” do MASTER, sem assumir que child tem esse método
        $classrooms->getCollection()->transform(function ($classroom) {
            if (method_exists($classroom, 'eligibleEnrollments') && ! $classroom->parent_classroom_id) {
                $classroom->total_all_students = $classroom->eligibleEnrollments()->count();
            }

            return $classroom;
        });

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
        if ($classroom->parent_classroom_id) {
            return redirect()->route('subclassrooms.show', [
                'parent' => $classroom->parent_classroom_id,
                'classroom' => $classroom->id,
            ]);
        }

        return redirect()->route('classrooms.show', $classroom);
    }

    public function create(School $school)
    {
        return view('schools.classrooms.create', [
            'school' => $school,
            'schoolNav' => $school,
            'schools' => [$school->id => $school->name],
            'parentClassrooms' => Classroom::query()
                ->where('school_id', $school->id)
                ->whereNull('parent_classroom_id')
                ->orderBy('name')
                ->pluck('name', 'id'),
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

        $this->validateParentClassroom($data['parent_classroom_id'] ?? null, $school);
        $this->validateGradeLevels($data['grade_level_ids'], $school);

        $classroom = Classroom::create([
            'school_id' => $data['school_id'],
            'parent_classroom_id' => $data['parent_classroom_id'] ?? null,
            'name' => $data['name'],
            'shift' => $data['shift'],
            'is_active' => $request->boolean('is_active'),
            'academic_year' => (int) $data['academic_year'],
            'grade_level_key' => $data['grade_level_key'],
        ]);

        $classroom->gradeLevels()->sync($data['grade_level_ids']);

        $allowedWorkshopIds = $school->workshops()
            ->select('workshops.id')
            ->pluck('workshops.id')
            ->all();
        $workshopsPayload = collect($data['workshops'] ?? [])
            ->filter(fn ($row) => empty($row['id']) || in_array((int) $row['id'], $allowedWorkshopIds, true))
            ->values()
            ->all();
        $classroom->workshops()->sync($this->buildWorkshopSyncPayload($workshopsPayload));

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

    private function validateParentClassroom(?int $parentClassroomId, School $school): void
    {
        if (! $parentClassroomId) {
            return;
        }

        $exists = Classroom::query()
            ->whereKey($parentClassroomId)
            ->where('school_id', $school->id)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'parent_classroom_id' => 'A turma pai precisa pertencer a esta escola.',
            ]);
        }
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

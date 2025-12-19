<?php

namespace App\Http\Controllers\Schools;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProvisionGroupsRequest;
use App\Models\Classroom;
use App\Models\School;
use App\Models\StudentEnrollment;
use App\Models\Workshop;
use App\Models\WorkshopGroupSet;
use App\Services\WorkshopGroupSetProvisioner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class SchoolGroupsWizardController extends Controller
{
    public function __construct(
        private WorkshopGroupSetProvisioner $provisioner
    ) {}

    public function create(School $school, Request $request)
    {
        $workshops = $school->workshops()
            ->select('workshops.id', 'workshops.name')
            ->orderBy('workshops.name')
            ->get();

        $gradeLevels = $this->gradeLevelsWithEnrollments($school);

        $defaultYear = (int) date('Y');
        $input = [
            'workshop_id' => $request->query('workshop_id'),
            'grade_level_ids' => (array) $request->query('grade_level_ids', []),
            'academic_year' => $request->query('academic_year', $defaultYear),
            'shift' => $request->query('shift', ''),
            'max_students' => $request->query('max_students'),
        ];

        $hasParams = $request->query->has('workshop_id')
            || $request->query->has('grade_level_ids')
            || $request->query->has('academic_year')
            || $request->query->has('shift')
            || $request->query->has('max_students');

        $preview = null;

        if ($hasParams) {
            $preview = $this->buildPreview($school, $workshops, $input);
        }

        return view('schools.groups-wizard.create', [
            'school' => $school,
            'schoolNav' => $school,
            'workshops' => $workshops,
            'gradeLevels' => $gradeLevels,
            'defaultYear' => $defaultYear,
            'input' => $input,
            'preview' => $preview,
        ]);
    }

    public function store(School $school, ProvisionGroupsRequest $request)
    {
        $data = $request->validated();

        $workshop = $school->workshops()
            ->whereKey((int) $data['workshop_id'])
            ->firstOrFail();

        $result = $this->provisioner->provision(
            $school,
            $workshop,
            $data['grade_level_ids'],
            (int) $data['academic_year'],
            $data['shift'],
            $data['max_students'] ?? null
        );

        $createdCount = $result['created_groups']->count();

        session()->flash(
            'success',
            sprintf(
                'Conjunto %s: existiam %d, necessários %d, criados %d.',
                $result['signature'],
                $result['existing_groups'],
                $result['required_groups'],
                $createdCount
            )
        );

        if (Route::has('schools.grade-levels.show')) {
            $firstGrade = collect($data['grade_level_ids'])->map(fn ($id) => (int) $id)->first();
            if ($firstGrade) {
                return redirect()->route('schools.grade-levels.show', [
                    'school' => $school->id,
                    'grade_level' => $firstGrade,
                ]);
            }
        }

        return redirect()->route('schools.classrooms.index', $school);
    }

    private function buildPreview(School $school, $workshops, array $input): array
    {
        $preview = [
            'error' => null,
            'conflict' => null,
            'set' => null,
            'set_url' => null,
            'signature' => null,
            'total_students' => null,
            'max_students' => null,
            'required_groups' => null,
            'existing_groups' => null,
        ];

        $workshopId = $input['workshop_id'] !== null ? (int) $input['workshop_id'] : null;
        $workshop = $workshops->firstWhere('id', $workshopId);

        if (! $workshop) {
            $preview['error'] = 'Selecione uma oficina válida.';
            return $preview;
        }

        $normalizedIds = collect($input['grade_level_ids'] ?? [])
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->sort()
            ->values();

        if ($normalizedIds->isEmpty()) {
            $preview['error'] = 'Selecione ao menos um ano escolar.';
            return $preview;
        }

        $academicYear = (int) ($input['academic_year'] ?? date('Y'));
        $shift = (string) ($input['shift'] ?? '');
        $maxStudents = $input['max_students'] !== null ? (int) $input['max_students'] : null;
        $maxStudents = ($maxStudents === null || $maxStudents <= 0) ? 9999 : $maxStudents;

        $signature = $normalizedIds->implode(',');

        $sets = WorkshopGroupSet::query()
            ->where('school_id', $school->id)
            ->where('workshop_id', $workshop->id)
            ->where('academic_year', $academicYear)
            ->where('shift', $shift)
            ->whereHas('gradeLevels', fn ($q) => $q->whereIn('grade_levels.id', $normalizedIds->all()))
            ->with('gradeLevels')
            ->get();

        foreach ($sets as $existing) {
            if ($existing->grade_levels_signature !== $signature) {
                $intersection = $existing->gradeLevels
                    ->pluck('id')
                    ->intersect($normalizedIds)
                    ->sort()
                    ->values()
                    ->implode(',');

                $preview['conflict'] = sprintf(
                    'Conflito: já existe conjunto %s para esta oficina/contexto. Não é permitido criar %s pois compartilham o(s) ano(s) %s.',
                    $existing->grade_levels_signature,
                    $signature,
                    $intersection
                );
                $preview['set'] = $existing;
                $preview['signature'] = $signature;
                return $preview;
            }

            $preview['set'] = $existing;
        }

        $preview['signature'] = $signature;
        $preview['max_students'] = $maxStudents;

        $totalStudents = StudentEnrollment::query()
            ->where('school_id', $school->id)
            ->whereIn('grade_level_id', $normalizedIds->all())
            ->whereIn('status', StudentEnrollment::ongoingStatuses())
            ->whereNull('ended_at')
            ->count();

        $requiredGroups = max(1, (int) ceil($totalStudents / $maxStudents));

        $existingGroups = $preview['set']
            ? Classroom::query()->where('workshop_group_set_id', $preview['set']->id)->count()
            : 0;

        $preview['total_students'] = $totalStudents;
        $preview['required_groups'] = $requiredGroups;
        $preview['existing_groups'] = $existingGroups;

        if ($preview['set'] && Route::has('schools.workshop-group-sets.show')) {
            $preview['set_url'] = route('schools.workshop-group-sets.show', [
                'school' => $school->id,
                'workshop_group_set' => $preview['set']->id,
            ]);
        }

        return $preview;
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

        return \App\Models\GradeLevel::query()
            ->whereIn('id', $gradeIds)
            ->orderBy('sequence')
            ->orderBy('name')
            ->pluck('name', 'id');
    }
}

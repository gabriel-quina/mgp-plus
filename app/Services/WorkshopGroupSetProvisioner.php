<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\School;
use App\Models\StudentEnrollment;
use App\Models\Workshop;
use App\Models\WorkshopGroupSet;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class WorkshopGroupSetProvisioner
{
    /**
     * Provisiona grupos de oficina de forma idempotente.
     *
     * Validação manual (sem testes automatizados):
     * - Use tinker para chamar provision() com o mesmo conjunto e verificar idempotência.
     * - Tente criar subset sobreposto (ex.: {1,2} e depois {1}) para validar o conflito.
     */
    public function provision(
        School $school,
        Workshop $workshop,
        array $gradeLevelIds,
        int $academicYear,
        string $shift,
        ?int $maxStudents = null
    ): array {
        $normalizedIds = collect($gradeLevelIds)
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->sort()
            ->values();

        if ($normalizedIds->isEmpty()) {
            throw ValidationException::withMessages([
                'grade_level_ids' => 'Selecione ao menos um ano escolar.',
            ]);
        }

        $signature = $normalizedIds->implode(',');
        $maxStudents = ($maxStudents === null || $maxStudents <= 0) ? 9999 : $maxStudents;
        $gradeLevels = \App\Models\GradeLevel::query()
            ->whereIn('id', $normalizedIds->all())
            ->orderBy('sequence')
            ->orderBy('name')
            ->get();
        $label = $this->buildGradeLevelLabel($gradeLevels);
        $shiftLabel = $this->shiftLabel($shift);

        $sets = WorkshopGroupSet::query()
            ->where('school_id', $school->id)
            ->where('workshop_id', $workshop->id)
            ->where('academic_year', $academicYear)
            ->where('shift', $shift)
            ->whereHas('gradeLevels', fn ($q) => $q->whereIn('grade_levels.id', $normalizedIds->all()))
            ->with('gradeLevels')
            ->get();

        $set = null;

        foreach ($sets as $existing) {
            if ($existing->grade_levels_signature !== $signature) {
                $intersection = $existing->gradeLevels
                    ->pluck('id')
                    ->intersect($normalizedIds)
                    ->sort()
                    ->values()
                    ->implode(',');

                throw ValidationException::withMessages([
                    'grade_level_ids' => sprintf(
                        'Conflito: já existe conjunto %s para esta oficina/contexto. Não é permitido criar %s pois compartilham o(s) ano(s) %s.',
                        $existing->grade_levels_signature,
                        $signature,
                        $intersection
                    ),
                ]);
            }

            $set = $existing;
        }

        if (! $set) {
            $set = WorkshopGroupSet::create([
                'school_id' => $school->id,
                'workshop_id' => $workshop->id,
                'academic_year' => $academicYear,
                'shift' => $shift,
                'grade_levels_signature' => $signature,
                'status' => 'active',
            ]);

            $set->gradeLevels()->sync($normalizedIds->all());
        }

        $totalStudents = StudentEnrollment::query()
            ->where('school_id', $school->id)
            ->where('academic_year', $academicYear)
            ->where('shift', $shift)
            ->whereIn('grade_level_id', $normalizedIds->all())
            ->whereIn('status', StudentEnrollment::ongoingStatuses())
            ->whereNull('ended_at')
            ->count();

        $requiredGroups = max(1, (int) ceil($totalStudents / $maxStudents));

        $existingGroups = Classroom::query()
            ->where('workshop_group_set_id', $set->id)
            ->count();

        $createdGroups = collect();

        if ($existingGroups < $requiredGroups) {
            for ($n = $existingGroups + 1; $n <= $requiredGroups; $n++) {
                $classroom = Classroom::create([
                    'school_id' => $school->id,
                    'academic_year' => $academicYear,
                    'shift' => $shift,
                    'workshop_id' => $workshop->id,
                    'workshop_group_set_id' => $set->id,
                    'group_number' => $n,
                    'status' => 'active',
                    'is_active' => true,
                    'name' => sprintf(
                        '%s — %s — %s — %d — Grupo #%d',
                        $workshop->name,
                        $label ?: $signature,
                        $shiftLabel,
                        $academicYear,
                        $n
                    ),
                ]);

                $classroom->gradeLevels()->sync($normalizedIds->all());

                $createdGroups->push($classroom);
            }
        }

        $this->autoAllocateIfSingleGroup(
            $set,
            $workshop,
            $normalizedIds,
            $academicYear,
            $shift
        );

        return [
            'set' => $set,
            'created_groups' => $createdGroups,
            'required_groups' => $requiredGroups,
            'existing_groups' => $existingGroups,
            'total_students' => $totalStudents,
            'signature' => $signature,
        ];
    }

    private function buildGradeLevelLabel(Collection $gradeLevels): string
    {
        $short = $gradeLevels->pluck('short_name')->filter()->all();
        if (! empty($short)) {
            return implode('+', $short);
        }

        $names = $gradeLevels->pluck('name')->filter()->all();

        return ! empty($names) ? implode('+', $names) : '';
    }

    private function shiftLabel(string $shift): string
    {
        return match ($shift) {
            'morning' => 'Manhã',
            'afternoon' => 'Tarde',
            'evening' => 'Noite',
            default => $shift,
        };
    }

    private function autoAllocateIfSingleGroup(
        WorkshopGroupSet $set,
        Workshop $workshop,
        Collection $gradeLevelIds,
        int $academicYear,
        string $shift
    ): void {
        $group = Classroom::query()
            ->where('workshop_group_set_id', $set->id)
            ->where('group_number', 1)
            ->first();

        if (! $group) {
            return;
        }

        $hasAllocations = \App\Models\WorkshopAllocation::query()
            ->where('child_classroom_id', $group->id)
            ->where('workshop_id', $workshop->id)
            ->exists();

        if ($hasAllocations) {
            return;
        }

        $enrollments = StudentEnrollment::query()
            ->where('school_id', $set->school_id)
            ->where('academic_year', $academicYear)
            ->where('shift', $shift)
            ->whereIn('grade_level_id', $gradeLevelIds->all())
            ->whereIn('status', StudentEnrollment::ongoingStatuses())
            ->whereNull('ended_at')
            ->get(['id']);

        foreach ($enrollments as $enrollment) {
            \App\Models\WorkshopAllocation::firstOrCreate([
                'child_classroom_id' => $group->id,
                'workshop_id' => $workshop->id,
                'student_enrollment_id' => $enrollment->id,
            ], [
                'is_locked' => false,
                'note' => null,
            ]);
        }
    }
}

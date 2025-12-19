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
                $createdGroups->push(Classroom::create([
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
                        $signature,
                        $shift,
                        $academicYear,
                        $n
                    ),
                ]));
            }
        }

        return [
            'set' => $set,
            'created_groups' => $createdGroups,
            'required_groups' => $requiredGroups,
            'existing_groups' => $existingGroups,
            'total_students' => $totalStudents,
            'signature' => $signature,
        ];
    }
}

<?php

namespace Database\Seeders\Dev;

use App\Models\Classroom;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\StudentEnrollment;
use App\Models\Workshop;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClassroomSeeder extends Seeder
{
    public function run(): void
    {
        $academicYear = (int) date('Y');

        $statusActive = defined(StudentEnrollment::class.'::STATUS_ACTIVE')
            ? StudentEnrollment::STATUS_ACTIVE
            : 'active';

        $hasEndedAtEnroll = Schema::hasColumn('student_enrollments', 'ended_at');

        $baseEnroll = StudentEnrollment::query()
            ->where('academic_year', $academicYear)
            ->where('status', $statusActive);

        if ($hasEndedAtEnroll) {
            $baseEnroll->whereNull('ended_at');
        }

        $groups = (clone $baseEnroll)
            ->select('school_id', 'shift')
            ->groupBy('school_id', 'shift')
            ->get();

        if ($groups->isEmpty()) {
            return;
        }

        $anchorSchoolId = (clone $baseEnroll)
            ->select('school_id', DB::raw('COUNT(*) as total'))
            ->groupBy('school_id')
            ->orderByDesc('total')
            ->value('school_id');

        $schoolIds = $groups->pluck('school_id')->unique()->values();
        $schoolNames = School::whereIn('id', $schoolIds)->pluck('name', 'id');

        // Workshops padrão (somente os que existem no catálogo de implantação)
        $workshopNames = ['Ingles', 'Artes', 'Xadrez'];
        $workshops = Workshop::whereIn('name', $workshopNames)->get()->keyBy('name');

        $hasIsActiveClassroom   = Schema::hasColumn('classrooms', 'is_active');
        $hasGradeLevelKey       = Schema::hasColumn('classrooms', 'grade_level_key');
        $hasShiftClassroom      = Schema::hasColumn('classrooms', 'shift');
        $hasAcademicYearClass   = Schema::hasColumn('classrooms', 'academic_year');
        $hasParentClassroomId   = Schema::hasColumn('classrooms', 'parent_classroom_id');

        foreach ($groups as $g) {
            $schoolId = (int) $g->school_id;
            $shift    = (string) $g->shift;

            $levelIds = (clone $baseEnroll)
                ->where('school_id', $schoolId)
                ->where('shift', $shift)
                ->distinct()
                ->pluck('grade_level_id')
                ->values();

            if ($levelIds->isEmpty()) {
                continue;
            }

            $levels = GradeLevel::whereIn('id', $levelIds)->orderBy('sequence')->get();

            $didMixed = false;

            // ====== Escola âncora: turma mista (2 níveis) ======
            if ($schoolId === (int) $anchorSchoolId && $levels->count() >= 2) {
                $firstTwo = $levels->take(2)->values();

                $schoolName = $schoolNames[$schoolId] ?? 'Escola';

                $name = sprintf(
                    '%s - Turma Mista (%s + %s, %s)',
                    $schoolName,
                    $firstTwo[0]->short_name ?? $firstTwo[0]->name,
                    $firstTwo[1]->short_name ?? $firstTwo[1]->name,
                    ucfirst($shift)
                );

                $ident = [
                    'school_id' => $schoolId,
                    'name'      => $name,
                ];
                if ($hasShiftClassroom)    $ident['shift'] = $shift;
                if ($hasAcademicYearClass) $ident['academic_year'] = $academicYear;
                if ($hasParentClassroomId) $ident['parent_classroom_id'] = null;

                $attrs = [];
                if ($hasIsActiveClassroom) $attrs['is_active'] = true;
                if ($hasGradeLevelKey) {
                    $attrs['grade_level_key'] = implode('+', $firstTwo->pluck('id')->all());
                }

                $classroom = Classroom::updateOrCreate($ident, $attrs);

                // vincula levels
                if (method_exists($classroom, 'gradeLevels')) {
                    $classroom->gradeLevels()->sync($firstTwo->pluck('id')->all());
                }

                // vincula workshops (Ingles com capacidade menor para forçar subturmas)
                $this->attachWorkshopsWithCapacities($classroom, $workshops, [
                    'Ingles' => 10,
                    'Artes'  => 20,
                    'Xadrez' => 20,
                ]);

                $didMixed = true;
                $levelIds = $levelIds->diff($firstTwo->pluck('id'))->values();
            }

            // ====== Turmas simples (1 nível) ======
            foreach ($levelIds as $idx => $levelId) {
                $gl = $levels->firstWhere('id', $levelId);

                $schoolName = $schoolNames[$schoolId] ?? 'Escola';

                $name = sprintf(
                    '%s - Turma %s (%s, %s)',
                    $schoolName,
                    $this->letter($idx + 1, $didMixed),
                    $gl?->short_name ?? $gl?->name ?? 'Série',
                    ucfirst($shift)
                );

                $ident = [
                    'school_id' => $schoolId,
                    'name'      => $name,
                ];
                if ($hasShiftClassroom)    $ident['shift'] = $shift;
                if ($hasAcademicYearClass) $ident['academic_year'] = $academicYear;
                if ($hasParentClassroomId) $ident['parent_classroom_id'] = null;

                $attrs = [];
                if ($hasIsActiveClassroom) $attrs['is_active'] = true;
                if ($hasGradeLevelKey)     $attrs['grade_level_key'] = (string) $levelId;

                $classroom = Classroom::updateOrCreate($ident, $attrs);

                if (method_exists($classroom, 'gradeLevels')) {
                    $classroom->gradeLevels()->sync([(int) $levelId]);
                }

                $this->attachWorkshopsWithCapacities($classroom, $workshops, [
                    'Ingles' => 20,
                    'Artes'  => 20,
                    'Xadrez' => 20,
                ]);
            }
        }
    }

    private function attachWorkshopsWithCapacities(Classroom $classroom, $workshopsByName, array $capacities): void
    {
        if (!method_exists($classroom, 'workshops')) {
            return;
        }

        $relation = $classroom->workshops();
        $pivotTable = $relation->getTable();
        $hasMaxStudents = Schema::hasColumn($pivotTable, 'max_students');

        foreach ($capacities as $name => $cap) {
            $w = $workshopsByName[$name] ?? null;
            if (!$w) continue;

            if ($hasMaxStudents) {
                $relation->syncWithoutDetaching([$w->id => ['max_students' => $cap]]);
            } else {
                $relation->syncWithoutDetaching([$w->id]);
            }
        }
    }

    private function letter(int $n, bool $mixedAlready): string
    {
        $offset = $mixedAlready ? 1 : 0;
        return chr(65 + $n + $offset - 1);
    }
}


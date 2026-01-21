<?php

namespace Database\Seeders\Dev;

use App\Models\GradeLevel;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class StudentEnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $year = (int) date('Y');

        $schools = School::orderBy('id')->get();

        $levelsQ = GradeLevel::query()->orderBy('sequence');
        if (Schema::hasColumn('grade_levels', 'is_active')) {
            $levelsQ->where('is_active', true);
        }
        $levels = $levelsQ->get();

        $students = Student::orderBy('id')->get();

        if ($schools->isEmpty() || $levels->count() < 2 || $students->isEmpty()) {
            return;
        }

        $statusActive = defined(StudentEnrollment::class.'::STATUS_ACTIVE')
            ? StudentEnrollment::STATUS_ACTIVE
            : 'active';

        $shiftMorning = defined(StudentEnrollment::class.'::SHIFT_MORNING')
            ? StudentEnrollment::SHIFT_MORNING
            : 'morning';

        $shiftAfternoon = defined(StudentEnrollment::class.'::SHIFT_AFTERNOON')
            ? StudentEnrollment::SHIFT_AFTERNOON
            : 'afternoon';

        $shiftEvening = defined(StudentEnrollment::class.'::SHIFT_EVENING')
            ? StudentEnrollment::SHIFT_EVENING
            : 'evening';

        $scopeFirst = defined(StudentEnrollment::class.'::SCOPE_FIRST')
            ? StudentEnrollment::SCOPE_FIRST
            : null;

        $hasEndedAt       = Schema::hasColumn('student_enrollments', 'ended_at');
        $hasStartedAt     = Schema::hasColumn('student_enrollments', 'started_at');
        $hasTransferScope = Schema::hasColumn('student_enrollments', 'transfer_scope');
        $hasOriginSchool  = Schema::hasColumn('student_enrollments', 'origin_school_id');

        $anchorSchool = $schools->first();

        $anchorLevels = $levels->take(2)->values();
        $shiftAnchor = $shiftMorning;

        $otherShifts = [$shiftAfternoon, $shiftEvening, $shiftMorning];

        $total = $students->count();
        $anchorTotal = min(60, max(24, (int) floor($total * 0.5)));
        $perAnchorLevel = max(12, (int) floor($anchorTotal / 2));

        $anchorSlice = $students->slice(0, $perAnchorLevel * 2)->values();
        $otherSlice  = $students->slice($perAnchorLevel * 2)->values();

        $this->seedForSchoolDistributingLevels(
            students: $anchorSlice,
            schoolId: $anchorSchool->id,
            levelIds: $anchorLevels->pluck('id'),
            year: $year,
            shift: $shiftAnchor,
            statusActive: $statusActive,
            scopeFirst: $scopeFirst,
            hasEndedAt: $hasEndedAt,
            hasStartedAt: $hasStartedAt,
            hasTransferScope: $hasTransferScope,
            hasOriginSchool: $hasOriginSchool,
        );

        if ($otherSlice->isNotEmpty()) {
            $rotLevel = 2;
            $rotShift = 0;
            $idx = 0;

            foreach ($schools->where('id', '!=', $anchorSchool->id) as $school) {
                if ($idx >= $otherSlice->count()) break;

                $levelId = $levels[$rotLevel % $levels->count()]->id;
                $shift   = $otherShifts[$rotShift % count($otherShifts)];

                $target = min(28, max(14, (int) floor(($otherSlice->count()) / max(1, $schools->count() - 1))));

                $bucket = $otherSlice->slice($idx, $target)->values();
                $idx += $target;

                $this->seedForSchoolFixedLevel(
                    students: $bucket,
                    schoolId: $school->id,
                    levelId: $levelId,
                    year: $year,
                    shift: $shift,
                    statusActive: $statusActive,
                    scopeFirst: $scopeFirst,
                    hasEndedAt: $hasEndedAt,
                    hasStartedAt: $hasStartedAt,
                    hasTransferScope: $hasTransferScope,
                    hasOriginSchool: $hasOriginSchool,
                );

                $rotLevel++;
                $rotShift++;
            }

            if ($idx < $otherSlice->count()) {
                $leftover = $otherSlice->slice($idx)->values();
                $this->seedForSchoolDistributingLevels(
                    students: $leftover,
                    schoolId: $anchorSchool->id,
                    levelIds: $anchorLevels->pluck('id'),
                    year: $year,
                    shift: $shiftAnchor,
                    statusActive: $statusActive,
                    scopeFirst: $scopeFirst,
                    hasEndedAt: $hasEndedAt,
                    hasStartedAt: $hasStartedAt,
                    hasTransferScope: $hasTransferScope,
                    hasOriginSchool: $hasOriginSchool,
                );
            }
        }
    }

    private function seedForSchoolDistributingLevels(
        Collection $students,
        int $schoolId,
        Collection $levelIds,
        int $year,
        string $shift,
        string $statusActive,
        mixed $scopeFirst,
        bool $hasEndedAt,
        bool $hasStartedAt,
        bool $hasTransferScope,
        bool $hasOriginSchool,
    ): void {
        if ($students->isEmpty() || $levelIds->isEmpty()) return;

        $nLevels = $levelIds->count();

        foreach ($students as $i => $student) {
            $levelId = $levelIds[$i % $nLevels];

            $data = [
                'student_id'     => $student->id,
                'school_id'      => $schoolId,
                'grade_level_id' => $levelId,
                'academic_year'  => $year,
                'shift'          => $shift,
                'status'         => $statusActive,
            ];

            if ($hasStartedAt)     $data['started_at'] = now()->startOfYear();
            if ($hasEndedAt)       $data['ended_at'] = null;
            if ($hasTransferScope && $scopeFirst !== null) $data['transfer_scope'] = $scopeFirst;
            if ($hasOriginSchool)  $data['origin_school_id'] = null;

            StudentEnrollment::updateOrCreate(
                ['student_id' => $student->id, 'academic_year' => $year],
                $data
            );
        }
    }

    private function seedForSchoolFixedLevel(
        Collection $students,
        int $schoolId,
        int $levelId,
        int $year,
        string $shift,
        string $statusActive,
        mixed $scopeFirst,
        bool $hasEndedAt,
        bool $hasStartedAt,
        bool $hasTransferScope,
        bool $hasOriginSchool,
    ): void {
        if ($students->isEmpty()) return;

        foreach ($students as $student) {
            $data = [
                'student_id'     => $student->id,
                'school_id'      => $schoolId,
                'grade_level_id' => $levelId,
                'academic_year'  => $year,
                'shift'          => $shift,
                'status'         => $statusActive,
            ];

            if ($hasStartedAt)     $data['started_at'] = now()->startOfYear();
            if ($hasEndedAt)       $data['ended_at'] = null;
            if ($hasTransferScope && $scopeFirst !== null) $data['transfer_scope'] = $scopeFirst;
            if ($hasOriginSchool)  $data['origin_school_id'] = null;

            StudentEnrollment::updateOrCreate(
                ['student_id' => $student->id, 'academic_year' => $year],
                $data
            );
        }
    }
}


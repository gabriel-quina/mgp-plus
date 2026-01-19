<?php

namespace App\Services\Schools\Queries;

use App\Models\GradeLevel;
use App\Models\School;
use App\Models\StudentEnrollment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class GetSchoolGradeLevelCounts
{
    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\GradeLevel>
     */
    public function execute(School $school, int $academicYear): Collection
    {
        // "Matriculado aguardando início" + "Cursando" (não inclui pré-matrícula nem históricos).
        $eligibleStatuses = [
            StudentEnrollment::STATUS_ENROLLED,
            StudentEnrollment::STATUS_ACTIVE,
        ];
        $applyEnrollmentFilters = function ($q) use ($school, $academicYear, $eligibleStatuses) {
            $q->where('school_id', $school->id)
                ->where('academic_year', $academicYear)
                ->whereIn('status', $eligibleStatuses)
                ->whereNull('ended_at');
        };

        // Anos escolares que têm pelo menos um aluno matriculado nessa escola
        return GradeLevel::query()
            ->whereHas('studentEnrollments', $applyEnrollmentFilters)
            ->withCount([
                'studentEnrollments as enrollments_count' => function ($q) use ($applyEnrollmentFilters) {
                    $applyEnrollmentFilters($q);
                    $q->select(DB::raw('count(distinct student_id)'));
                },
            ])
            ->orderBy('sequence')
            ->orderBy('name')
            ->get();
    }
}

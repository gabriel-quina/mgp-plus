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
    public function execute(School $school): Collection
    {
        // Ano letivo vigente: seguimos o padrão do sistema (ano corrente).
        $currentAcademicYear = (int) now()->year;
        // "Matriculado aguardando início" + "Cursando" (não inclui pré-matrícula nem históricos).
        $eligibleStatuses = [
            StudentEnrollment::STATUS_ENROLLED,
            StudentEnrollment::STATUS_ACTIVE,
        ];
        $applyEnrollmentFilters = function ($q) use ($school, $currentAcademicYear, $eligibleStatuses) {
            $q->where('school_id', $school->id)
                ->where('academic_year', $currentAcademicYear)
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
                'classrooms as classrooms_count' => function ($q) use ($school, $currentAcademicYear) {
                    $q->where('school_id', $school->id)
                        ->where('academic_year', $currentAcademicYear)
                        ->whereNull('parent_classroom_id');
                },
            ])
            ->orderBy('sequence')
            ->orderBy('name')
            ->get();
    }
}

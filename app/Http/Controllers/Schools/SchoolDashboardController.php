<?php

namespace App\Http\Controllers\Schools;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\StudentEnrollment;
use App\Services\Schools\Queries\GetSchoolGradeLevelCounts;
use Illuminate\Support\Facades\DB;

class SchoolDashboardController extends Controller
{
    public function show(School $school)
    {
        $currentAcademicYear = (int) now()->year;

        $school->load([
            'city.state',
            'classrooms' => function ($q) use ($currentAcademicYear) {
                $q->whereNull('parent_classroom_id')
                    ->where('academic_year', $currentAcademicYear)
                    ->where('is_active', true)
                    ->with(['gradeLevels'])
                    ->orderBy('name');
            },
            'workshops',
        ])->loadCount([
            'classrooms as classrooms_count' => function ($q) use ($currentAcademicYear) {
                $q->whereNull('parent_classroom_id')
                    ->where('academic_year', $currentAcademicYear)
                    ->where('is_active', true);
            },
            'workshops as workshops_count',
            'enrollments as enrollments_count' => function ($q) use ($currentAcademicYear) {
                $q->select(DB::raw('count(distinct student_id)'))
                    ->where('academic_year', $currentAcademicYear)
                    ->whereIn('status', [
                        StudentEnrollment::STATUS_ENROLLED,
                        StudentEnrollment::STATUS_ACTIVE,
                    ])
                    ->whereNull('ended_at');
            },
        ]);

        $gradeLevelsWithStudents = (new GetSchoolGradeLevelCounts())->execute($school, $currentAcademicYear);

        return view('schools.dashboard.show', [
            'school' => $school,
            'schoolNav' => $school,
            'gradeLevelsWithStudents' => $gradeLevelsWithStudents,
        ]);
    }
}

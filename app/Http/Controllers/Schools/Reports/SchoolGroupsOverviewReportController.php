<?php

namespace App\Http\Controllers\Schools\Reports;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\ClassroomMembership;
use App\Models\School;
use Illuminate\Http\Request;

class SchoolGroupsOverviewReportController extends Controller
{
    public function index(School $school, Request $request)
    {
        $year = $request->get('year');
        $shift = $request->get('shift');

        $query = Classroom::query()
            ->where('school_id', $school->id)
            ->with(['workshop'])
            ->orderBy('academic_year_id', 'desc')
            ->orderBy('group_number');

        if ($year) {
            $query->where('academic_year_id', (int) $year);
        }
        if ($shift) {
            $query->where('shift', $shift);
        }

        $classrooms = $query->get();

        $counts = ClassroomMembership::query()
            ->whereIn('classroom_id', $classrooms->pluck('id'))
            ->activeAt(now())
            ->get(['classroom_id', 'student_enrollment_id'])
            ->groupBy('classroom_id')
            ->map(fn ($rows) => $rows->pluck('student_enrollment_id')->unique()->count());

        $classrooms->each(function ($c) use ($counts) {
            $c->students_allocated = (int) ($counts[$c->id] ?? 0);
        });

        return view('schools.reports.groups-overview', [
            'school' => $school,
            'schoolNav' => $school,
            'classrooms' => $classrooms,
            'year' => $year,
            'shift' => $shift,
        ]);
    }
}

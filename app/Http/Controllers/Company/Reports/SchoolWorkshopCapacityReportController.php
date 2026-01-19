<?php

namespace App\Http\Controllers\Company\Reports;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\ClassroomMembership;
use App\Models\School;

class SchoolWorkshopCapacityReportController extends Controller
{
    public function index(School $school)
    {
        $classrooms = Classroom::query()
            ->where('school_id', $school->id)
            ->with(['workshop'])
            ->orderBy('academic_year_id', 'desc')
            ->orderBy('group_number')
            ->get();

        $allocatedCounts = ClassroomMembership::query()
            ->whereIn('classroom_id', $classrooms->pluck('id'))
            ->activeAt(now())
            ->get(['classroom_id', 'student_enrollment_id'])
            ->groupBy('classroom_id')
            ->map(fn ($rows) => $rows->pluck('student_enrollment_id')->unique()->count());

        $rows = [];

        foreach ($classrooms as $classroom) {
            $rows[] = (object) [
                'classroom_id' => $classroom->id,
                'classroom_name' => $classroom->name,
                'academic_year' => $classroom->academic_year_id,
                'shift' => $classroom->shift,
                'workshop_id' => $classroom->workshop_id,
                'workshop_name' => $classroom->workshop?->name,
                'capacity' => (int) ($classroom->capacity_hint ?? 0),
                'allocated_students' => (int) ($allocatedCounts[$classroom->id] ?? 0),
            ];
        }

        $rows = collect($rows)
            ->sortBy([
                ['academic_year', 'desc'],
                ['classroom_name', 'asc'],
                ['workshop_name', 'asc'],
            ]);

        return view('schools.reports.workshops-capacity', [
            'school' => $school,
            'schoolNav' => $school,
            'rows' => $rows,
        ]);
    }
}

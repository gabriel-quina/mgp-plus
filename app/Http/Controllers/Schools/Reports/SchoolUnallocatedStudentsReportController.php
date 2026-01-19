<?php

namespace App\Http\Controllers\Schools\Reports;

use App\Http\Controllers\Controller;
use App\Models\ClassroomMembership;
use App\Models\School;
use App\Models\StudentEnrollment;
use Illuminate\Http\Request;

class SchoolUnallocatedStudentsReportController extends Controller
{
    public function index(School $school, Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $allocatedIds = ClassroomMembership::query()
            ->whereHas('enrollment', function ($q) use ($school) {
                $q->where('school_id', $school->id);
            })
            ->activeAt(now())
            ->pluck('student_enrollment_id')
            ->unique();

        $enrollmentsQuery = StudentEnrollment::query()
            ->where('school_id', $school->id)
            ->with(['student', 'gradeLevel']);

        if ($q !== '') {
            $enrollmentsQuery->whereHas('student', function ($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%");
            });
        }

        if ($allocatedIds->isNotEmpty()) {
            $enrollmentsQuery->whereNotIn('id', $allocatedIds->all());
        }

        $enrollments = $enrollmentsQuery
            ->orderBy('academic_year', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('schools.reports.students-unallocated', [
            'school' => $school,
            'schoolNav' => $school,
            'enrollments' => $enrollments,
            'q' => $q,
        ]);
    }
}

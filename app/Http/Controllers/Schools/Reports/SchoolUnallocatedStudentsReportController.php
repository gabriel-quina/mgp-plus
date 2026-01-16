<?php

namespace App\Http\Controllers\Schools\Reports;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\StudentEnrollment;
use App\Models\WorkshopAllocation;
use Illuminate\Http\Request;

class SchoolUnallocatedStudentsReportController extends Controller
{
    public function index(School $school, Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $allocatedIds = WorkshopAllocation::query()
            ->whereHas('studentEnrollment', function ($q) use ($school) {
                $q->where('school_id', $school->id);
            })
            ->pluck('student_enrollment_id')
            ->unique();

        // fallback caso você não tenha relation enrollment() no WorkshopAllocation
        // Se quebrar, troque por um join manual depois.

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
            ->orderBy('school_year', 'desc')
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

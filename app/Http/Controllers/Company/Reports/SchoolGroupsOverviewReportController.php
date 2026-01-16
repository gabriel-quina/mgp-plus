<?php

namespace App\Http\Controllers\Company\Reports;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\School;
use App\Models\WorkshopAllocation;
use Illuminate\Http\Request;

class SchoolGroupsOverviewReportController extends Controller
{
    public function index(School $school, Request $request)
    {
        $year = $request->get('year');
        $shift = $request->get('shift');

        $query = Classroom::query()
            ->where('school_id', $school->id)
            ->with(['gradeLevels', 'workshops'])
            ->orderByRaw('parent_classroom_id is null desc')
            ->orderBy('academic_year', 'desc')
            ->orderBy('name');

        if ($year) {
            $query->where('academic_year', (int) $year);
        }
        if ($shift) {
            $query->where('shift', $shift);
        }

        $classrooms = $query->get();

        // Contagem de alocados por grupo (child_classroom)
        $childIds = $classrooms->whereNotNull('parent_classroom_id')->pluck('id')->values();

        $allocCountByChild = $childIds->isEmpty()
            ? collect()
            : WorkshopAllocation::query()
                ->whereIn('child_classroom_id', $childIds)
                ->get(['child_classroom_id', 'student_enrollment_id'])
                ->groupBy('child_classroom_id')
                ->map(fn ($rows) => $rows->pluck('student_enrollment_id')->unique()->count());

        $classrooms->each(function ($c) use ($allocCountByChild) {
            $c->students_allocated = (int) ($allocCountByChild[$c->id] ?? 0);

            if (method_exists($c, 'eligibleEnrollments') && is_null($c->parent_classroom_id)) {
                $c->total_all_students = $c->eligibleEnrollments()->count();
            }
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

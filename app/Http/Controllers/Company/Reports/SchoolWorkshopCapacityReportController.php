<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\School;
use App\Models\WorkshopAllocation;

class SchoolWorkshopCapacityReportController extends Controller
{
    public function index(School $school)
    {
        // Turmas base (PAI) da escola com suas oficinas pivotadas
        $parents = Classroom::query()
            ->where('school_id', $school->id)
            ->whereNull('parent_classroom_id')
            ->with(['workshops'])
            ->orderBy('academic_year', 'desc')
            ->orderBy('name')
            ->get();

        $workshopIds = $parents->flatMap(fn ($c) => $c->workshops->pluck('id'))->unique();

        $allocatedPerWorkshop = $workshopIds->isEmpty()
            ? collect()
            : WorkshopAllocation::query()
                ->whereIn('workshop_id', $workshopIds)
                ->get(['workshop_id', 'student_enrollment_id'])
                ->groupBy('workshop_id')
                ->map(fn ($rows) => $rows->pluck('student_enrollment_id')->unique()->count());

        $rows = [];

        foreach ($parents as $parent) {
            foreach ($parent->workshops as $wk) {
                $rows[] = (object) [
                    'parent_id' => $parent->id,
                    'parent_name' => $parent->name,
                    'academic_year' => $parent->academic_year,
                    'shift' => $parent->shift,
                    'workshop_id' => $wk->id,
                    'workshop_name' => $wk->name,
                    'capacity' => (int) ($wk->pivot->max_students ?? 0),
                    'allocated_students' => (int) ($allocatedPerWorkshop[$wk->id] ?? 0),
                ];
            }
        }

        $rows = collect($rows)
            ->sortBy([
                ['academic_year', 'desc'],
                ['parent_name', 'asc'],
                ['workshop_name', 'asc'],
            ]);

        return view('schools.reports.workshops-capacity', [
            'school' => $school,
            'schoolNav' => $school,
            'rows' => $rows,
        ]);
    }
}

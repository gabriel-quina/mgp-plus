<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\WorkshopAllocation;
use Illuminate\Http\Request;

class SchoolWorkshopsOverviewReportController extends Controller
{
    public function index(School $school, Request $request)
    {
        // Oficinas ativas na escola via pivot school_workshop
        $school->loadMissing('workshops');

        $workshopIds = $school->workshops->pluck('id');

        $allocatedPerWorkshop = $workshopIds->isEmpty()
            ? collect()
            : WorkshopAllocation::query()
                ->whereIn('workshop_id', $workshopIds)
                ->get(['workshop_id', 'student_enrollment_id'])
                ->groupBy('workshop_id')
                ->map(fn ($rows) => $rows->pluck('student_enrollment_id')->unique()->count());

        $rows = $school->workshops->map(function ($wk) use ($allocatedPerWorkshop) {
            return (object) [
                'id' => $wk->id,
                'name' => $wk->name,
                'allocated_students' => (int) ($allocatedPerWorkshop[$wk->id] ?? 0),
            ];
        })->sortBy('name');

        return view('schools.reports.workshops-overview', [
            'school' => $school,
            'schoolNav' => $school,
            'rows' => $rows,
        ]);
    }
}

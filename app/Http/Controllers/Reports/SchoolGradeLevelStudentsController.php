<?php

// app/Http/Controllers/Reports/SchoolGradeLevelStudentsController.php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\GradeLevel;
use App\Models\School;
use App\Services\GradeLevelStudentReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SchoolGradeLevelStudentsController extends Controller
{
    public function __construct(
        protected GradeLevelStudentReportService $reportService
    ) {}

    public function index(School $school, GradeLevel $gradeLevel, Request $request)
    {
        $start = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))
            : null;

        $end = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))
            : null;

        $report = $this->reportService->forSchoolAndGrade($school, $gradeLevel, $start, $end);

        return view('schools.grade-level-students.index', [
            'school' => $school,
            'gradeLevel' => $gradeLevel,
            'report' => $report,
            'filters' => [
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
            ],
            'schoolNav' => $school,
        ]);
    }
}

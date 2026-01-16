<?php

namespace App\Http\Controllers\Schools\Reports;

use App\Http\Controllers\Controller;
use App\Models\School;

class SchoolReportsController extends Controller
{
    public function index(School $school)
    {
        return view('schools.reports.index', [
            'school' => $school,
            'schoolNav' => $school,
        ]);
    }
}

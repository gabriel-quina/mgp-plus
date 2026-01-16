<?php

use App\Http\Controllers\Schools\Reports\{
    SchoolGroupsOverviewReportController,
    SchoolReportsController,
    SchoolUnallocatedStudentsReportController,
    SchoolWorkshopCapacityReportController,
    SchoolWorkshopsOverviewReportController
};
use App\Models\School;
use Illuminate\Support\Facades\Route;

Route::get('relatorios', [SchoolReportsController::class, 'index'])->name('reports.index');

Route::get('relatorios/turmas', [SchoolGroupsOverviewReportController::class, 'index'])
    ->name('reports.groups.index');

Route::get('relatorios/grupos', function (School $school) {
    return redirect()->route('schools.reports.groups.index', array_merge([
        'school' => $school->id,
    ], request()->query()), 301);
});

Route::get('relatorios/oficinas', [SchoolWorkshopsOverviewReportController::class, 'index'])
    ->name('reports.workshops.index');

Route::get('relatorios/oficinas/capacidade', [SchoolWorkshopCapacityReportController::class, 'index'])
    ->name('reports.workshops.capacity');

Route::get('relatorios/alunos/nao-alocados', [SchoolUnallocatedStudentsReportController::class, 'index'])
    ->name('reports.students.unallocated');

Route::get('anos-escolares/{gradeLevel}/alunos', function (\App\Models\School $school, \App\Models\GradeLevel $gradeLevel) {
    return redirect()->route('schools.students.index', [
        $school,
        'grade_level' => $gradeLevel->id,
    ]);
})
    ->whereNumber('gradeLevel')
    ->name('grade-level-students.index');

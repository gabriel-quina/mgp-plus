<?php

use App\Http\Controllers\Schools\Reports\{
    SchoolGradeLevelStudentsController,
    SchoolGroupsOverviewReportController,
    SchoolReportsController,
    SchoolUnallocatedStudentsReportController,
    SchoolWorkshopCapacityReportController,
    SchoolWorkshopsOverviewReportController
};
use Illuminate\Support\Facades\Route;

Route::get('relatorios', [SchoolReportsController::class, 'index'])->name('reports.index');

Route::get('relatorios/grupos', [SchoolGroupsOverviewReportController::class, 'index'])
    ->name('reports.groups.index');

Route::get('relatorios/oficinas', [SchoolWorkshopsOverviewReportController::class, 'index'])
    ->name('reports.workshops.index');

Route::get('relatorios/oficinas/capacidade', [SchoolWorkshopCapacityReportController::class, 'index'])
    ->name('reports.workshops.capacity');

Route::get('relatorios/alunos/nao-alocados', [SchoolUnallocatedStudentsReportController::class, 'index'])
    ->name('reports.students.unallocated');

Route::get('anos-escolares/{gradeLevel}/alunos', [SchoolGradeLevelStudentsController::class, 'index'])
    ->whereNumber('gradeLevel')
    ->name('grade-level-students.index');

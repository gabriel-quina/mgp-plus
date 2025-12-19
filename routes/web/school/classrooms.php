<?php

use App\Http\Controllers\Schools\SchoolClassroomController;
use App\Http\Controllers\Schools\SchoolGroupsWizardController;
use Illuminate\Support\Facades\Route;

Route::get('grupos/novo-helper', [SchoolGroupsWizardController::class, 'create'])
    ->name('schools.groups-wizard.create');
Route::post('grupos/novo-helper', [SchoolGroupsWizardController::class, 'store'])
    ->name('schools.groups-wizard.store');

Route::resource('grupos', SchoolClassroomController::class)
    ->only(['index', 'show', 'create', 'store'])
    ->names('classrooms')
    ->parameters(['grupos' => 'classroom']);

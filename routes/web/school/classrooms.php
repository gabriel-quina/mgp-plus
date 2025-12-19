<?php

use App\Http\Controllers\Schools\SchoolClassroomController;
use App\Http\Controllers\Schools\SchoolGroupsWizardController;
use Illuminate\Support\Facades\Route;

Route::prefix('grupos/novo-helper')
    ->name('groups-wizard.')
    ->group(function () {
        Route::get('/', [SchoolGroupsWizardController::class, 'create'])
            ->name('create');
        Route::post('/', [SchoolGroupsWizardController::class, 'store'])
            ->name('store');
    });

Route::resource('grupos', SchoolClassroomController::class)
    ->only(['index', 'show', 'create', 'store'])
    ->names('classrooms')
    ->parameters(['grupos' => 'classroom']);

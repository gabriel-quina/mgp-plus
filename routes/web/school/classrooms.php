<?php

use App\Http\Controllers\Schools\SchoolClassroomController;
use App\Http\Controllers\Schools\SchoolClassroomMembershipController;
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

// Página dedicada para movimentação/alocação de alunos (ClassroomMembership)
Route::prefix('grupos/{classroom}/alunos')
    ->name('classrooms.memberships.')
    ->group(function () {
        Route::get('/', [SchoolClassroomMembershipController::class, 'index'])
            ->name('index');

        Route::post('/', [SchoolClassroomMembershipController::class, 'store'])
            ->name('store');

        Route::patch('{membership}/end', [SchoolClassroomMembershipController::class, 'end'])
            ->name('end');
    });


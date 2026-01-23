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

/**
 * Alunos do grupo (ClassroomMembership)
 */
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

/**
 * Aulas (Lesson)
 * Rotas finais: schools.classrooms.lessons.*
 */
Route::prefix('grupos/{classroom}/aulas')
    ->name('classrooms.lessons.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Schools\Classrooms\LessonController::class, 'index'])
            ->name('index');

        Route::get('/create', [\App\Http\Controllers\Schools\Classrooms\LessonController::class, 'create'])
            ->name('create');

        Route::post('/', [\App\Http\Controllers\Schools\Classrooms\LessonController::class, 'store'])
            ->name('store');

        Route::get('/{lesson}', [\App\Http\Controllers\Schools\Classrooms\LessonController::class, 'show'])
            ->name('show');
    });

/**
 * Avaliações / Notas (Assessment)
 * Rotas finais: schools.classrooms.assessments.*
 */
Route::prefix('grupos/{classroom}/avaliacoes')
    ->name('classrooms.assessments.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Schools\Classrooms\AssessmentController::class, 'index'])
            ->name('index');

        Route::get('/create', [\App\Http\Controllers\Schools\Classrooms\AssessmentController::class, 'create'])
            ->name('create');

        Route::post('/', [\App\Http\Controllers\Schools\Classrooms\AssessmentController::class, 'store'])
            ->name('store');

        Route::get('/{assessment}', [\App\Http\Controllers\Schools\Classrooms\AssessmentController::class, 'show'])
            ->name('show');
    });


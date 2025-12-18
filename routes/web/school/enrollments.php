<?php

use App\Http\Controllers\Schools\SchoolEnrollmentController;
use Illuminate\Support\Facades\Route;

/**
 * Assumindo que este arquivo é incluído dentro do grupo:
 * Route::prefix('escolas/{school}')->name('schools.')->group(...)
 */

// Resource principal
Route::resource('matriculas', SchoolEnrollmentController::class)
    ->only(['index', 'create', 'store', 'show', 'edit', 'update'])
    ->names('enrollments')
    ->parameters(['matriculas' => 'enrollment']);

// Ações de workflow (pré-matrícula / efetivação / iniciar curso)
Route::post('matriculas/gerar-pre', [SchoolEnrollmentController::class, 'generatePreEnrollments'])
    ->name('enrollments.generate-pre');

Route::post('matriculas/{enrollment}/confirmar', [SchoolEnrollmentController::class, 'confirm'])
    ->name('enrollments.confirm')
    ->whereNumber('enrollment');

Route::post('matriculas/{enrollment}/iniciar-curso', [SchoolEnrollmentController::class, 'startCourse'])
    ->name('enrollments.start-course')
    ->whereNumber('enrollment');


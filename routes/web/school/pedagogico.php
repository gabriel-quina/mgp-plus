<?php

use App\Http\Controllers\Schools\Classrooms\{AssessmentController, LessonController};
use Illuminate\Support\Facades\Route;

Route::prefix('grupos/{classroom}/oficinas/{workshop}')
    ->whereNumber('classroom')
    ->whereNumber('workshop')
    ->group(function () {

        // AULAS
        Route::name('lessons.')->group(function () {
            Route::get('aulas', [LessonController::class, 'index'])->name('index');
            Route::get('aulas/criar', [LessonController::class, 'create'])->name('create');
            Route::post('aulas', [LessonController::class, 'store'])->name('store');
            Route::get('aulas/{lesson}', [LessonController::class, 'show'])
                ->whereNumber('lesson')
                ->name('show');
        });

        // AVALIAÇÕES
        Route::name('assessments.')->group(function () {
            Route::get('avaliacoes', [AssessmentController::class, 'index'])->name('index');
            Route::get('avaliacoes/criar', [AssessmentController::class, 'create'])->name('create');
            Route::post('avaliacoes', [AssessmentController::class, 'store'])->name('store');
            Route::get('avaliacoes/{assessment}', [AssessmentController::class, 'show'])
                ->whereNumber('assessment')
                ->name('show');
        });
    });

<?php

use App\Http\Controllers\Schools\Classrooms\{AssessmentController, LessonController};
use App\Models\Assessment;
use App\Models\Classroom;
use App\Models\Lesson;
use App\Models\School;
use App\Models\Workshop;
use Illuminate\Support\Facades\Route;

Route::prefix('turmas/{classroom}/oficinas/{workshop}')
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

Route::prefix('grupos/{classroom}/oficinas/{workshop}')
    ->whereNumber('classroom')
    ->whereNumber('workshop')
    ->group(function () {
        Route::get('aulas', function (School $school, Classroom $classroom, Workshop $workshop) {
            return redirect()->route('schools.lessons.index', array_merge([
                'school' => $school->id,
                'classroom' => $classroom->id,
                'workshop' => $workshop->id,
            ], request()->query()), 301);
        });
        Route::get('aulas/criar', function (School $school, Classroom $classroom, Workshop $workshop) {
            return redirect()->route('schools.lessons.create', array_merge([
                'school' => $school->id,
                'classroom' => $classroom->id,
                'workshop' => $workshop->id,
            ], request()->query()), 301);
        });
        Route::post('aulas', [LessonController::class, 'store']);
        Route::get('aulas/{lesson}', function (School $school, Classroom $classroom, Workshop $workshop, Lesson $lesson) {
            return redirect()->route('schools.lessons.show', array_merge([
                'school' => $school->id,
                'classroom' => $classroom->id,
                'workshop' => $workshop->id,
                'lesson' => $lesson->id,
            ], request()->query()), 301);
        })
            ->whereNumber('lesson');

        Route::get('avaliacoes', function (School $school, Classroom $classroom, Workshop $workshop) {
            return redirect()->route('schools.assessments.index', array_merge([
                'school' => $school->id,
                'classroom' => $classroom->id,
                'workshop' => $workshop->id,
            ], request()->query()), 301);
        });
        Route::get('avaliacoes/criar', function (School $school, Classroom $classroom, Workshop $workshop) {
            return redirect()->route('schools.assessments.create', array_merge([
                'school' => $school->id,
                'classroom' => $classroom->id,
                'workshop' => $workshop->id,
            ], request()->query()), 301);
        });
        Route::post('avaliacoes', [AssessmentController::class, 'store']);
        Route::get('avaliacoes/{assessment}', function (
            School $school,
            Classroom $classroom,
            Workshop $workshop,
            Assessment $assessment
        ) {
            return redirect()->route('schools.assessments.show', array_merge([
                'school' => $school->id,
                'classroom' => $classroom->id,
                'workshop' => $workshop->id,
                'assessment' => $assessment->id,
            ], request()->query()), 301);
        })
            ->whereNumber('assessment');
    });

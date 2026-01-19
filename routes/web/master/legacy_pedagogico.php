<?php

use App\Models\Classroom;
use Illuminate\Support\Facades\Route;

Route::prefix('turmas/{classroom}')
    ->whereNumber('classroom')
    ->group(function () {

        Route::get('aulas', function ($classroom) {
            $schoolId = Classroom::query()->findOrFail($classroom)->school_id;

            return redirect()->route('schools.lessons.index', [
                'school' => $schoolId,
                'classroom' => $classroom,
            ]);
        })->name('classrooms.lessons.index');

        Route::get('avaliacoes', function ($classroom) {
            $schoolId = Classroom::query()->findOrFail($classroom)->school_id;

            return redirect()->route('schools.assessments.index', [
                'school' => $schoolId,
                'classroom' => $classroom,
            ]);
        })->name('classrooms.assessments.index');
    });

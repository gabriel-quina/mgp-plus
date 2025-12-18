<?php

use App\Models\Classroom;
use Illuminate\Support\Facades\Route;

Route::prefix('turmas/{classroom}/oficinas/{workshop}')
    ->whereNumber('classroom')
    ->whereNumber('workshop')
    ->group(function () {

        Route::get('aulas', function ($classroom, $workshop) {
            $schoolId = Classroom::query()->findOrFail($classroom)->school_id;

            return redirect()->route('schools.lessons.index', [
                'school' => $schoolId,
                'classroom' => $classroom,
                'workshop' => $workshop,
            ]);
        })->name('classrooms.lessons.index');

        Route::get('avaliacoes', function ($classroom, $workshop) {
            $schoolId = Classroom::query()->findOrFail($classroom)->school_id;

            return redirect()->route('schools.assessments.index', [
                'school' => $schoolId,
                'classroom' => $classroom,
                'workshop' => $workshop,
            ]);
        })->name('classrooms.assessments.index');
    });

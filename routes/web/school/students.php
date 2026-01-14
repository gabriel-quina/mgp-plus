<?php

use App\Http\Controllers\Schools\SchoolStudentController;
use Illuminate\Support\Facades\Route;

Route::resource('alunos', SchoolStudentController::class)
    ->only(['index', 'create', 'store', 'edit', 'update'])
    ->names('students')
    ->parameters(['alunos' => 'student']);

Route::get('alunos/{student}', [SchoolStudentController::class, 'show'])
    ->whereNumber('student')
    ->name('students.show');

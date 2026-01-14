<?php

use App\Http\Controllers\Schools\SchoolStudentController;
use Illuminate\Support\Facades\Route;

Route::get('alunos/{student}', [SchoolStudentController::class, 'show'])
    ->name('students.show');

Route::resource('alunos', SchoolStudentController::class)
    ->only(['index', 'create', 'store', 'edit', 'update'])
    ->names('students')
    ->parameters(['alunos' => 'student']);

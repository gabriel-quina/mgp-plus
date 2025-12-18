<?php

use App\Http\Controllers\Schools\SchoolStudentController;
use Illuminate\Support\Facades\Route;

Route::resource('alunos', SchoolStudentController::class)
    ->only(['index', 'create', 'store', 'show', 'edit', 'update'])
    ->names('students')
    ->parameters(['alunos' => 'student']);

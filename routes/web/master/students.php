<?php

use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::resource('alunos', StudentController::class)
    ->only(['index', 'create', 'store', 'show', 'edit', 'update'])
    ->parameters(['alunos' => 'student'])
    ->names('students');

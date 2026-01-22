<?php

use App\Http\Controllers\Schools\SchoolStudentController;
use Illuminate\Support\Facades\Route;

Route::resource('alunos', SchoolStudentController::class)
    ->names('students')
    ->parameters(['alunos' => 'student']);


<?php

use App\Http\Controllers\Schools\SchoolTeacherController;
use Illuminate\Support\Facades\Route;

Route::resource('professores', SchoolTeacherController::class)
    ->only(['index', 'show'])
    ->names('teachers')
    ->parameters(['professores' => 'teacher']);

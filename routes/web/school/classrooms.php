<?php

use App\Http\Controllers\Schools\SchoolClassroomController;
use Illuminate\Support\Facades\Route;

Route::resource('grupos', SchoolClassroomController::class)
    ->only(['index', 'show'])
    ->names('classrooms')
    ->parameters(['grupos' => 'classroom']);

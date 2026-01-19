<?php

use App\Http\Controllers\Schools\SchoolClassroomController;
use Illuminate\Support\Facades\Route;

Route::resource('grupos', SchoolClassroomController::class)
    ->only(['index', 'show', 'create', 'store'])
    ->names('classrooms')
    ->parameters(['grupos' => 'classroom']);

<?php

use App\Http\Controllers\Schools\SchoolClassroomController;
use Illuminate\Support\Facades\Route;

Route::resource('grupos', SchoolClassroomController::class)
    ->only(['index', 'show', 'create', 'store', 'edit', 'update', 'destroy'])
    ->names('classrooms')
    ->parameters(['grupos' => 'classroom']);

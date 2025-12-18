<?php

use App\Http\Controllers\StudentEnrollmentController;
use Illuminate\Support\Facades\Route;

Route::resource('matriculas', StudentEnrollmentController::class)
    ->names('enrollments')
    ->parameters(['matriculas' => 'enrollment']);


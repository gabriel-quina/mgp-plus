<?php

use App\Http\Controllers\Schools\SchoolDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SchoolDashboardController::class, 'show'])
    ->name('dashboard');

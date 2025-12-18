<?php

use App\Http\Controllers\SchoolController;
use Illuminate\Support\Facades\Route;

Route::get('/api/escolas/buscar', [SchoolController::class, 'search'])->name('schools.search');


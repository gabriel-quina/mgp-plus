<?php

use App\Http\Controllers\Admin\Schools\SchoolController;
use Illuminate\Support\Facades\Route;

Route::get('/api/escolas/buscar', [SchoolController::class, 'search'])->name('schools.search');

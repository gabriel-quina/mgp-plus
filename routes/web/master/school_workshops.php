<?php

use App\Http\Controllers\SchoolWorkshopController;
use Illuminate\Support\Facades\Route;

Route::prefix('escolas/{school}')
    ->whereNumber('school')
    ->group(function () {
        Route::get('workshops', [SchoolWorkshopController::class, 'edit'])->name('schools.workshops.edit');
        Route::post('workshops', [SchoolWorkshopController::class, 'update'])->name('schools.workshops.update');
    });

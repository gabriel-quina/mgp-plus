<?php

use App\Http\Controllers\Company\ClassroomController;
use Illuminate\Support\Facades\Route;

Route::resource('turmas', ClassroomController::class)
    ->except(['show'])
    ->names('classrooms')
    ->parameters(['turmas' => 'classroom']);

Route::get('/turmas/{classroom}', [ClassroomController::class, 'show'])
    ->whereNumber('classroom')
    ->name('classrooms.show');

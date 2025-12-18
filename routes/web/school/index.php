<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Middleware\SubstituteBindings;

Route::prefix('escolas/{school}')
    ->whereNumber('school')
    ->name('schools.')
    ->middleware(SubstituteBindings::class)
    ->group(function () {
        require __DIR__.'/students.php';
        require __DIR__.'/enrollments.php';
        require __DIR__.'/teachers.php';
        require __DIR__.'/classrooms.php';
        require __DIR__.'/reports.php';
        require __DIR__.'/pedagogico.php';
    });

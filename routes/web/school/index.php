<?php

use App\Models\School;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Middleware\SubstituteBindings;

Route::get('escolas/{school:id}', function (School $school) {
    return redirect()->route('schools.dashboard', $school, 301);
})
    ->whereNumber('school')
    ->middleware(SubstituteBindings::class)
    ->middleware('can:access-school,school');

Route::prefix('escola/{school:id}')
    ->whereNumber('school')
    ->name('schools.')
    ->middleware(SubstituteBindings::class)
    ->middleware('can:access-school,school')
    ->scopeBindings()
    ->group(function () {
        require __DIR__.'/dashboard.php';
        require __DIR__.'/students.php';
        require __DIR__.'/enrollments.php';
        require __DIR__.'/teachers.php';
        require __DIR__.'/classrooms.php';
        require __DIR__.'/reports.php';
        require __DIR__.'/pedagogico.php';
    });

<?php

use App\Http\Controllers\Schools\SchoolClassroomController;
use App\Http\Controllers\Schools\SchoolGroupsWizardController;
use App\Models\Classroom;
use App\Models\School;
use Illuminate\Support\Facades\Route;

Route::prefix('turmas/novo-helper')
    ->name('groups-wizard.')
    ->group(function () {
        Route::get('/', [SchoolGroupsWizardController::class, 'create'])
            ->name('create');
        Route::post('/', [SchoolGroupsWizardController::class, 'store'])
            ->name('store');
    });

Route::resource('turmas', SchoolClassroomController::class)
    ->only(['index', 'show', 'create', 'store'])
    ->names('classrooms')
    ->parameters(['turmas' => 'classroom']);

Route::prefix('grupos/novo-helper')->group(function () {
    Route::get('/', function (School $school) {
        return redirect()->route('schools.groups-wizard.create', array_merge([
            'school' => $school->id,
        ], request()->query()), 301);
    });

    Route::post('/', [SchoolGroupsWizardController::class, 'store']);
});

Route::get('grupos', function (School $school) {
    return redirect()->route('schools.classrooms.index', array_merge([
        'school' => $school->id,
    ], request()->query()), 301);
});

Route::get('grupos/create', function (School $school) {
    return redirect()->route('schools.classrooms.create', array_merge([
        'school' => $school->id,
    ], request()->query()), 301);
});

Route::post('grupos', [SchoolClassroomController::class, 'store']);

Route::get('grupos/{classroom}', function (School $school, Classroom $classroom) {
    return redirect()->route('schools.classrooms.show', array_merge([
        'school' => $school->id,
        'classroom' => $classroom->id,
    ], request()->query()), 301);
})
    ->whereNumber('classroom');

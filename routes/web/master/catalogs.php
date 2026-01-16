<?php

use App\Http\Controllers\{
    CityController,
    GradeLevelController
};
use App\Http\Controllers\Admin\Schools\SchoolController;
use Illuminate\Support\Facades\Route;

Route::resource('cidades', CityController::class)
    ->names('cities')
    ->parameters(['cidades' => 'city']);

Route::resource('escolas', SchoolController::class)
    ->names('schools')
    ->parameters(['escolas' => 'school']);

Route::resource('anos-escolares', GradeLevelController::class)
    ->names('grade-levels')
    ->parameters(['anos-escolares' => 'grade-level']);

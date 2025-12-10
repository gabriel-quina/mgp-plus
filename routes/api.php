<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StudentApiController;
use App\Http\Controllers\Api\CityApiController;
use App\Http\Controllers\Api\StateApiController;
use App\Http\Controllers\Api\SchoolApiController;

// Público por enquanto (como já estava), mas com nomes "api.*"
Route::apiResource('students', StudentApiController::class)->names('api.students');
Route::apiResource('cities',   CityApiController::class)->names('api.cities');
Route::apiResource('states',   StateApiController::class)->names('api.states');
Route::apiResource('schools',  SchoolApiController::class)->names('api.schools');

// (Se um dia mover para sanctum, mantenha ->names('api.*') dentro do group)


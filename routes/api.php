<?php
use App\Http\Controllers\Api\StateApiController;
use App\Http\Controllers\Api\CityApiController;
use App\Http\Controllers\Api\SchoolApiController;
use App\Http\Controllers\Api\StudentApiController;
use Illuminate\Support\Facades\Route;

// Protegido, somente com acesso
Route::middleware('auth:sanctum')->group(function () {
//    Route::apiResource('students', StudentApiController::class);
//    Route::apiResource('cities', CityApiController::class);
//    Route::apiResource('states', StateApiController::class);
//    Route::apiResource('school', SchoolApiController::class);
});

// Público por enquanto:
Route::apiResource('students', StudentApiController::class);
Route::apiResource('cities', CityApiController::class);
Route::apiResource('states', StateApiController::class);
Route::apiResource('schools', SchoolApiController::class);


<?php

use App\Http\Controllers\{
    TeacherCityAccessController,
    TeacherController,
    TeacherEngagementController,
    TeachingAssignmentController
};
use Illuminate\Support\Facades\Route;

Route::resource('professores', TeacherController::class)
    ->names('teachers')
    ->parameters(['professores' => 'teacher']);

Route::resource('professores.vinculos', TeacherEngagementController::class)
    ->except(['show'])
    ->names('teacher-engagements')
    ->parameters(['professores' => 'teacher', 'vinculos' => 'teacher_engagement']);

Route::resource('professores.cidades', TeacherCityAccessController::class)
    ->only(['create', 'store', 'destroy'])
    ->names('teacher-city-access')
    ->parameters(['professores' => 'teacher', 'cidades' => 'teacher_city_access']);

Route::resource('professores.alocacoes', TeachingAssignmentController::class)
    ->except(['index', 'show'])
    ->names('teaching-assignments')
    ->parameters(['professores' => 'teacher', 'alocacoes' => 'teaching_assignment']);

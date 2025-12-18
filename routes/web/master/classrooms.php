<?php

use App\Http\Controllers\{ClassroomController, WorkshopDistributionController};
use App\Http\Controllers\Classrooms\{
    ClassroomChildController,
    ClassroomParentController,
    WorkshopClassController
};
use Illuminate\Support\Facades\Route;

Route::resource('turmas', ClassroomController::class)
    ->except(['show'])
    ->names('classrooms')
    ->parameters(['turmas' => 'classroom']);

Route::get('/turmas/{classroom}', [ClassroomParentController::class, 'show'])
    ->whereNumber('classroom')
    ->name('classrooms.show');

Route::get('/turmas/{parent}/subturmas/{classroom}', [ClassroomChildController::class, 'show'])
    ->whereNumber('parent')
    ->whereNumber('classroom')
    ->name('subclassrooms.show');

Route::get('/turmas/{classroom}/oficinas/{workshop}', [WorkshopClassController::class, 'show'])
    ->whereNumber('classroom')
    ->whereNumber('workshop')
    ->name('classrooms.workshops.show');

Route::get('/turmas/{classroom}/oficinas/{workshop}/subturmas', [WorkshopClassController::class, 'indexSubclasses'])
    ->whereNumber('classroom')
    ->whereNumber('workshop')
    ->name('classrooms.workshops.subclasses.index');

Route::get('/turmas/{classroom}/oficinas/{workshop}/preview', [WorkshopDistributionController::class, 'preview'])
    ->whereNumber('classroom')
    ->whereNumber('workshop')
    ->name('classrooms.workshops.preview');

Route::post('/turmas/{classroom}/oficinas/{workshop}/aplicar', [WorkshopDistributionController::class, 'apply'])
    ->whereNumber('classroom')
    ->whereNumber('workshop')
    ->name('classrooms.workshops.apply');

Route::post('/turmas/{classroom}/oficinas/{workshop}/ajustar-capacidade', [WorkshopDistributionController::class, 'adjustCapacity'])
    ->whereNumber('classroom')
    ->whereNumber('workshop')
    ->name('classrooms.workshops.adjust_capacity');

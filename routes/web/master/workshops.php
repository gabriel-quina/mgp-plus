<?php

use App\Http\Controllers\WorkshopController;
use Illuminate\Support\Facades\Route;

Route::controller(WorkshopController::class)->group(function () {
    Route::get('/oficinas', 'index')->name('workshops.index');
    Route::get('/oficinas/nova', 'create')->name('workshops.create');
    Route::post('/oficinas', 'store')->name('workshops.store');
    Route::get('/oficinas/{workshop}/editar', 'edit')->whereNumber('workshop')->name('workshops.edit');
    Route::put('/oficinas/{workshop}', 'update')->whereNumber('workshop')->name('workshops.update');
});

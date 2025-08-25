<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\SchoolController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::get('/alunos',         [StudentController::class, 'index'])->name('students.index');
Route::get('/alunos/novo',    [StudentController::class, 'create'])->name('students.create');
Route::post('/alunos',        [StudentController::class, 'store'])->name('students.store');
Route::get('/cidades',        [CityController::class, 'index'])->name('cities.index');
Route::get('/cidades/nova',   [CityController::class, 'create'])->name('cities.create');
Route::post('/cidades',       [CityController::class, 'store'])->name('cities.store');
Route::get('/escolas',        [SchoolController::class, 'index'])->name('schools.index');
Route::get('/escolas/nova',   [SchoolController::class, 'create'])->name('schools.create');
Route::post('/escolas',       [SchoolController::class, 'store'])->name('schools.store');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

<?php

use App\Http\Controllers\Schools{
    SchoolController
};
use Illuminate\Support\Facades\Route;

Route::resource('escolas', SchoolController::class)
    ->names('schools')
    ->parameters(['escolas' => 'school']);

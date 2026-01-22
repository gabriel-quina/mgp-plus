<?php

use App\Http\Controllers\Company\Schools\SchoolWorkshopController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| School Workshops (Admin/Master)
|--------------------------------------------------------------------------
| URL: /admin/schools/{school}/workshops
| Route names: company.schools.workshops.*
|
| Este arquivo é carregado dentro do grupo:
| Route::prefix('admin')->as('admin.')->group(...)
|
| Portanto, o prefixo de URL já é /admin.
| Aqui definimos os nomes explicitamente como company.* para manter o padrão
| usado na view.
*/

Route::prefix('schools/{school}/workshops')
    ->scopeBindings()
    ->group(function () {
        Route::get('/', [SchoolWorkshopController::class, 'index'])
            ->name('schools.workshops.index');

        Route::post('/', [SchoolWorkshopController::class, 'store'])
            ->name('schools.workshops.store');

        Route::patch('{schoolWorkshop}', [SchoolWorkshopController::class, 'update'])
            ->name('schools.workshops.update');

        Route::delete('{schoolWorkshop}', [SchoolWorkshopController::class, 'destroy'])
            ->name('schools.workshops.destroy');
    });


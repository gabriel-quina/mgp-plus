<?php

use App\Models\{Classroom, School, Workshop};
use Illuminate\Support\Facades\Route;

Route::model('school', School::class);
Route::model('classroom', Classroom::class);
Route::model('workshop', Workshop::class);

require __DIR__.'/auth.php';
require __DIR__.'/web/public.php';

Route::middleware('auth')->group(function () {
    require __DIR__.'/web/profile.php';

    require __DIR__.'/web/master/index.php';
    require __DIR__.'/web/school/index.php';

    // Fase 1 (convivência): mantenha seu pedagógico legado ainda carregado
    // require __DIR__.'/web/pedagogico.php';

    // Fase 2 (migração): remova o require acima e, se quiser, ligue redirects
    // require __DIR__.'/web/master/legacy_pedagogico.php';
});

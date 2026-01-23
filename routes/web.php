<?php

use App\Models\{Classroom, School, Workshop};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::model('school', School::class);
Route::model('classroom', Classroom::class);
Route::model('workshop', Workshop::class);

require __DIR__ . '/auth.php';

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    /** @var \App\Models\User $user */
    $user = Auth::user();

    $actingScope = session('acting_scope');
    $actingSchoolId = session('acting_school_id');

    if ($actingScope === 'school' && $actingSchoolId) {
        return redirect()->route('schools.dashboard', ['school' => (int) $actingSchoolId]);
    }

    // Admin/company: master ou qualquer role de company ou escopo company
    $isAdmin =
        ($user->is_master ?? false)
        || $user->companyRoleAssignments()->exists()
        || ($user->scopeType() === 'company');

    if ($isAdmin) {
        return redirect()->route('admin.dashboard');
    }

    // Usuário de escola: tenta resolver uma escola padrão
    $actingSchool = method_exists($user, 'actingSchool') ? $user->actingSchool() : null;

    if ($actingSchool) {
        session(['acting_scope' => 'school', 'acting_school_id' => (int) $actingSchool->id]);
        return redirect()->route('schools.dashboard', ['school' => (int) $actingSchool->id]);
    }

    abort(404);
});

Route::middleware('auth')->group(function () {
    require __DIR__ . '/web/profile.php';

    Route::prefix('admin')
        ->as('admin.')
        ->group(function () {
            require __DIR__ . '/web/master/dashboard.php';
            require __DIR__ . '/web/master/api.php';
            require __DIR__ . '/web/master/students.php';
            require __DIR__ . '/web/master/teachers.php';
            require __DIR__ . '/web/master/catalogs.php';
            require __DIR__ . '/web/master/enrollments.php';
            require __DIR__ . '/web/master/classrooms.php';
            require __DIR__ . '/web/master/workshops.php';
            require __DIR__ . '/web/master/school_workshops.php';
        });

    require __DIR__ . '/web/school/index.php';
});


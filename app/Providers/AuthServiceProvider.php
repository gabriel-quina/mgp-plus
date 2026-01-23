<?php

namespace App\Providers;

use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

class AuthServiceProvider extends ServiceProvider
{
    private function resolveTeacherForUser(User $user): ?Teacher
    {
        if (Schema::hasTable('teachers') && Schema::hasColumn('teachers', 'cpf') && !empty($user->cpf)) {
            $cpf = preg_replace('/\D+/', '', (string) $user->cpf) ?: null;
            if ($cpf) {
                $t = Teacher::query()->where('cpf', $cpf)->first();
                if ($t) return $t;
            }
        }

        if (Schema::hasTable('teachers') && Schema::hasColumn('teachers', 'email') && !empty($user->email)) {
            $t = Teacher::query()->where('email', $user->email)->first();
            if ($t) return $t;
        }

        return null;
    }

    public function boot(): void
    {
        // Master passa em tudo
        Gate::before(function (User $user) {
            return $user->is_master ? true : null;
        });

        // Acesso à escola (novo RBAC)
        Gate::define('access-school', function (User $user, School $school) {
            return $user->schoolRoleAssignments()
                ->where('school_id', $school->id)
                ->exists();
        });

        // Company (novo RBAC)
        Gate::define('access-company', function (User $user) {
            return $user->companyRoleAssignments()->exists()
                || $user->scopeType() === 'company';
        });

        /**
         * Aulas: por enquanto NÃO usa permission catalog.
         * Regra: qualquer professor (Teacher vinculado ao User) com acesso à escola pode criar.
         */
        Gate::define('lessons.create', function (User $user, School $school) {
            $hasSchoolAccess = $user->schoolRoleAssignments()
                ->where('school_id', $school->id)
                ->exists();

            if (! $hasSchoolAccess) {
                return false;
            }

            return (bool) $this->resolveTeacherForUser($user);
        });
    }
}


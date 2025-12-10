<?php

namespace App\Providers;

use App\Models\City;
use App\Models\School;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Master passa em tudo
        Gate::before(function (User $user) {
            return $user->is_master ? true : null;
        });

        // Acesso por escola com regra municipal/estadual
        Gate::define('access-school', function (User $user, School $school) {

            // Roles diretas na escola
            if ($user->hasRole('school_teacher', $school)) {
                return true;
            }
            if ($user->hasRole('school_director', $school)) {
                return true;
            }
            if ($user->hasRole('school_secretary', $school)) {
                return true;
            }

            // Autoridade municipal
            if ($school->administrative_dependency === 'municipal') {
                if ($user->hasRole('city_education_secretary', $school->city)) {
                    return true;
                }
                if ($user->hasRole('city_coordinator', $school->city)) {
                    return true;
                } // exemplo
            }

            // Autoridade estadual (se você já tiver State)
            if ($school->administrative_dependency === 'state') {
                if (class_exists(State::class) && $school->city && $school->city->state) {
                    if ($user->hasRole('state_education_secretary', $school->city->state)) {
                        return true;
                    }
                }
            }

            // Equipe da empresa com acesso global por role (não-master)
            if ($user->hasRole('company_coordinator')) {
                return true;
            }
            if ($user->hasRole('company_consultant')) {
                return true;
            }

            return false;
        });

        // Acesso à cidade (secretaria municipal)
        Gate::define('access-city', function (User $user, City $city) {
            if ($user->hasRole('city_education_secretary', $city)) {
                return true;
            }
            if ($user->hasRole('city_coordinator', $city)) {
                return true;
            }

            if ($user->hasRole('company_coordinator')) {
                return true;
            }
            if ($user->hasRole('company_consultant')) {
                return true;
            }

            return false;
        });

        // Acesso global de empresa (não-master)
        Gate::define('access-company', function (User $user) {
            return $user->hasRole('company_coordinator')
                || $user->hasRole('company_consultant')
                || $user->role !== null; // caso você use users.role como fallback
        });

        // Permissões de ação internas (exemplos)
        Gate::define('lessons.create', function (User $user, School $school) {
            return $user->canByRole('lessons.create', $school);
        });

        Gate::define('reports.view', function (User $user, School $school) {
            return $user->canByRole('reports.view', $school)
                || $user->canByRole('reports.view', $school->city);
        });
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ActingScope
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $accessibleSchools = $user ? $user->accessibleSchools() : collect();

        /**
         * REGRA DE OURO:
         * - /admin* força escopo company (sempre)
         * - /escola/{school}* força escopo school via hydrateFromRoute()
         */
        if ($request->is('admin') || $request->is('admin/*')) {
            $request->session()->put('acting_scope', 'company');
            $request->session()->forget('acting_school_id');

            view()->share('actingScope', $request->session()->get('acting_scope'));
            view()->share('actingSchoolId', $request->session()->get('acting_school_id'));
            view()->share('scopeSchools', $accessibleSchools);

            return $next($request);
        }

        [$incomingScope, $incomingSchoolId] = $this->extractScopeFromRequest($request);

        if ($user && $incomingScope) {
            $this->persistScope($request, $incomingScope, $incomingSchoolId, $accessibleSchools);
        } elseif (! $request->session()->has('acting_scope')) {
            $this->hydrateFromRoute($request);
        } else {
            // Se já existe acting_scope, e estamos numa rota de escola, atualiza a sessão
            // (garante que "entrar em escola" sempre fixe school)
            $this->hydrateFromRoute($request);
        }

        view()->share('actingScope', $request->session()->get('acting_scope'));
        view()->share('actingSchoolId', $request->session()->get('acting_school_id'));
        view()->share('scopeSchools', $accessibleSchools);

        return $next($request);
    }

    private function extractScopeFromRequest(Request $request): array
    {
        // Como seu form é GET, priorizamos querystring.
        // (input() também funciona em muitos casos, mas query() é o correto para GET)
        $scope = $request->query('scope');
        $schoolId = $request->query('school_id');

        if (is_string($scope) && str_starts_with($scope, 'school:')) {
            return ['school', (int) str_replace('school:', '', $scope)];
        }

        if ($scope === 'school') {
            return ['school', $schoolId];
        }

        if ($scope === 'company') {
            return ['company', null];
        }

        return [null, null];
    }

    private function persistScope(Request $request, string $scope, $schoolId, Collection $accessibleSchools): void
    {
        if ($scope === 'company') {
            $request->session()->put('acting_scope', 'company');
            $request->session()->forget('acting_school_id');

            return;
        }

        $resolvedSchoolId = $this->normalizeSchoolId($schoolId, $accessibleSchools);

        if ($scope === 'school' && $resolvedSchoolId) {
            $request->session()->put('acting_scope', 'school');
            $request->session()->put('acting_school_id', $resolvedSchoolId);
        }
    }

    private function normalizeSchoolId($schoolId, Collection $accessibleSchools): ?int
    {
        $id = is_numeric($schoolId) ? (int) $schoolId : null;

        if (! $id) {
            return null;
        }

        $hasSchool = $accessibleSchools->contains(function ($s, $key) use ($id) {
            if (is_object($s)) {
                return (int) ($s->id ?? 0) === $id;
            }

            if (is_array($s)) {
                $sid = $s['id'] ?? (is_numeric($key) ? (int) $key : null);

                return $sid === $id;
            }

            if (is_numeric($key)) {
                return (int) $key === $id;
            }

            return false;
        });

        return $hasSchool ? $id : null;
    }

    private function hydrateFromRoute(Request $request): void
    {
        $routeSchool = $request->route('school');

        if (! $routeSchool) {
            return;
        }

        $schoolId = is_object($routeSchool) ? ($routeSchool->id ?? null) : $routeSchool;

        if ($schoolId) {
            $request->session()->put('acting_scope', 'school');
            $request->session()->put('acting_school_id', (int) $schoolId);
        }
    }
}


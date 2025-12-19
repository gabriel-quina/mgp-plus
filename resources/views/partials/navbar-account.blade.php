{{-- resources/views/partials/navbar-account.blade.php --}}

@php
    $user = auth()->user();

    $isMaster = (bool) ($user?->is_master ?? false);
    $isCompany = $user && ($user->hasRole('company_coordinator') || $user->hasRole('company_consultant'));
    $scopeSchools = collect($scopeSchools ?? ($schools ?? []));

    /**
     * UI-only: escopo “em atuação”.
     * Persistimos em sessão via middleware (acting_scope / acting_school_id),
     * mas mantemos compatibilidade com querystring (?scope=...&school_id=...).
     */

    // 1) Se estiver dentro de /escolas/{school}/..., isso define o escopo como escola.
    $routeSchool = request()->route('school'); // pode ser Model ou id
    $school = $routeSchool ?: ($schoolNav['school'] ?? null); // se você já usa $schoolNav em algumas telas

    $schoolId = null;
    $schoolName = null;

    if ($school) {
        $schoolId = is_object($school) ? ($school->id ?? null) : $school;
        $schoolName = is_object($school) ? ($school->name ?? null) : null;
    }

    // 2) Querystring para alternar escopo: ?scope=company ou ?scope=school:ID
    $scopeFromQuery = request('scope'); // ex.: 'company' | 'school:12' | 'school' | null
    $schoolIdFromQuery = request('school_id');

    // Compatibilidade: aceita tanto ?scope=school:ID quanto ?scope=school&school_id=ID
    $parsedScopeFromQuery = $scopeFromQuery;
    $parsedSchoolIdFromQuery = $schoolIdFromQuery;

    if (is_string($scopeFromQuery) && str_starts_with($scopeFromQuery, 'school:')) {
        $parsedScopeFromQuery = 'school';
        $parsedSchoolIdFromQuery = str_replace('school:', '', $scopeFromQuery);
    }

    // 3) Sessão (persistida pelo middleware).
    $actingScope = $actingScope ?? session('acting_scope');        // 'company' | 'school' | null
    $actingSchoolId = $actingSchoolId ?? session('acting_school_id'); // int|null

    // Resolve o escopo atual (prioridade: rota escola > sessão > query > default master/company)
    $resolvedScope = null;

    if ($schoolId) {
        $resolvedScope = 'school';
        $actingSchoolId = (int) $schoolId;
    } elseif ($actingScope === 'school' && $actingSchoolId) {
        $resolvedScope = 'school';
    } elseif ($actingScope === 'company') {
        $resolvedScope = 'company';
    } elseif ($parsedScopeFromQuery === 'school') {
        $resolvedScope = 'school';
        $actingSchoolId = (int) $parsedSchoolIdFromQuery;
    } elseif (is_string($scopeFromQuery) && str_starts_with($scopeFromQuery, 'school:')) {
        $resolvedScope = 'school';
        $actingSchoolId = (int) str_replace('school:', '', $scopeFromQuery);
    } elseif ($scopeFromQuery === 'school' && $schoolIdFromQuery) {
        $resolvedScope = 'school';
        $actingSchoolId = (int) $schoolIdFromQuery;
    } elseif ($scopeFromQuery === 'company') {
        $resolvedScope = 'company';
    }

    // Default do MASTER: Empresa (Rede)
    if (!$resolvedScope && $isMaster) {
        $resolvedScope = 'company';
    }

    // Default de quem é “empresa” (não-master)
    if (!$resolvedScope && $isCompany) {
        $resolvedScope = 'company';
    }

    // Normalização de escola (apenas para exibir nome quando só temos o id)
    $selectedSchoolId = $actingSchoolId;

    $normalizeSchoolOption = function ($schoolOption, $key) {
        if (is_object($schoolOption)) {
            return [$schoolOption->id ?? null, $schoolOption->name ?? null];
        }

        if (is_array($schoolOption)) {
            $sid = $schoolOption['id'] ?? (is_numeric($key) ? (int) $key : null);
            $sname = $schoolOption['name'] ?? ($schoolOption['label'] ?? null);

            return [$sid, $sname];
        }

        $sid = (is_numeric($key) && (int) $key > 0) ? (int) $key : null;
        $sname = is_string($schoolOption) ? $schoolOption : null;

        return [$sid, $sname];
    };

    if (! $schoolName && $selectedSchoolId) {
        foreach ($scopeSchools as $key => $s) {
            [$sid, $sname] = $normalizeSchoolOption($s, $key);

            if ($sid === $selectedSchoolId) {
                $schoolName = $sname;
                break;
            }
        }
    }
@endphp

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        {{-- Brand simples (sem "Minha Área" e sem item "Dashboard") --}}
        <a class="navbar-brand" href="{{ route('home') }}">
            <i class="bi bi-mortarboard"></i> MGP+
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#app-topbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="app-topbar">
            {{-- Navegação do escopo (esquerda) --}}
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                @if ($resolvedScope === 'company')
                    {{-- Links do escopo EMPRESA/REDE --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                            Início
                        </a>
                    </li>

                    @if ($isMaster)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('grade-levels.*') ? 'active' : '' }}"
                               href="{{ route('grade-levels.index') }}">
                                Anos Escolares
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('cities.*') ? 'active' : '' }}"
                               href="{{ route('cities.index') }}">
                                Cidades
                            </a>
                        </li>
                    @endif

                    @if ($isMaster || $isCompany)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('schools.*') ? 'active' : '' }}"
                               href="{{ route('schools.index') }}">
                                Escolas
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('workshops.*') ? 'active' : '' }}"
                               href="{{ route('workshops.index') }}">
                                Oficinas
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('teachers.*') ? 'active' : '' }}"
                               href="{{ route('teachers.index') }}">
                                Professores
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('students.*') ? 'active' : '' }}"
                               href="{{ route('students.index') }}">
                                Alunos
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('enrollments.*') ? 'active' : '' }}"
                               href="{{ route('enrollments.index') }}">
                                Matrículas
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('classrooms.*') || request()->routeIs('subclassrooms.*') ? 'active' : '' }}"
                               href="{{ route('classrooms.index') }}">
                                Turmas
                            </a>
                        </li>
                    @endif

                @elseif ($resolvedScope === 'school')
                    {{-- Links do escopo ESCOLA (somente se tiver school id) --}}
                    @if ($actingSchoolId)
                        @if (isset($schoolNav))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('schools.show') ? 'active' : '' }}"
                                   href="{{ route('schools.show', $schoolNav) }}">
                                    Resumo da escola
                                </a>
                            </li>
                        @endif

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('schools.students.*') ? 'active' : '' }}"
                               href="{{ route('schools.students.index', ['school' => $actingSchoolId]) }}">
                                Alunos
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('schools.enrollments.*') ? 'active' : '' }}"
                               href="{{ route('schools.enrollments.index', ['school' => $actingSchoolId]) }}">
                                Matrículas
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('schools.teachers.*') ? 'active' : '' }}"
                               href="{{ route('schools.teachers.index', ['school' => $actingSchoolId]) }}">
                                Professores
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('schools.classrooms.*') ? 'active' : '' }}"
                               href="{{ route('schools.classrooms.index', ['school' => $actingSchoolId]) }}">
                                Grupos
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('schools.reports.*') ? 'active' : '' }}"
                               href="{{ route('schools.reports.index', ['school' => $actingSchoolId]) }}">
                                Relatórios
                            </a>
                        </li>
                    @endif
                @endif
            </ul>

            {{-- Direita: indicador de escopo + menu do usuário --}}
            <div class="d-flex align-items-center gap-2">
                {{-- Indicador de escopo --}}
                <span class="badge bg-secondary">
                    @if ($resolvedScope === 'company')
                        Empresa
                    @elseif ($resolvedScope === 'school')
                        Escola{{ $schoolName ? " — {$schoolName}" : ($actingSchoolId ? " — #{$actingSchoolId}" : '') }}
                    @else
                        —
                    @endif
                </span>

                {{-- Dropdown do usuário --}}
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> {{ $user->name }}
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('dashboard') }}">
                                Meu dashboard
                            </a>
                        </li>

                        <li><hr class="dropdown-divider"></li>

                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item" type="submit">
                                    Sair
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>

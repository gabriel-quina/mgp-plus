{{-- resources/views/partials/navbar-account.blade.php --}}

@php
    $user = auth()->user();

    $isMaster = (bool) ($user?->is_master ?? false);

    // Novo RBAC: escopo do usuário vem de user_scopes
    $isCompany = $user && method_exists($user, 'isCompany') ? $user->isCompany() : false;
    $isSchool  = $user && method_exists($user, 'isSchool') ? $user->isSchool() : false;

    // Lista de escolas acessíveis (novo RBAC)
    $scopeSchools = $user && method_exists($user, 'accessibleSchools')
        ? $user->accessibleSchools()
        : collect();

    // ===== Resolução de escopo (UI) =====
    $actingScope = $actingScope ?? session('acting_scope');
    $actingSchoolId = $actingSchoolId ?? session('acting_school_id');

    // Se está em rota de escola, a rota manda no escopo
    $isSchoolArea = request()->routeIs('schools.*');
    $routeSchool = $isSchoolArea ? request()->route('school') : null;

    $routeSchoolId = null;
    if ($routeSchool) {
        $routeSchoolId = is_object($routeSchool) ? ($routeSchool->id ?? null) : $routeSchool;
    }

    // resolvedScope: prioridade -> rota schools.* -> sessão -> escopo do usuário
    if ($routeSchoolId) {
        $resolvedScope = 'school';
        $actingSchoolId = (int) $routeSchoolId;
    } elseif ($actingScope === 'school' && $actingSchoolId) {
        $resolvedScope = 'school';
    } elseif ($actingScope === 'company') {
        $resolvedScope = 'company';
    } else {
        // fallback pelo escopo do usuário
        if ($isMaster || $isCompany) {
            $resolvedScope = 'company';
        } elseif ($isSchool) {
            $resolvedScope = 'school';
        } else {
            $resolvedScope = 'company'; // fallback seguro para não quebrar UI
        }
    }

    // Se resolvedScope = school mas não tem actingSchoolId,
    // tenta resolver automaticamente se só tiver 1 escola acessível
    if ($resolvedScope === 'school' && ! $actingSchoolId && $scopeSchools->count() === 1) {
        $actingSchoolId = (int) ($scopeSchools->first()?->id ?? 0);
        if ($actingSchoolId > 0) {
            session(['acting_scope' => 'school', 'acting_school_id' => $actingSchoolId]);
        }
    }

@endphp

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        {{-- Brand --}}
        <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
            <i class="bi bi-mortarboard"></i> MGP+
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#app-topbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="app-topbar">
            {{-- ESQUERDA: navegação por escopo --}}
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                @if ($resolvedScope === 'company')
                    {{-- ADMIN / COMPANY --}}
                    @if ($isMaster)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.grade-levels.*') ? 'active' : '' }}"
                               href="{{ route('admin.grade-levels.index') }}">
                                Anos Escolares
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.cities.*') ? 'active' : '' }}"
                               href="{{ route('admin.cities.index') }}">
                                Cidades
                            </a>
                        </li>
                    @endif

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.schools.*') ? 'active' : '' }}"
                           href="{{ route('admin.schools.index') }}">
                            Escolas
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.workshops.*') ? 'active' : '' }}"
                           href="{{ route('admin.workshops.index') }}">
                            Oficinas
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}"
                           href="{{ route('admin.teachers.index') }}">
                            Professores
                        </a>
                    </li>

                @elseif ($resolvedScope === 'school' && $actingSchoolId)
                    {{-- ESCOLA --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('schools.dashboard') ? 'active' : '' }}"
                           href="{{ route('schools.dashboard', ['school' => $actingSchoolId]) }}">
                            Resumo da escola
                        </a>
                    </li>

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
                            Turmas
                        </a>
                    </li>

                @endif
            </ul>

            {{-- DIREITA --}}
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-secondary">
                    {{ $resolvedScope === 'company' ? 'Empresa' : 'Escola' }}
                </span>

                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button"
                            data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> {{ $user->name }}
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            @if ($resolvedScope === 'company')
                                <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                    Dashboard administrativo
                                </a>
                            @elseif ($resolvedScope === 'school' && $actingSchoolId)
                                <a class="dropdown-item"
                                   href="{{ route('schools.dashboard', ['school' => $actingSchoolId]) }}">
                                    Dashboard da escola
                                </a>
                            @endif
                        </li>

                        {{-- (Opcional) seletor de escola, se user tem mais de 1 --}}
                        @if ($resolvedScope === 'school' && $scopeSchools->count() > 1)
                            <li><hr class="dropdown-divider"></li>
                            <li class="px-3 py-1 text-muted small">Trocar escola</li>
                            @foreach ($scopeSchools as $s)
                                <li>
                                    <a class="dropdown-item"
                                       href="{{ route('schools.dashboard', ['school' => $s->id]) }}">
                                        {{ $s->name }}
                                    </a>
                                </li>
                            @endforeach
                        @endif

                        <li><hr class="dropdown-divider"></li>

                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item" type="submit">Sair</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>


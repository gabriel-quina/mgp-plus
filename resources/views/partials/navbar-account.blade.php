{{-- resources/views/partials/navbar-account.blade.php --}}

@php
    $user = auth()->user();

    $isMaster = (bool) ($user?->is_master ?? false);
    $isCompany = $user && ($user->hasRole('company_coordinator') || $user->hasRole('company_consultant'));
    $scopeSchools = collect($scopeSchools ?? ($schools ?? []));

    // ===== Resolução de escopo =====

    $isSchoolArea = request()->routeIs('schools.*');
    $routeSchool = $isSchoolArea ? request()->route('school') : null;

    $school = $routeSchool ?: ($schoolNav['school'] ?? null);

    $schoolId = null;
    $schoolName = null;

    if ($school) {
        $schoolId = is_object($school) ? $school->id ?? null : $school;
        $schoolName = is_object($school) ? $school->name ?? null : null;
    }

    $actingScope = $actingScope ?? session('acting_scope');
    $actingSchoolId = $actingSchoolId ?? session('acting_school_id');

    $resolvedScope = null;

    if ($schoolId) {
        $resolvedScope = 'school';
        $actingSchoolId = (int) $schoolId;
    } elseif ($actingScope === 'school' && $actingSchoolId) {
        $resolvedScope = 'school';
    } elseif ($actingScope === 'company') {
        $resolvedScope = 'company';
    }

    if (! $resolvedScope && ($isMaster || $isCompany)) {
        $resolvedScope = 'company';
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
                           href="{{ route('schools.dashboard', $actingSchoolId) }}">
                            Resumo da escola
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('schools.students.*') ? 'active' : '' }}"
                           href="{{ route('schools.students.index', $actingSchoolId) }}">
                            Alunos
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('schools.enrollments.*') ? 'active' : '' }}"
                           href="{{ route('schools.enrollments.index', $actingSchoolId) }}">
                            Matrículas
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('schools.teachers.*') ? 'active' : '' }}"
                           href="{{ route('schools.teachers.index', $actingSchoolId) }}">
                            Professores
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('schools.classrooms.*') ? 'active' : '' }}"
                           href="{{ route('schools.classrooms.index', $actingSchoolId) }}">
                            Grupos
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('schools.reports.*') ? 'active' : '' }}"
                           href="{{ route('schools.reports.index', $actingSchoolId) }}">
                            Relatórios
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
                                   href="{{ route('schools.dashboard', $actingSchoolId) }}">
                                    Dashboard da escola
                                </a>
                            @endif
                        </li>

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


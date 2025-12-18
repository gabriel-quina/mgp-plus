{{-- resources/views/partials/navbar-account.blade.php --}}

@php
    $user = auth()->user();

    $isMaster = (bool) ($user?->is_master ?? false);
    $isCompany = $user && ($user->hasRole('company_coordinator') || $user->hasRole('company_consultant'));

    /**
     * UI-only: escopo “em atuação”.
     * Por enquanto NÃO existe rota POST nem persistência em sessão: usamos querystring (?scope=...).
     * Quando você implementar, basta trocar a origem para session('acting_scope') / session('acting_school_id').
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

    // 2) Querystring (placeholder) para alternar escopo: ?scope=company ou ?scope=school:ID
    $scopeFromQuery = request('scope'); // ex.: 'company' | 'school:12' | null

    // 3) Sessão (futuro): deixo aqui já pronto, mas você não precisa ter implementado ainda.
    $actingScope = session('acting_scope');        // 'company' | 'school' | null
    $actingSchoolId = session('acting_school_id'); // int|null

    // Resolve o escopo atual (prioridade: rota escola > sessão > query > default master/company)
    $resolvedScope = null;

    if ($schoolId) {
        $resolvedScope = 'school';
        $actingSchoolId = (int) $schoolId;
    } elseif ($actingScope) {
        $resolvedScope = $actingScope;
    } elseif (is_string($scopeFromQuery) && str_starts_with($scopeFromQuery, 'school:')) {
        $resolvedScope = 'school';
        $actingSchoolId = (int) str_replace('school:', '', $scopeFromQuery);
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

    // Valor selecionado no select (para o master já abrir marcado)
    $selectedScopeValue =
        $resolvedScope === 'school' && $actingSchoolId ? "school:{$actingSchoolId}" :
        ($resolvedScope === 'company' ? 'company' : '');
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

            {{-- Direita: seletor de escopo (MASTER) + menu do usuário --}}
            <div class="d-flex align-items-center gap-2">
                {{-- Seletor de escopo (MASTER) - placeholder via GET (?scope=...) --}}
                @if ($isMaster)
                    <form method="GET" action="{{ route('dashboard') }}" class="d-flex align-items-center">
                        <select name="scope" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="company" @selected($selectedScopeValue === 'company')>
                                Empresa (Rede)
                            </option>

                            {{-- FIX: suporta $schools como Collection de Models OU pluck(id=>name) --}}
                            @foreach (($schools ?? []) as $key => $s)
                                @php
                                    $sid = null;
                                    $sname = null;

                                    if (is_object($s)) {
                                        $sid = $s->id ?? null;
                                        $sname = $s->name ?? null;
                                    } elseif (is_array($s)) {
                                        $sid = $s['id'] ?? (is_numeric($key) ? (int) $key : null);
                                        $sname = $s['name'] ?? ($s['label'] ?? null);
                                    } else { // string (provável: pluck name,id => [id => name])
                                        $sid = (is_numeric($key) && (int) $key > 0) ? (int) $key : null;
                                        $sname = is_string($s) ? $s : null;
                                    }
                                @endphp

                                @if ($sid)
                                    <option value="school:{{ $sid }}" @selected($selectedScopeValue === "school:{$sid}")>
                                        Escola — {{ $sname ?? "#{$sid}" }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </form>
                @endif

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


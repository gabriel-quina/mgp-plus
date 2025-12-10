@php
    $user = auth()->user();

    $isMaster = $user?->is_master ?? false;

    $isCompany = $user && ($user->hasRole('company_coordinator') || $user->hasRole('company_consultant'));

    // Se você quiser, dá pra criar depois um método tipo:
    // $canSee = fn($perm) => $isMaster || $user->canByRole($perm);

@endphp

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            <i class="bi bi-mortarboard"></i> MGP+
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#master-topbar"
            aria-controls="master-topbar" aria-expanded="false" aria-label="Alternar navegação">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="master-topbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                {{-- Início --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                        Início
                    </a>
                </li>

                {{-- ======= MASTER-ONLY (estrutural da rede) ======= --}}
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

                {{-- ======= MASTER + EMPRESA (operacional da rede) ======= --}}
                @if ($isMaster || $isCompany)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('schools.index') ? 'active' : '' }}"
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

            </ul>

            {{-- Área de usuário / sair (opcional) --}}
        </div>
    </div>
</nav>

{{-- resources/views/partials/nav/company-links.blade.php --}}

@php
    $user = auth()->user();
    $isMaster = (bool) ($user?->is_master ?? false);
    $isCompany = $user && ($user->hasRole('company_coordinator') || $user->hasRole('company_consultant'));
@endphp

<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
        Início
    </a>
</li>

@if ($isMaster)
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('grade-levels.*') ? 'active' : '' }}" href="{{ route('grade-levels.index') }}">
            Anos Escolares
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('cities.*') ? 'active' : '' }}" href="{{ route('cities.index') }}">
            Cidades
        </a>
    </li>
@endif

@if ($isMaster || $isCompany)
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('schools.*') ? 'active' : '' }}" href="{{ route('schools.index') }}">
            Escolas
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('workshops.*') ? 'active' : '' }}" href="{{ route('workshops.index') }}">
            Oficinas
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('teachers.*') ? 'active' : '' }}" href="{{ route('teachers.index') }}">
            Professores
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('students.*') ? 'active' : '' }}" href="{{ route('students.index') }}">
            Alunos
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('enrollments.*') ? 'active' : '' }}" href="{{ route('enrollments.index') }}">
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


{{-- resources/views/partials/nav/school-links.blade.php --}}
@php
    /** @var \App\Models\School $school */
@endphp

<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('schools.students.*') ? 'active' : '' }}"
       href="{{ route('schools.students.index', ['school' => $school->id]) }}">
        Alunos
    </a>
</li>

<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('schools.enrollments.*') ? 'active' : '' }}"
       href="{{ route('schools.enrollments.index', ['school' => $school->id]) }}">
        Matrículas
    </a>
</li>

<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('schools.teachers.*') ? 'active' : '' }}"
       href="{{ route('schools.teachers.index', ['school' => $school->id]) }}">
        Professores
    </a>
</li>

<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('schools.classrooms.*') ? 'active' : '' }}"
       href="{{ route('schools.classrooms.index', ['school' => $school->id]) }}">
        Grupos
    </a>
</li>

<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('schools.reports.*') ? 'active' : '' }}"
       href="{{ route('schools.reports.index', ['school' => $school->id]) }}">
        Relatórios
    </a>
</li>


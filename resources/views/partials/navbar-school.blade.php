{{--
    Navbar da ESCOLA (visão cliente)

    Observações:
    - Este menu só deve ser incluído pelo layout quando:
      @can('access-school', $schoolNav)
    - Ou seja, aqui assumimos que o usuário já tem acesso ao contexto da escola.

    A ideia agora é controlar itens específicos por permissões finas
    (ex.: relatórios, gestão de oficinas, etc.).
--}}

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        {{-- Marca: nome da escola (home do contexto escola) --}}
        <a class="navbar-brand" href="{{ route('schools.show', $school) }}">
            <i class="bi bi-mortarboard"></i>
            {{ $school->short_name ?? $school->name }}
        </a>

        {{-- Botão do menu mobile --}}
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#school-topbar"
            aria-controls="school-topbar" aria-expanded="false" aria-label="Alternar navegação">
            <span class="navbar-toggler-icon"></span>
        </button>

        {{-- Itens do menu da escola --}}
        <div class="collapse navbar-collapse" id="school-topbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                {{-- Início (visão geral da escola) --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('schools.show') ? 'active' : '' }}"
                        href="{{ route('schools.show', $school) }}">
                        Início
                    </a>
                </li>

                {{-- Alunos da escola --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('schools.students.*') ? 'active' : '' }}"
                        href="{{ route('schools.students.index', $school) }}">
                        Alunos
                    </a>
                </li>

                {{-- Matrículas da escola --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('schools.enrollments.*') ? 'active' : '' }}"
                        href="{{ route('schools.enrollments.index', $school) }}">
                        Matrículas
                    </a>
                </li>

                {{-- Professores da escola --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('schools.teachers.*') ? 'active' : '' }}"
                        href="{{ route('schools.teachers.index', $school) }}">
                        Professores
                    </a>
                </li>

                {{-- Oficinas da escola --}}
                {{--
                    Se você ainda não criou esse gate, pode remover esse @can.
                    Sugestão de permissão futura:
                    - school.workshops.manage
                --}}
                @can('school.workshops.manage', $school)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('schools.workshops.*') ? 'active' : '' }}"
                            href="{{ route('schools.workshops.edit', $school) }}">
                            Oficinas
                        </a>
                    </li>
                @else
                    {{-- fallback temporário sem gate definido --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('schools.workshops.*') ? 'active' : '' }}"
                            href="{{ route('schools.workshops.edit', $school) }}">
                            Oficinas
                        </a>
                    </li>
                @endcan

                {{-- Grupos da escola --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('schools.classrooms.*') ? 'active' : '' }}"
                        href="{{ route('schools.classrooms.index', $school) }}">
                        Grupos
                    </a>
                </li>

                {{-- Relatórios da escola --}}
                @can('reports.view', $school)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('schools.reports.*') || request()->routeIs('schools.grade-level-students.*') ? 'active' : '' }}"
                            href="{{ route('schools.reports.index', $school) }}">
                            Relatórios
                        </a>
                    </li>
                @endcan
            </ul>

            {{-- Espaço pra coisas específicas da escola (futuro) --}}
        </div>
    </div>
</nav>

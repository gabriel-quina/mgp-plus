@extends('layouts.app')

@section('title', 'Alunos da Escola')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">
                Alunos da escola
            </h1>
            <small class="text-muted">
                {{ $school->name }}
            </small>
        </div>

        <a href="{{ route('schools.students.create', $school) }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Novo aluno
        </a>
    </div>

    {{-- Filtros / busca --}}
    <form method="GET" action="{{ route('schools.students.index', $school) }}" class="row gy-2 gx-2 align-items-end mb-3">
        <div class="col-md-4">
            <label for="q" class="form-label">Buscar por nome</label>
            <input type="text" name="q" id="q" value="{{ $search }}" class="form-control"
                placeholder="Digite parte do nome do aluno">
        </div>
        @if ($gradeLevelId)
            <input type="hidden" name="grade_level" value="{{ $gradeLevelId }}">
        @endif

        <div class="col-md-2">
            <button type="submit" class="btn btn-outline-primary w-100">
                <i class="bi bi-search"></i> Buscar
            </button>
        </div>
    </form>

    @if ($gradeLevelFilter)
        @php
            $clearFilterUrl = route('schools.students.index', $school);
            if (! empty($search)) {
                $clearFilterUrl .= '?' . http_build_query(['q' => $search]);
            }
        @endphp
        <div class="alert alert-info d-flex justify-content-between align-items-center">
            <div>
                <strong>Filtro:</strong> Ano escolar {{ $gradeLevelFilter->name ?? $gradeLevelFilter->short_name }}
            </div>
            <a class="btn btn-sm btn-outline-secondary" href="{{ $clearFilterUrl }}">Limpar filtro</a>
        </div>
    @endif

    @if ($enrollments->isEmpty())
        <div class="alert alert-info">
            Nenhum aluno encontrado para esta escola.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-sm table-striped align-middle">
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Ano escolar</th>
                        <th>Ano letivo</th>
                        <th>Detalhes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($enrollments as $enrollment)
                        <tr>
                            <td>
                                {{ $enrollment->student->name ?? '—' }}
                            </td>
                            <td>
                                {{ $enrollment->gradeLevel->name ?? '—' }}
                            </td>
                            <td>
                                {{ $enrollment->academic_year ?? '—' }}
                            </td>
                            <td>
                                @if ($enrollment->student)
                                    <a href="{{ route('schools.students.show', [$school, $enrollment->student, 'return_to' => url()->full()]) }}"
                                        class="btn btn-sm btn-outline-secondary">
                                        Ver aluno
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Paginação --}}
        <div class="mt-3">
            {{ $enrollments->links() }}
        </div>
    @endif
@endsection

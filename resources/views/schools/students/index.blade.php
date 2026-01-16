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
        <div class="alert alert-info d-flex justify-content-between align-items-center">
            <div>
                <strong>Filtro:</strong> Ano escolar {{ $gradeLevelFilter->name ?? $gradeLevelFilter->short_name }}
            </div>
            <a class="btn btn-sm btn-outline-secondary" href="{{ $clearFilterUrl }}">Limpar filtro</a>
        </div>
    @endif

    @if ($gradeLevelId)
        <details class="mb-3">
            <summary class="text-muted">Colunas</summary>
            <form method="GET" action="{{ route('schools.students.index', $school) }}" class="mt-2">
                <input type="hidden" name="grade_level" value="{{ $gradeLevelId }}">
                @if ($search)
                    <input type="hidden" name="q" value="{{ $search }}">
                @endif
                <div class="d-flex flex-wrap gap-3 align-items-center">
                    <label class="form-check-label d-flex align-items-center gap-2">
                        <input class="form-check-input" type="checkbox" name="cols[]" value="avg"
                            @checked($showAvg)>
                        Média de notas
                    </label>
                    <label class="form-check-label d-flex align-items-center gap-2">
                        <input class="form-check-input" type="checkbox" name="cols[]" value="att"
                            @checked($showAtt)>
                        Frequência
                    </label>
                    <button type="submit" class="btn btn-sm btn-outline-primary">Aplicar</button>
                </div>
            </form>
        </details>
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
                        @if ($gradeLevelId && $showAvg)
                            <th style="width: 140px;">Média de notas</th>
                        @endif
                        @if ($gradeLevelId && $showAtt)
                            <th style="width: 140px;">Frequência</th>
                        @endif
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
                            @if ($gradeLevelId && $showAvg)
                                <td>
                                    @php
                                        $avg = $studentMetrics[$enrollment->student_id]['avg'] ?? null;
                                    @endphp
                                    @if ($avg !== null)
                                        {{ number_format($avg, 2, ',', '.') }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            @endif
                            @if ($gradeLevelId && $showAtt)
                                <td>
                                    @php
                                        $att = $studentMetrics[$enrollment->student_id]['att'] ?? null;
                                    @endphp
                                    @if ($att !== null)
                                        {{ number_format($att, 1, ',', '.') }}%
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            @endif
                            <td>
                                {{ $enrollment->gradeLevel->name ?? '—' }}
                            </td>
                            <td>
                                {{ $enrollment->academic_year ?? '—' }}
                            </td>
                            <td>
                                @if ($enrollment->student)
                                    <a href="{{ route('schools.students.show', array_filter([
                                        $school,
                                        $enrollment->student,
                                        'back' => 'students',
                                        'grade_level' => request('grade_level'),
                                        'q' => request('q'),
                                    ], fn ($value) => ! is_null($value) && $value !== '')) }}"
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

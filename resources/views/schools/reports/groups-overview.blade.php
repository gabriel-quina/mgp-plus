@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">Relatório — Grupos</h1>
            <div class="text-muted">{{ $school->short_name ?? $school->name }}</div>
        </div>
        <a href="{{ route('schools.reports.index', $school) }}" class="btn btn-sm btn-outline-secondary">Voltar</a>
    </div>

    <form method="GET" class="card card-body mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label mb-1">Ano letivo</label>
                <input type="number" name="year" class="form-control" value="{{ $year }}">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label mb-1">Turno</label>
                <input type="text" name="shift" class="form-control" value="{{ $shift }}">
            </div>
            <div class="col-12 col-md-3">
                <button class="btn btn-primary">Filtrar</button>
                <a href="{{ route('schools.reports.groups.index', $school) }}" class="btn btn-outline-secondary">Limpar</a>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th>Turma</th>
                        <th>Oficina</th>
                        <th>Ano</th>
                        <th>Turno</th>
                        <th>Séries</th>
                        <th>Grupo</th>
                        <th class="text-end">Alunos alocados</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classrooms as $c)
                        <tr>
                            <td class="fw-semibold">{{ $c->name }}</td>
                            <td>{{ $c->workshop?->name ?? '—' }}</td>
                            <td>{{ $c->academic_year_id ?? '—' }}</td>
                            <td>{{ $c->shift ?? '—' }}</td>
                            <td>{{ $c->grade_level_names }}</td>
                            <td>{{ $c->group_number ?? '—' }}</td>
                            <td class="text-end">{{ $c->students_allocated ?? 0 }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Sem dados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

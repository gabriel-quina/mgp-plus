@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">Relatório — Capacidade x Alocação</h1>
            <div class="text-muted">{{ $school->short_name ?? $school->name }}</div>
        </div>
        <a href="{{ route('schools.reports.index', $school) }}" class="btn btn-sm btn-outline-secondary">Voltar</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th>Turma base</th>
                        <th>Ano</th>
                        <th>Turno</th>
                        <th>Oficina</th>
                        <th class="text-end">Capacidade</th>
                        <th class="text-end">Alocados</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        <tr>
                            <td>{{ $r->parent_name }}</td>
                            <td>{{ $r->academic_year ?? '—' }}</td>
                            <td>{{ $r->shift ?? '—' }}</td>
                            <td class="fw-semibold">{{ $r->workshop_name }}</td>
                            <td class="text-end">{{ $r->capacity ?: '—' }}</td>
                            <td class="text-end">{{ $r->allocated_students }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Sem dados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

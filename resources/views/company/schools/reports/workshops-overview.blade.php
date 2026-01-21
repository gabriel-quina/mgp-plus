@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">Relatório — Oficinas ativas</h1>
            <div class="text-muted">{{ $school->short_name ?? $school->name }}</div>
        </div>
        <a href="{{ route('admin.schools.reports.index', $school) }}" class="btn btn-sm btn-outline-secondary">Voltar</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th>Oficina</th>
                        <th class="text-end">Alunos alocados</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td class="fw-semibold">{{ $row->name }}</td>
                            <td class="text-end">{{ $row->allocated_students }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center text-muted py-4">Nenhuma oficina ativa.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

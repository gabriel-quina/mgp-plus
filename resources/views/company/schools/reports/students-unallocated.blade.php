@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">Relatório — Alunos não alocados</h1>
            <div class="text-muted">{{ $school->short_name ?? $school->name }}</div>
        </div>
        <a href="{{ route('schools.reports.index', $school) }}" class="btn btn-sm btn-outline-secondary">Voltar</a>
    </div>

    <form method="GET" class="card card-body mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <label class="form-label mb-1">Buscar aluno</label>
                <input type="text" name="q" class="form-control" value="{{ $q }}">
            </div>
            <div class="col-12 col-md-3">
                <button class="btn btn-primary">Filtrar</button>
                <a href="{{ route('schools.reports.students.unallocated', $school) }}"
                    class="btn btn-outline-secondary">Limpar</a>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Ano escolar</th>
                        <th class="text-nowrap">Ano letivo</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enrollments as $e)
                        <tr>
                            <td class="fw-semibold">{{ $e->student->name ?? '—' }}</td>
                            <td>{{ $e->gradeLevel->name ?? '—' }}</td>
                            <td>{{ $e->school_year ?? ($e->year ?? '—') }}</td>
                            <td class="text-end">
                                @if (isset($e->student_id))
                                    <a class="btn btn-sm btn-outline-primary"
                                        href="{{ route('students.show', $e->student_id) }}">
                                        Ver aluno
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Nenhum aluno pendente.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $enrollments->links() }}
        </div>
    </div>
@endsection

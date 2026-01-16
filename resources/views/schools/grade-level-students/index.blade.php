@extends('layouts.app')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="h3 mb-0">
                Alunos — {{ $gradeLevel->short_name ?? $gradeLevel->name }}
            </h1>
            <div class="text-muted small">
                Escola: <strong>{{ $school->name }}</strong>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('schools.dashboard', $school) }}" class="btn btn-outline-dark">
                Voltar para escola
            </a>
        </div>
    </div>

    {{-- Filtros de período (opcional) --}}
    <form method="GET" action="{{ route('schools.grade-level-students.index', [$school, $gradeLevel]) }}"
        class="row g-2 mb-3">
        <div class="col-md-3">
            <input type="date" name="start_date" class="form-control" value="{{ $filters['start_date'] ?? '' }}"
                placeholder="Data inicial">
        </div>
        <div class="col-md-3">
            <input type="date" name="end_date" class="form-control" value="{{ $filters['end_date'] ?? '' }}"
                placeholder="Data final">
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-secondary w-100">Filtrar</button>
        </div>
    </form>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Aluno</th>
                            <th style="width: 140px;">Média de notas</th>
                            <th style="width: 140px;">Frequência</th>
                            <th style="width: 160px;" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report as $row)
                            @php
                                $student = $row['student'];
                            @endphp
                            <tr>
                                <td>{{ $student->display_name ?? $student->name }}</td>

                                <td>
                                    @if ($row['avg_points'] !== null)
                                        {{ number_format($row['avg_points'], 2, ',', '.') }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <td>
                                    @if ($row['freq_pct'] !== null)
                                        {{ number_format($row['freq_pct'], 1, ',', '.') }}%
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <td class="text-end">
                                    @if (Route::has('schools.students.show'))
                                        <a href="{{ route('schools.students.show', [$school, $student]) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            Ver aluno
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    Nenhum aluno encontrado para este ano escolar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Avaliações')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h4 mb-1">
                    Avaliações – {{ $classroom->name }}
                </h1>
                <div class="text-muted small">
                    {{ $classroom->school->name ?? '—' }} ·
                    Ano {{ $classroom->academic_year }} ·
                    {{ $classroom->shift ?? '—' }}<br>
                    Oficina: <strong>{{ $workshop->name }}</strong>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('classrooms.assessments.create', [$classroom, $workshop]) }}"
                    class="btn btn-outline-secondary btn-sm">
                    Lançar avaliação
                </a>

                <a href="{{ $backUrl }}" class="btn btn-outline-primary btn-sm">
                    Voltar para o grupo
                </a>
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <span>Avaliações</span>
                <span class="text-muted small">Total: {{ $assessments->total() }}</span>
            </div>

            <div class="card-body p-0">
                @if ($assessments->isEmpty())
                    <p class="p-3 mb-0 text-muted">
                        Nenhuma avaliação lançada ainda para este grupo.
                    </p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Título</th>
                                    <th>Escala</th>
                                    <th class="text-center">Qtde notas</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($assessments as $assessment)
                                    <tr>
                                        <td>{{ optional($assessment->due_at)->format('d/m/Y') ?? '—' }}</td>
                                        <td>{{ $assessment->title }}</td>
                                        <td>
                                            @if ($assessment->scale_type === 'points')
                                                <span class="badge bg-primary">Pontos
                                                    (0–{{ $assessment->max_points }})</span>
                                            @else
                                                <span class="badge bg-secondary">Conceito</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            {{ $assessment->grades_count }}
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('classrooms.assessments.show', [$classroom, $workshop, $assessment]) }}"
                                                class="btn btn-outline-primary btn-sm">
                                                Ver notas
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            @if ($assessments->hasPages())
                <div class="card-footer">
                    {{ $assessments->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

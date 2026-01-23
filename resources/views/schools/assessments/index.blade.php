@extends('layouts.app')

@section('title', 'Avaliações')

@section('content')
    @php
        $classroom->loadMissing(['school', 'gradeLevels', 'schoolWorkshop.workshop']);
        $school = $school ?? $classroom->school;
        $workshop = $classroom->workshop;
    @endphp

    <div class="container-xxl">

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Erro:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h3 mb-1">Avaliações</h1>
                <div class="text-muted small">
                    Escola: <strong>{{ $school->name ?? '—' }}</strong> ·
                    Turma: <strong>{{ $classroom->name }}</strong><br>
                    Oficina: <strong>{{ $workshop?->name ?? '—' }}</strong>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('schools.classrooms.show', [$school, $classroom]) }}"
                   class="btn btn-outline-secondary btn-sm">
                    Voltar para o grupo
                </a>

                @if (!empty($canLaunch) && $canLaunch)
                    <a href="{{ route('schools.classrooms.assessments.create', [$school, $classroom]) }}"
                       class="btn btn-primary btn-sm">
                        Lançar avaliação
                    </a>
                @endif
            </div>
        </div>

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
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 15%;">Data</th>
                                    <th>Título</th>
                                    <th style="width: 20%;">Escala</th>
                                    <th style="width: 15%;" class="text-center">Qtde notas</th>
                                    <th style="width: 15%;" class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($assessments as $assessment)
                                    <tr>
                                        <td>{{ optional($assessment->due_at)->format('d/m/Y') ?? '—' }}</td>
                                        <td>{{ $assessment->title }}</td>
                                        <td>
                                            @if ($assessment->scale_type === 'points')
                                                <span class="badge bg-primary">Pontos (0–{{ $assessment->max_points }})</span>
                                            @else
                                                <span class="badge bg-secondary">Conceito</span>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $assessment->grades_count }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('schools.classrooms.assessments.show', [$school, $classroom, $assessment]) }}"
                                               class="btn btn-outline-secondary btn-sm">
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


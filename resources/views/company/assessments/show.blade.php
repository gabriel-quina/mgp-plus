@extends('layouts.app')

@section('title', 'Avaliação')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h4 mb-1">
                    {{ $assessment->title }} – {{ $classroom->name }}
                </h1>
                <div class="text-muted small">
                    {{ $classroom->school->name ?? '—' }} ·
                    Ano {{ $classroom->academic_year }} ·
                    {{ $classroom->shift ?? '—' }}<br>
                    Oficina: <strong>{{ $workshop->name }}</strong><br>
                    Data: {{ optional($assessment->due_at)->format('d/m/Y') ?? '—' }}
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('admin.schools.assessments.create', ['school' => $school->id, 'classroom' => $classroom->id, 'workshop' => $workshop->id]) }}"
                    class="btn btn-outline-secondary btn-sm">
                    Lançar nova avaliação
                </a>

                <a href="{{ $backUrl }}" class="btn btn-outline-primary btn-sm">
                    Voltar para lista
                </a>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body row g-3">
                <div class="col-md-3">
                    <strong>Escala:</strong><br>
                    @if ($assessment->scale_type === 'points')
                        Pontos (0–{{ $assessment->max_points }})
                    @else
                        Conceito (ruim–excelente)
                    @endif
                </div>
                <div class="col-md-9">
                    <strong>Descrição:</strong><br>
                    <span class="text-muted">{{ $assessment->description ?: '—' }}</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Notas dos alunos ({{ $grades->count() }})
            </div>
            <div class="card-body p-0">
                @if ($grades->isEmpty())
                    <p class="p-3 mb-0 text-muted">
                        Nenhuma nota registrada para esta avaliação.
                    </p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Aluno</th>
                                    <th>Ano</th>
                                    <th>Nota</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($grades as $grade)
                                    @php
                                        $enrollment = $grade->enrollment;
                                        $student = $enrollment->student;
                                    @endphp
                                    <tr>
                                        <td>{{ $student->name }}</td>
                                        <td>{{ optional($enrollment->gradeLevel)->short_name ?? (optional($enrollment->gradeLevel)->name ?? '—') }}
                                        </td>
                                        <td>
                                            @if ($assessment->scale_type === 'points')
                                                {{-- Escala numérica: mostra só o valor em pontos --}}
                                                {{ $grade->score_points !== null ? number_format($grade->score_points, 1, ',', '.') : '—' }}
                                            @else
                                                {{-- Escala conceito: mostra só o conceito, sem pontos ao lado --}}
                                                {{ $grade->score_concept ? ucfirst(str_replace('_', ' ', $grade->score_concept)) : '—' }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
        {{-- Card de estatísticas para pontos --}}
        @if ($assessment->scale_type === 'points' && !empty($numericStats))
            <div class="card mb-3">
                <div class="card-body row g-3">
                    <div class="col-md-3">
                        <strong>Média da turma</strong><br>
                        <span class="fs-4">
                            {{ number_format($numericStats['avg'], 1, ',', '.') }}
                        </span>
                        <span class="text-muted">
                            / {{ number_format($numericStats['max_points'], 1, ',', '.') }}
                        </span>
                    </div>
                    <div class="col-md-3">
                        <strong>Maior nota</strong><br>
                        {{ number_format($numericStats['max'], 1, ',', '.') }}
                    </div>
                    <div class="col-md-3">
                        <strong>Menor nota</strong><br>
                        {{ number_format($numericStats['min'], 1, ',', '.') }}
                    </div>
                    <div class="col-md-3">
                        <strong>Alunos com nota</strong><br>
                        {{ $numericStats['count'] }} de {{ $grades->count() }}
                    </div>
                </div>
            </div>
        @endif

        {{-- Card de distribuição de conceitos --}}
        @if ($assessment->scale_type === 'concept' && !empty($conceptStats))
            <div class="card mb-3">
                <div class="card-body">
                    <strong>Distribuição de conceitos</strong><br>
                    <div class="mt-2 d-flex flex-wrap gap-2">
                        @foreach ($conceptStats['distribution'] as $concept => $count)
                            @php
                                $label = $concept ? ucfirst(str_replace('_', ' ', $concept)) : 'Sem conceito';
                            @endphp
                            <span class="badge bg-secondary">
                                {{ $label }}: {{ $count }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

    </div>
@endsection

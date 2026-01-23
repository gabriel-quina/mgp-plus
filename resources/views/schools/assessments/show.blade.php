@extends('layouts.app')

@section('title', 'Avaliação')

@section('content')
    @php
        $classroom->loadMissing(['school', 'gradeLevels', 'schoolWorkshop.workshop']);
        $school = $school ?? $classroom->school;
        $workshop = $classroom->workshop;
        $due = optional($assessment->due_at);
    @endphp

    <div class="container-xxl">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h3 mb-1">{{ $assessment->title }} – {{ $classroom->name }}</h1>
                <div class="text-muted small">
                    Escola: <strong>{{ $school->name ?? '—' }}</strong> ·
                    Ano letivo: <strong>{{ $classroom->academic_year ?? '—' }}</strong> ·
                    Turno: <strong>{{ $classroom->shift ?? '—' }}</strong><br>
                    Oficina: <strong>{{ $workshop?->name ?? '—' }}</strong><br>
                    Data: {{ $due?->format('d/m/Y') ?? '—' }}
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('schools.classrooms.assessments.create', [$school, $classroom]) }}"
                   class="btn btn-outline-secondary btn-sm">
                    Lançar nova avaliação
                </a>

                <a href="{{ route('schools.classrooms.assessments.index', [$school, $classroom]) }}"
                   class="btn btn-outline-secondary btn-sm">
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

        {{-- Tabela de notas (roster da data) --}}
        <div class="card mb-3">
            <div class="card-header">
                Notas dos alunos ({{ is_countable($roster) ? count($roster) : (method_exists($roster,'count') ? $roster->count() : 0) }})
            </div>
            <div class="card-body p-0">
                @if (empty($roster) || (is_countable($roster) && count($roster) === 0) || (method_exists($roster,'count') && $roster->count() === 0))
                    <p class="p-3 mb-0 text-muted">
                        Nenhum aluno no grupo na data desta avaliação.
                    </p>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Aluno</th>
                                    <th style="width: 16%;">Ano</th>
                                    <th style="width: 20%;">Nota</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roster as $enrollment)
                                    @php
                                        $student = $enrollment->student;
                                        $grade = $gradesByEnrollment[$enrollment->id] ?? null;
                                    @endphp
                                    <tr>
                                        <td>{{ $student->display_name ?? ($student->name ?? '—') }}</td>
                                        <td>
                                            {{ optional($enrollment->gradeLevel)->short_name ?? (optional($enrollment->gradeLevel)->name ?? '—') }}
                                        </td>
                                        <td>
                                            @if ($assessment->scale_type === 'points')
                                                {{ $grade && $grade->score_points !== null ? number_format((float) $grade->score_points, 1, ',', '.') : '—' }}
                                            @else
                                                {{ $grade && $grade->score_concept ? ucfirst(str_replace('_', ' ', $grade->score_concept)) : '—' }}
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

        {{-- Estatísticas para pontos --}}
        @if ($assessment->scale_type === 'points' && !empty($numericStats))
            <div class="card mb-3">
                <div class="card-body row g-3">
                    <div class="col-md-3">
                        <strong>Média da turma</strong><br>
                        <span class="fs-4">
                            {{ number_format((float) $numericStats['avg'], 1, ',', '.') }}
                        </span>
                        <span class="text-muted">
                            / {{ number_format((float) $numericStats['max_points'], 1, ',', '.') }}
                        </span>
                    </div>
                    <div class="col-md-3">
                        <strong>Maior nota</strong><br>
                        {{ number_format((float) $numericStats['max'], 1, ',', '.') }}
                    </div>
                    <div class="col-md-3">
                        <strong>Menor nota</strong><br>
                        {{ number_format((float) $numericStats['min'], 1, ',', '.') }}
                    </div>
                    <div class="col-md-3">
                        <strong>Alunos com nota</strong><br>
                        {{ $numericStats['count'] }}
                    </div>
                </div>
            </div>
        @endif

        {{-- Distribuição de conceitos --}}
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


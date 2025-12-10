@extends('layouts.app')

@section('title', 'Presença da aula')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h4 mb-1">
                    Presença da aula – {{ $lesson->taught_at->format('d/m/Y') }}
                </h1>
                <div class="text-muted small">
                    {{ $classroom->name }} · {{ $classroom->school->name }} ·
                    {{ $classroom->academic_year }} · {{ $classroom->shift_label }}<br>
                    Oficina: {{ $workshop->name }}
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('classrooms.lessons.create', [$classroom, $workshop]) }}"
                    class="btn btn-outline-secondary btn-sm">
                    Lançar nova aula
                </a>

                <a href="{{ $backUrl }}" class="btn btn-outline-primary btn-sm">
                    Voltar para o grupo
                </a>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <strong>Data:</strong><br>
                    {{ $lesson->taught_at->format('d/m/Y') }}
                </div>
                <div class="col-md-6">
                    <strong>Conteúdo:</strong><br>
                    {{ $lesson->topic ?? '—' }}
                </div>
                @if ($lesson->notes)
                    <div class="col-12">
                        <strong>Observações:</strong><br>
                        <span class="text-muted">{{ $lesson->notes }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Presença dos alunos ({{ $attendances->count() }})
            </div>
            <div class="card-body p-0">
                @if ($attendances->isEmpty())
                    <p class="p-3 mb-0 text-muted">
                        Nenhum registro de presença para esta aula.
                    </p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 60%">Aluno</th>
                                    <th style="width: 20%">Matrícula / Ano</th>
                                    <th style="width: 20%" class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($attendances as $attendance)
                                    @php
                                        $enrollment = $attendance->enrollment;
                                        $student = $enrollment->student;
                                    @endphp
                                    <tr>
                                        <td>
                                            {{ $student->name }}
                                            @if (method_exists($student, 'cpf_formatted'))
                                                <br>
                                                <small class="text-muted">
                                                    CPF: {{ $student->cpf_formatted }}
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                Matrícula #{{ $enrollment->id }}<br>
                                                Ano: {{ $enrollment->gradeLevel->name ?? '—' }}
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            @if ($attendance->present)
                                                <span class="badge bg-success">Presente</span>
                                            @else
                                                <span class="badge bg-danger">Faltou</span>
                                            @endif
                                            @if ($attendance->justification)
                                                <br>
                                                <small class="text-muted">
                                                    {{ $attendance->justification }}
                                                </small>
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
    </div>
@endsection

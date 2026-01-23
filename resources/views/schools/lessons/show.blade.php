@extends('layouts.app')

@section('title', 'Aula')

@section('content')
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

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="h3 mb-1">Aula</h1>
            <div class="text-muted small">
                Escola: <strong>{{ $school->name }}</strong> ·
                Turma: <strong>{{ $classroom->name }}</strong>
            </div>
            <div class="text-muted small">
                Data: <strong>{{ $lesson->taught_at?->format('d/m/Y') ?? '—' }}</strong> ·
                Professor(a): <strong>{{ $lesson->teacher?->name ?? '—' }}</strong> ·
                Lançada em: <strong>{{ $lesson->created_at?->format('d/m/Y H:i') ?? '—' }}</strong>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('schools.classrooms.lessons.index', [$school, $classroom]) }}"
               class="btn btn-outline-secondary btn-sm">
                Voltar para Aulas
            </a>
            <a href="{{ route('schools.classrooms.show', [$school, $classroom]) }}"
               class="btn btn-outline-secondary btn-sm">
                Voltar para Turma
            </a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Conteúdo / tópico</div>
                    <div class="fw-semibold">{{ $lesson->topic ?: '—' }}</div>

                    <div class="text-muted small mt-3 mb-1">Observações</div>
                    <div>{{ $lesson->notes ?: '—' }}</div>
                </div>
            </div>
        </div>

        @php
            $total = $roster->count();
            $present = 0;
            $absent = 0;
            $justified = 0;

            foreach ($roster as $enrollment) {
                $att = $attendanceByEnrollment->get($enrollment->id);
                if (!$att) continue;

                if ($att->present) {
                    $present++;
                } else {
                    if (!empty($att->justification)) $justified++;
                    else $absent++;
                }
            }
        @endphp

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-2">Resumo</div>

                    <div class="d-flex flex-wrap gap-3">
                        <div>
                            <div class="text-muted small">Alunos no roster</div>
                            <div class="h3 mb-0">{{ $total }}</div>
                        </div>
                        <div>
                            <div class="text-muted small">Presente</div>
                            <div class="h3 mb-0">{{ $present }}</div>
                        </div>
                        <div>
                            <div class="text-muted small">Falta</div>
                            <div class="h3 mb-0">{{ $absent }}</div>
                        </div>
                        <div>
                            <div class="text-muted small">Falta justificada</div>
                            <div class="h3 mb-0">{{ $justified }}</div>
                        </div>
                    </div>

                    <div class="small text-muted mt-2">
                        Roster calculado com base nas memberships no início do dia (00:00).
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Presença dos alunos</strong>
            <span class="text-muted small">Registro por matrícula (StudentEnrollment).</span>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40%;">Aluno</th>
                        <th style="width: 15%;">CPF</th>
                        <th style="width: 25%;">Situação</th>
                        <th style="width: 20%;">Justificativa/Observação</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roster as $enrollment)
                        @php
                            $att = $attendanceByEnrollment->get($enrollment->id);
                            $cpf = $enrollment->student?->cpf ?? '';

                            $status = '—';
                            $just = '—';

                            if ($att) {
                                if ($att->present) {
                                    $status = 'Presente';
                                    $just = '—';
                                } else {
                                    if (!empty($att->justification)) {
                                        $status = 'Falta justificada';
                                        $just = $att->justification;
                                    } else {
                                        $status = 'Falta';
                                        $just = '—';
                                    }
                                }
                            }
                        @endphp

                        <tr>
                            <td>{{ $enrollment->student?->name ?? '—' }}</td>
                            <td>{{ $cpf ?: '—' }}</td>
                            <td>{{ $status }}</td>
                            <td>{{ $just }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-muted text-center py-4">
                                Nenhum aluno no roster para esta data.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection


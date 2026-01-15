@extends('layouts.app')

@section('title', 'Oficina — ' . ($workshop->name ?? ''))

@section('content')
    <div class="container-xxl">

        {{-- Mensagens de status/alertas --}}
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

        {{-- Cabeçalho --}}
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h1 class="h3 mb-1">
                    Oficina — {{ $workshop->name }}
                </h1>
                <div class="text-muted small">
                    Turma: <strong>{{ $classroom->name }}</strong> ·
                    Escola: <strong>{{ optional($classroom->school)->name ?? '—' }}</strong> ·
                    Ano letivo: <strong>{{ $classroom->academic_year }}</strong> ·
                    Turno: <strong>{{ $classroom->shift ?? '—' }}</strong>
                </div>
                <div class="text-muted small">
                    Atende:
                    @forelse ($classroom->gradeLevels as $gl)
                        <span class="badge bg-secondary">{{ $gl->short_name ?? $gl->name }}</span>
                    @empty
                        <span class="text-muted">—</span>
                    @endforelse
                </div>
            </div>
            <div class="d-flex gap-2">
                {{-- Aqui depois você pode ligar para uma rota de lançamento de aula/presença --}}
                <a href="#" class="btn btn-outline-secondary btn-sm">
                    Lançar aula / presença
                </a>

                <a href="{{ route('classrooms.show', $classroom) }}" class="btn btn-outline-primary btn-sm">
                    Voltar para Turma
                </a>
            </div>
        </div>

        {{-- Estatísticas rápidas --}}
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">Total de alunos (turma)</div>
                        <div class="display-6">{{ $stats['total_eligible'] ?? $enrollments->count() }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">Capacidade máxima da oficina</div>
                        <div class="display-6">
                            @php
                                $max = optional($workshop->pivot)->max_students;
                            @endphp
                            @if ($max)
                                {{ $max }}
                            @else
                                <span class="text-muted small">Sem capacidade máxima</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lista de alunos da oficina (turma inteira) --}}
        <div class="card">
            <div class="card-body p-0">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Aluno</th>
                            <th>CPF</th>
                            <th>Ano escolar</th>
                            {{-- Espaço para futuras colunas (presença, notas, etc.) --}}
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($enrollments as $en)
                            @php
                                $st = optional($en->student);
                            @endphp
                            <tr>
                                <td>{{ $st->display_name ?? ($st->name ?? '—') }}</td>
                                <td>{{ $st->cpf_formatted ?? ($st->cpf ?? '—') }}</td>
                                <td>
                                    {{ optional($en->gradeLevel)->short_name ?? (optional($en->gradeLevel)->name ?? '—') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    Nenhum aluno elegível para esta oficina.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection

@extends('layouts.app')

@section('title', 'Turma — ' . ($classroom->name ?? ''))

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
                <h1 class="h3 mb-1">{{ $classroom->name }}</h1>
                <div class="text-muted small">
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
            <div>
                <a href="{{ route('classrooms.edit', $classroom) }}" class="btn btn-outline-primary btn-sm">
                    Editar Turma
                </a>
            </div>
        </div>

        {{-- Estatísticas rápidas --}}
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">Total elegíveis</div>
                        <div class="display-6">{{ $stats['total_all'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Oficinas da turma (agora ocupando a largura toda) --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="text-muted small">
                        Oficinas da turma
                    </div>

                    {{-- Legenda de status --}}
                    <div class="small text-muted">
                        <span class="me-3">
                            <span class="me-1 text-success">●</span>
                            Tudo ok
                        </span>
                        <span class="me-3">
                            <span class="me-1 text-warning">●</span>
                            Alunos a alocar
                        </span>
                        <span>
                            <span class="me-1 text-danger">●</span>
                            Subturmas faltando
                        </span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Oficina</th>
                                <th style="width: 25%;">Capacidade máxima</th>
                                <th style="width: 20%;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($workshopSummaries->isEmpty())
                                <tr>
                                    <td colspan="3" class="text-muted text-center py-3">
                                        Nenhuma oficina vinculada à turma.
                                    </td>
                                </tr>
                            @else
                                @foreach ($workshopSummaries as $wk)
                                    <tr>
                                        <td>
                                            <span
                                                class="me-1
                                                @if ($wk->status === 'ok') text-success
                                                @elseif ($wk->status === 'warning') text-warning
                                                @else text-danger @endif">
                                                ●
                                            </span>
                                            {{ $wk->name }}

                                            @if ($wk->status === 'warning' && ($wk->not_allocated ?? 0) > 0)
                                                <span class="small text-muted ms-1">
                                                    ({{ $wk->not_allocated }} aluno(s) a alocar)
                                                </span>
                                            @endif

                                            @if ($wk->status === 'danger')
                                                <span class="small text-muted ms-1">
                                                    (subturmas faltando)
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($wk->has_limit)
                                                {{ $wk->limit }}
                                            @else
                                                <span class="text-muted">Sem capacidade máxima</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($wk->show_subclasses_button)
                                                {{-- Oficina em modo subturma (limit < total da turma) --}}
                                                <a href="{{ route('classrooms.workshops.subclasses.index', [$classroom, $wk->id]) }}"
                                                    class="btn btn-sm btn-outline-primary">
                                                    Ver subturmas
                                                </a>
                                            @else
                                                {{-- Oficina da turma inteira (sem limite ou limit >= total) --}}
                                                <a href="{{ route('classrooms.workshops.show', [$classroom, $wk->id]) }}"
                                                    class="btn btn-sm btn-outline-secondary">
                                                    Ver oficina
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Lista de ALUNOS (elegíveis do PAI) --}}
        <div class="card">
            <div class="card-body p-0">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Aluno</th>
                            <th>CPF</th>
                            <th>Ano escolar</th>
                            <th class="text-center">Alocado em alguma oficina?</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $allocatedAny = collect($stats['allocated_any_ids'] ?? []);
                        @endphp

                        @forelse ($enrollments as $en)
                            @php
                                $st = optional($en->student);
                                $isAllocated = $allocatedAny->contains($en->id);
                            @endphp
                            <tr>
                                <td>{{ $st->display_name ?? ($st->name ?? '—') }}</td>
                                <td>{{ $st->cpf_formatted ?? ($st->cpf ?? '—') }}</td>
                                <td>{{ optional($en->gradeLevel)->short_name ?? (optional($en->gradeLevel)->name ?? '—') }}
                                </td>
                                <td class="text-center">
                                    @if ($isAllocated)
                                        <span class="badge bg-success">Sim</span>
                                    @else
                                        <span class="badge bg-secondary">Não</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Nenhum aluno elegível.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection

@extends('layouts.app')

@section('content')
@php
    $classroom->loadMissing(['schoolWorkshop.workshop', 'gradeLevels']);
@endphp

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h1 class="h3 mb-1">Alunos do grupo</h1>
        <div class="text-muted">
            {{ $school->short_name ?? $school->name }} · <strong>{{ $classroom->name }}</strong>
        </div>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('schools.classrooms.show', [$school, $classroom]) }}" class="btn btn-outline-secondary">
            Voltar
        </a>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label mb-1">Data de referência</label>
                <input type="date" name="at" class="form-control"
                       value="{{ optional($at)->toDateString() }}">
            </div>
            <div class="col-auto d-flex gap-2">
                <button class="btn btn-outline-primary">Atualizar</button>
                <a href="{{ route('schools.classrooms.memberships.index', [$school, $classroom]) }}" class="btn btn-outline-secondary">
                    Hoje
                </a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">No grupo (ativos em {{ $at->format('d/m/Y') }})</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Aluno</th>
                            <th>Ano</th>
                            <th class="text-nowrap">Início</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activeMemberships as $m)
                            @php
                                $en = $m->enrollment;
                                $st = optional($en)->student;
                            @endphp
                            <tr>
                                <td>
                                    {{ $st->display_name ?? ($st->name ?? '—') }}
                                    <div class="text-muted small">
                                        {{ $st->cpf_formatted ?? ($st->cpf ?? '') }}
                                    </div>
                                </td>
                                <td>
                                    {{ optional($en?->gradeLevel)->short_name ?? (optional($en?->gradeLevel)->name ?? '—') }}
                                </td>
                                <td class="text-nowrap">
                                    {{ $m->starts_at ? $m->starts_at->format('d/m/Y') : '—' }}
                                </td>
                                <td class="text-end">
                                    <form method="POST"
                                          action="{{ route('schools.classrooms.memberships.end', [$school, $classroom, $m]) }}"
                                          onsubmit="return confirm('Encerrar a alocação deste aluno no grupo?');">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-outline-danger">Encerrar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    Nenhum aluno alocado no grupo nesta data.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Elegíveis (mesmas séries do grupo)</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Aluno</th>
                            <th>Ano</th>
                            <th>Situação</th>
                            <th class="text-end">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($eligibleEnrollments as $en)
                            @php
                                $st = optional($en)->student;
                                $current = $activeByEnrollmentId->get($en->id);
                                $inThis = $current && (int)$current->classroom_id === (int)$classroom->id;
                            @endphp
                            <tr>
                                <td>
                                    {{ $st->display_name ?? ($st->name ?? '—') }}
                                    <div class="text-muted small">
                                        {{ $st->cpf_formatted ?? ($st->cpf ?? '') }}
                                    </div>
                                </td>
                                <td>
                                    {{ optional($en->gradeLevel)->short_name ?? (optional($en->gradeLevel)->name ?? '—') }}
                                </td>
                                <td>
                                    @if($inThis)
                                        <span class="badge bg-success">Já está no grupo</span>
                                    @elseif($current)
                                        <span class="badge bg-warning text-dark">
                                            Em outro grupo: {{ $current->classroom?->name ?? ('#'.$current->classroom_id) }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">Sem grupo</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($inThis)
                                        <button class="btn btn-sm btn-outline-secondary" disabled>—</button>
                                    @else
                                        <form method="POST" action="{{ route('schools.classrooms.memberships.store', [$school, $classroom]) }}">
                                            @csrf
                                            <input type="hidden" name="student_enrollment_id" value="{{ $en->id }}">
                                            <button class="btn btn-sm btn-outline-primary">
                                                {{ $current ? 'Transferir' : 'Alocar' }}
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    Nenhum aluno elegível para as séries deste grupo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection


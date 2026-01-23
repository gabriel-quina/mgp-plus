{{-- resources/views/schools/classrooms/show.blade.php --}}

@extends('layouts.app')

@section('title', 'Grupo — ' . ($classroom->name ?? ''))

@section('content')
    @php
        $school = $school ?? $classroom->school;
        $classroom->loadMissing(['school', 'gradeLevels', 'schoolWorkshop.workshop']);

        $contract = $classroom->schoolWorkshop;
        $workshop = $contract?->workshop;

        // Alunos vigentes no grupo (hoje), via histórico de memberships
        $roster = $roster ?? $classroom->rosterAt();

        $totalRoster = is_countable($roster)
            ? count($roster)
            : (method_exists($roster, 'count')
                ? $roster->count()
                : 0);
    @endphp

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
                    Escola: <strong>{{ $school?->name ?? '—' }}</strong> ·
                    Ano letivo: <strong>{{ $classroom->academic_year ?? '—' }}</strong> ·
                    Turno: <strong>{{ $classroom->shift ?? '—' }}</strong>
                    @if (!empty($classroom->group_number) && (int) $classroom->group_number > 1)
                        · Grupo: <strong>#{{ $classroom->group_number }}</strong>
                    @endif
                </div>

                <div class="text-muted small mt-1">
                    Oficina:
                    <strong>{{ $workshop?->name ?? '—' }}</strong>
                    @if ($contract)
                        · Contrato:
                        <strong>#{{ $contract->id }}</strong>
                        @if ($contract->starts_at)
                            · Início: <strong>{{ $contract->starts_at->format('d/m/Y') }}</strong>
                        @endif
                        @if ($contract->ends_at)
                            · Fim: <strong>{{ $contract->ends_at->format('d/m/Y') }}</strong>
                        @endif
                        @if (!empty($contract->status))
                            · Status: <strong>{{ $contract->status }}</strong>
                        @endif
                    @endif
                </div>

                <div class="text-muted small mt-1">
                    Atende:
                    @forelse ($classroom->gradeLevels as $gl)
                        <span class="badge bg-secondary">{{ $gl->short_name ?? $gl->name }}</span>
                    @empty
                        <span class="text-muted">—</span>
                    @endforelse
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('schools.classrooms.index', $school) }}" class="btn btn-outline-secondary btn-sm">
                    Voltar
                </a>

                <a href="{{ route('schools.classrooms.memberships.index', [$school, $classroom]) }}"
                    class="btn btn-outline-primary btn-sm">
                    Alunos do grupo
                </a>

                <a href="{{ route('schools.classrooms.lessons.index', [$school, $classroom]) }}"
                    class="btn btn-outline-primary btn-sm">
                    Aulas
                </a>
                <a href="{{ route('schools.classrooms.assessments.index', [$school, $classroom]) }}"
                    class="btn btn-outline-primary btn-sm">
                    Avaliações
                </a>


                @if (\Illuminate\Support\Facades\Route::has('schools.classrooms.edit'))
                    <a href="{{ route('schools.classrooms.edit', [$school, $classroom]) }}"
                        class="btn btn-outline-primary btn-sm">
                        Editar grupo
                    </a>
                @endif
            </div>

        </div>

        {{-- Estatísticas rápidas --}}
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">Alunos no grupo (hoje)</div>
                        <div class="display-6">{{ $totalRoster }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">Capacidade sugerida</div>
                        <div class="display-6">{{ $classroom->capacity_hint ?? '—' }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">Status</div>
                        <div class="display-6" style="font-size: 1.5rem;">
                            {{ $classroom->status ?? '—' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">Bloqueado em</div>
                        <div class="display-6" style="font-size: 1.5rem;">
                            {{ $classroom->locked_at ? $classroom->locked_at->format('d/m/Y H:i') : '—' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Oficina do grupo (novo modelo: 1 contrato por grupo) --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="text-muted small mb-2">
                    Oficina do grupo (via contrato escola↔oficina)
                </div>

                @if (!$contract || !$workshop)
                    <div class="text-muted">Nenhuma oficina/contrato associado a este grupo.</div>
                @else
                    <dl class="row mb-0">
                        <dt class="col-sm-3">Oficina</dt>
                        <dd class="col-sm-9 mb-2">{{ $workshop->name }}</dd>

                        <dt class="col-sm-3">Contrato</dt>
                        <dd class="col-sm-9 mb-2">#{{ $contract->id }}</dd>

                        <dt class="col-sm-3">Vigência</dt>
                        <dd class="col-sm-9 mb-2">
                            @if ($contract->starts_at)
                                {{ $contract->starts_at->format('d/m/Y') }}
                            @else
                                —
                            @endif
                            @if ($contract->ends_at)
                                → {{ $contract->ends_at->format('d/m/Y') }}
                                <span class="text-muted small">(ends_at exclusivo)</span>
                            @endif
                        </dd>

                        <dt class="col-sm-3">Status do contrato</dt>
                        <dd class="col-sm-9 mb-0">{{ $contract->status ?? '—' }}</dd>
                    </dl>
                @endif
            </div>
        </div>

        {{-- Lista de alunos no grupo (hoje) --}}
        <div class="card">
            <div class="card-body p-0">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Aluno</th>
                            <th>CPF</th>
                            <th>Ano escolar</th>
                            <th class="text-end">Matrícula</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roster as $en)
                            @php
                                $st = optional($en->student);
                            @endphp
                            <tr>
                                <td>{{ $st->display_name ?? ($st->name ?? '—') }}</td>
                                <td>{{ $st->cpf_formatted ?? ($st->cpf ?? '—') }}</td>
                                <td>
                                    {{ optional($en->gradeLevel)->short_name ?? (optional($en->gradeLevel)->name ?? '—') }}
                                </td>
                                <td class="text-end text-muted">
                                    #{{ $en->id ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Nenhum aluno no grupo (hoje).</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection

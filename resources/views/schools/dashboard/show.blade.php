{{-- resources/views/schools/dashboard/show.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h3 mb-0">{{ $school->name }}</h1>
        @if (Route::has('dashboard.entry') || Route::has('scope.select'))
            <div class="d-flex gap-2">
                @if (Route::has('dashboard.entry'))
                    <a href="{{ route('dashboard.entry') }}" class="btn btn-outline-secondary">Início</a>
                @endif
                @if (Route::has('scope.select'))
                    <a href="{{ route('scope.select') }}" class="btn btn-outline-secondary">Trocar escola</a>
                @endif
            </div>
        @endif
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Linha inicial: localização + stats da escola --}}
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Localização</div>
                <div class="card-body">
                    <p class="mb-1"><strong>Cidade:</strong> {{ optional($school->city)->name ?? '—' }}</p>
                    <p class="mb-1"><strong>UF:</strong> {{ optional(optional($school->city)->state)->uf ?? '—' }}</p>
                    <p class="mb-1">
                        <strong>Endereço:</strong>
                        {{ $school->street ?? '—' }}
                        @if ($school->number)
                            , {{ $school->number }}
                        @endif
                        @if ($school->neighborhood)
                            — {{ $school->neighborhood }}
                        @endif
                        @if ($school->complement)
                            — {{ $school->complement }}
                        @endif
                    </p>
                    <p class="mb-0"><strong>CEP:</strong> {{ $school->cep_formatted ?? '—' }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Resumo da escola</div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-4">
                            <div class="text-muted small">Turmas ativas (ano letivo vigente)</div>
                            <div class="h3 mb-0">
                                {{ $school->classrooms_count }}
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-muted small">Oficinas vinculadas</div>
                            <div class="h3 mb-0">
                                {{ $school->workshops_count }}
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-muted small">Alunos matriculados (ano letivo vigente)</div>
                            <div class="h3 mb-0">
                                {{ $school->enrollments_count }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Anos escolares com alunos matriculados (apenas dessa escola) --}}
    <div class="card mb-3">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span><strong>Anos escolares</strong></span>
        </div>
        <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nome</th>
                                <th style="width: 300px;">Qtd de turmas</th>
                                <th style="width: 220px;">Alunos Matriculados</th>
                                <th style="width: 150px;" class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($gradeLevelsWithStudents) && $gradeLevelsWithStudents->isNotEmpty())
                            @foreach ($gradeLevelsWithStudents as $gl)
                                <tr>
                                    <td>{{ $gl->name }}</td>
                                    <td>{{ $gl->classrooms_count }}</td>
                                    <td>{{ $gl->enrollments_count }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('schools.grade-level-students.index', [$school, $gl]) }}"
                                            class="btn btn-sm btn-outline-secondary">Ver</a>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4" class="text-muted">Nenhum aluno matriculado.</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
        </div>
    </div>
    {{-- Turmas da escola (apenas dessa escola) --}}
    <div class="card mb-3">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span><strong>Turmas da escola</strong></span>
        </div>
        <div class="card-body p-0">
            @if ($school->classrooms->isEmpty())
                <div class="p-3 text-muted">Nenhuma turma cadastrada para esta escola.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nome</th>
                                <th style="width: 150px;">Ano letivo</th>
                                <th style="width: 150px;">Turno</th>
                                <th>Anos atendidos</th>
                                <th style="width: 150px;" class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($school->classrooms as $classroom)
                                <tr>
                                    <td>{{ $classroom->name }}</td>
                                    <td>{{ $classroom->academic_year }}</td>
                                    <td>
                                        @if ($classroom->shift === 'morning')
                                            Manhã
                                        @elseif($classroom->shift === 'afternoon')
                                            Tarde
                                        @elseif($classroom->shift === 'evening')
                                            Noite
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $classroom->gradeLevels->pluck('short_name')->filter()->join(', ') ?: $classroom->gradeLevels->pluck('name')->join(', ') }}
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('schools.classrooms.show', ['school' => $school, 'classroom' => $classroom]) }}"
                                            class="btn btn-sm btn-outline-secondary">Ver</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Oficinas da escola --}}
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <strong>Oficinas da escola</strong>
                <span class="badge text-bg-secondary ms-2">
                    {{ $school->workshops_count ?? $school->workshops->count() }}
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            @if ($school->workshops->isEmpty())
                <div class="p-3 text-muted">Nenhuma oficina vinculada.</div>
            @else
                <ul class="list-group list-group-flush">
                    @foreach ($school->workshops as $workshop)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $workshop->name }}</span>
                            {{-- espaço pra status se quiser no futuro --}}
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection

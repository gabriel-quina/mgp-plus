{{-- resources/views/schools/classrooms/show.blade.php --}}

@extends('layouts.app')

@section('title', 'Turma — ' . ($classroom->name ?? ''))

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
                    Turno: <strong>{{ $shiftLabels[$classroom->shift] ?? '—' }}</strong>
                    @if (!empty($classroom->group_number) && (int) $classroom->group_number > 1)
                        · Turma: <strong>#{{ $classroom->group_number }}</strong>
                    @endif
                </div>

                <div class="text-muted small mt-1">
                    Oficina:
                    <strong>{{ $workshop?->name ?? '—' }}</strong>
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
                    Alunos da turma
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
                        Editar turma
                    </a>
                @endif
            </div>

        </div>

        {{-- Estatísticas rápidas --}}
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">Alunos na turma</div>
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
        </div>

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
                                <td colspan="4" class="text-center text-muted py-4">Nenhum aluno na turma.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection

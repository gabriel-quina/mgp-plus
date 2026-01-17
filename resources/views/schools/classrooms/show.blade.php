@extends('layouts.app')

@section('title', 'Turma — ' . ($classroom->name ?? ''))

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
                <h1 class="h3 mb-1">{{ $classroom->name }}</h1>
                <div class="text-muted small">
                    Escola: <strong>{{ $school->name ?? optional($classroom->school)->name ?? '—' }}</strong> ·
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
                <a href="{{ route('schools.classrooms.index', $school) }}" class="btn btn-outline-secondary btn-sm">
                    Voltar
                </a>
            </div>
        </div>

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

        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="text-muted small">
                        Oficina vinculada à turma
                    </div>
                </div>

                @if ($hasMultipleWorkshops)
                    <div class="alert alert-warning">
                        Há mais de uma oficina vinculada a esta turma. Exibindo a primeira por padrão.
                    </div>
                @endif

                @if ($hasWorkshopMismatch)
                    <div class="alert alert-warning">
                        A oficina cadastrada diretamente na turma é diferente da oficina vinculada via pivot.
                        Exibindo a oficina principal detectada.
                    </div>
                @endif

                @if (! $workshop)
                    <div class="alert alert-info mb-0">
                        Nenhuma oficina vinculada à turma. Os botões de lançamento estão indisponíveis.
                    </div>
                @else
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div>
                            <div class="fw-semibold">{{ $workshop->name }}</div>
                            <div class="text-muted small">Oficina vinculada automaticamente</div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a class="btn btn-sm btn-outline-secondary"
                                href="{{ route('schools.lessons.index', ['school' => $school->id, 'classroom' => $classroom->id, 'workshop' => $workshop->id]) }}">
                                Aulas
                            </a>
                            <a class="btn btn-sm btn-outline-primary"
                                href="{{ route('schools.lessons.create', ['school' => $school->id, 'classroom' => $classroom->id, 'workshop' => $workshop->id]) }}">
                                Lançar aula
                            </a>
                            <a class="btn btn-sm btn-outline-secondary"
                                href="{{ route('schools.assessments.index', ['school' => $school->id, 'classroom' => $classroom->id, 'workshop' => $workshop->id]) }}">
                                Avaliações
                            </a>
                            <a class="btn btn-sm btn-outline-primary"
                                href="{{ route('schools.assessments.create', ['school' => $school->id, 'classroom' => $classroom->id, 'workshop' => $workshop->id]) }}">
                                Lançar avaliação
                            </a>
                        </div>
                    </div>
                @endif
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

@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.messages')

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h4 mb-1">{{ $classroom->name }}</h1>
                <div class="text-muted small">
                    Escola: <strong>{{ $school->name }}</strong> ·
                    Oficina: <strong>{{ $classroom->workshop?->name ?? '—' }}</strong> ·
                    Ano letivo: <strong>{{ $classroom->academic_year_id }}</strong> ·
                    Turno: <strong>{{ $classroom->shift ?? '—' }}</strong>
                </div>
                <div class="text-muted small">
                    Séries: <strong>{{ $classroom->grade_level_names }}</strong> ·
                    Grupo: <strong>{{ $classroom->group_number }}</strong> ·
                    Capacidade sugerida: <strong>{{ $classroom->capacity_hint ?? '—' }}</strong> ·
                    Status: <strong>{{ $classroom->status }}</strong>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('schools.classrooms.edit', [$school, $classroom]) }}" class="btn btn-outline-primary btn-sm">
                    Editar
                </a>
                @if (! $classroom->hasAcademicData())
                    <form action="{{ route('schools.classrooms.destroy', [$school, $classroom]) }}" method="POST"
                        onsubmit="return confirm('Excluir este grupo?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm">Excluir</button>
                    </form>
                @else
                    <span class="text-muted small align-self-center">Exclusão bloqueada (aulas/avaliações).</span>
                @endif
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Alunos alocados</span>
                <form method="GET" class="d-flex gap-2">
                    <input type="datetime-local" name="at" class="form-control form-control-sm"
                        value="{{ $rosterAt->format('Y-m-d\\TH:i') }}">
                    <button class="btn btn-sm btn-outline-secondary">Atualizar</button>
                </form>
            </div>
            <div class="card-body p-0">
                @if ($roster->isEmpty())
                    <p class="p-3 mb-0 text-muted">Nenhum aluno alocado para este horário.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Aluno</th>
                                    <th>Matrícula</th>
                                    <th>Série</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roster as $enrollment)
                                    <tr>
                                        <td>{{ $enrollment->student->name }}</td>
                                        <td>#{{ $enrollment->id }}</td>
                                        <td>{{ $enrollment->gradeLevel->short_name ?? ($enrollment->gradeLevel->name ?? '—') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('schools.lessons.index', ['school' => $school->id, 'classroom' => $classroom->id]) }}"
                class="btn btn-outline-secondary btn-sm">
                Aulas
            </a>
            <a href="{{ route('schools.assessments.index', ['school' => $school->id, 'classroom' => $classroom->id]) }}"
                class="btn btn-outline-secondary btn-sm">
                Avaliações
            </a>
            <a href="{{ route('schools.classrooms.index', $school) }}" class="btn btn-outline-primary btn-sm">
                Voltar
            </a>
        </div>
    </div>
@endsection

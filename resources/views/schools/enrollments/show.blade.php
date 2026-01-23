@extends('layouts.app')

@section('title', 'Detalhes da matrícula')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">Detalhes da matrícula</h1>
            <small class="text-muted">{{ $school->name }}</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('schools.enrollments.edit', [$school, $enrollment]) }}" class="btn btn-outline-primary">
                Alterar status
            </a>
            <a href="{{ route('schools.enrollments.index', $school) }}" class="btn btn-outline-secondary">
                Voltar
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Aluno</dt>
                <dd class="col-sm-9">{{ $enrollment->student?->name ?? '—' }}</dd>

                <dt class="col-sm-3">Ano escolar</dt>
                <dd class="col-sm-9">{{ $enrollment->gradeLevel?->name ?? '—' }}</dd>

                <dt class="col-sm-3">Ano letivo</dt>
                <dd class="col-sm-9">{{ $enrollment->academic_year }}</dd>

                <dt class="col-sm-3">Turno</dt>
                <dd class="col-sm-9">{{ $shiftLabels[$enrollment->shift] }}</dd>

                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">
                    <span
                        class="badge text-bg-secondary">{{ $statusLabels[$enrollment->status] ?? $enrollment->status }}</span>
                </dd>

                <dt class="col-sm-3">Início</dt>
                <dd class="col-sm-9">{{ $enrollment->started_at ?? '—' }}</dd>

                <dt class="col-sm-3">Término</dt>
                <dd class="col-sm-9">{{ $enrollment->ended_at ?? '—' }}</dd>

                <dt class="col-sm-3">Origem</dt>
                <dd class="col-sm-9">
                    {{ $enrollment->originSchool?->name ?? '—' }}
                </dd>
            </dl>
        </div>
    </div>
@endsection

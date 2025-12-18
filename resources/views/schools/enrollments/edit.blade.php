@extends('layouts.app')

@section('title', 'Atualizar matrícula')

@section('content')
    <div class="mb-3">
        <h1 class="h3 mb-0">Atualizar matrícula</h1>
        <small class="text-muted">{{ $school->name }}</small>
    </div>

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
            <div class="row g-2">
                <div class="col-md-6"><strong>Aluno:</strong> {{ $enrollment->student?->name ?? '—' }}</div>
                <div class="col-md-3"><strong>Ano letivo:</strong> {{ $enrollment->academic_year }}</div>
                <div class="col-md-3"><strong>Turno:</strong> {{ $enrollment->shift }}</div>
                <div class="col-md-6"><strong>Ano escolar:</strong> {{ $enrollment->gradeLevel?->name ?? '—' }}</div>
                <div class="col-md-6"><strong>Status atual:</strong>
                    {{ $statusLabels[$enrollment->status] ?? $enrollment->status }}</div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('schools.enrollments.update', [$school, $enrollment]) }}" class="card">
        @csrf
        @method('PUT')

        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        @foreach ($allowedStatuses as $s)
                            <option value="{{ $s }}" @selected($enrollment->status === $s)>
                                {{ $statusLabels[$s] ?? $s }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Data de início</label>
                    <input type="date" name="started_at" class="form-control"
                        value="{{ old('started_at', $enrollment->started_at) }}">
                    <div class="form-text">Para “cursando”, se vazio, o sistema define como hoje.</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Data de término</label>
                    <input type="date" name="ended_at" class="form-control"
                        value="{{ old('ended_at', $enrollment->ended_at) }}">
                    <div class="form-text">Para status finais, se vazio, o sistema define como hoje.</div>
                </div>
            </div>
        </div>

        <div class="card-footer d-flex gap-2">
            <button class="btn btn-primary" type="submit">Salvar</button>
            <a href="{{ route('schools.enrollments.index', $school) }}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
@endsection

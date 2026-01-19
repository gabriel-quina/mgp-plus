@extends('layouts.app')

@section('title', 'Turma — ' . ($classroom->name ?? ''))

@section('content')
    <div class="container-xxl">
        @include('partials.messages')

        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h1 class="h3 mb-1">{{ $classroom->name }}</h1>
                <div class="text-muted small">
                    Escola: <strong>{{ optional($classroom->school)->name ?? '—' }}</strong> ·
                    Oficina: <strong>{{ $classroom->workshop?->name ?? '—' }}</strong> ·
                    Ano letivo: <strong>{{ $classroom->academic_year_id }}</strong> ·
                    Turno: <strong>{{ $classroom->shift ?? '—' }}</strong>
                </div>
                <div class="text-muted small">
                    Série(s): <strong>{{ $classroom->grades_signature ?? '—' }}</strong> ·
                    Grupo: <strong>{{ $classroom->group_number }}</strong> ·
                    Capacidade sugerida: <strong>{{ $classroom->capacity_hint ?? '—' }}</strong> ·
                    Status: <strong>{{ $classroom->status }}</strong>
                </div>
            </div>
            <div>
                <a href="{{ route('classrooms.edit', $classroom) }}" class="btn btn-outline-primary btn-sm">
                    Editar Turma
                </a>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">Novo grupo</h1>
            <small class="text-muted">{{ $school->name }}</small>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('schools.classrooms.index', $school) }}" class="btn btn-outline-secondary">Voltar</a>
        </div>
    </div>

    <form method="POST" action="{{ route('schools.classrooms.store', $school) }}">
        @csrf

        @include('schools.classrooms._form', [
            'showSchoolSelect' => false,
            'fixedSchoolId' => $school->id,
            'schoolName' => $school->short_name ?? $school->name,
            'gradeLevels' => $gradeLevels,
            'workshops' => $workshops,
            'defaultYear' => $defaultYear,
        ])

        <div class="card">
            <div class="card-footer d-flex gap-2">
                <button class="btn btn-primary">Salvar</button>
                <a href="{{ route('schools.classrooms.index', $school) }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </div>
    </form>
@endsection

@stack('scripts')

@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">Editar grupo</h1>
            <small class="text-muted">{{ $school->name }}</small>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('schools.classrooms.show', [$school, $classroom]) }}" class="btn btn-outline-secondary">Ver</a>
            <a href="{{ route('schools.classrooms.index', $school) }}" class="btn btn-outline-secondary">Listar</a>
        </div>
    </div>

    <form method="POST" action="{{ route('schools.classrooms.update', [$school, $classroom]) }}">
        @csrf
        @method('PUT')

        @include('classrooms._form', [
            'classroom' => $classroom,
            'showSchoolSelect' => false,
            'fixedSchoolId' => $school->id,
            'schoolName' => $school->short_name ?? $school->name,
            'schools' => $schools,
            'gradeLevels' => $gradeLevels,
            'workshops' => $workshops,
            'selectedGrades' => $selectedGrades,
            'lockAcademicFields' => $lockAcademicFields,
        ])

        <div class="card">
            <div class="card-footer d-flex gap-2">
                <button class="btn btn-primary">Salvar</button>
                <a href="{{ route('schools.classrooms.show', [$school, $classroom]) }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </div>
    </form>
@endsection

@stack('scripts')

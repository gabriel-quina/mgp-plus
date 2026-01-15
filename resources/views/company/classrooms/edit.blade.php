@extends('layouts.app')

@section('content')
<header class="d-flex align-items-center justify-content-between mb-4">
  <h3 class="mb-0">Editar Turma — {{ $classroom->name }}</h3>
  <div class="d-flex gap-2">
    <a href="{{ route('classrooms.show', $classroom) }}" class="btn btn-outline-secondary">Ver</a>
    <a href="{{ route('classrooms.index') }}" class="btn btn-outline-secondary">Listar</a>
  </div>
</header>

<form method="POST" action="{{ route('classrooms.update', $classroom) }}">
  @csrf
  @method('PUT')

  @include('classrooms._form', [
    'classroom'         => $classroom,
    'schools'           => $schools,
    'parentClassrooms'  => $parentClassrooms,
    'gradeLevels'       => $gradeLevels,
    'workshops'         => $workshops,
    'selectedGrades'    => $selectedGrades,
    'existingWorkshops' => $existingWorkshops
  ])

  <div class="card">
    <div class="card-footer d-flex gap-2">
      <button class="btn btn-primary">Salvar</button>
      <a href="{{ route('classrooms.show', $classroom) }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
  </div>
</form>
@endsection

@stack('scripts')


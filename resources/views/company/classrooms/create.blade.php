@extends('layouts.app')

@section('content')
<header class="d-flex align-items-center justify-content-between mb-4">
  <h3 class="mb-0">Nova Turma</h3>
  <div class="d-flex gap-2">
    <a href="{{ route('classrooms.index') }}" class="btn btn-outline-secondary">Voltar</a>
  </div>
</header>

<form method="POST" action="{{ route('classrooms.store') }}">
  @csrf

  @include('classrooms._form', [
    'schools'           => $schools,
    'gradeLevels'       => $gradeLevels,
    'workshops'         => $workshops,
    'defaultYear'       => $defaultYear
  ])

  <div class="card">
    <div class="card-footer d-flex gap-2">
      <button class="btn btn-primary">Salvar</button>
      <a href="{{ route('classrooms.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
  </div>
</form>
@endsection

@stack('scripts')

@extends('layouts.app')

@section('content')
  @include('partials.messages')

  <header class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="mb-0">Editar alocação — {{ $teacher->display_name }}</h3>
    <div class="d-flex gap-2">
      <a href="{{ route('teachers.show', $teacher) }}" class="btn btn-outline-secondary">Voltar</a>
    </div>
  </header>

  <form method="POST" action="{{ route('teaching-assignments.update', [$teacher, $assignment]) }}">
    @csrf
    @method('PUT')
    @include('teaching_assignments._form', [
      'teacher'    => $teacher,
      'assignment' => $assignment,
      'schools'    => $schools,
      'engagements'=> $engagements,
      'submitLabel'=> 'Salvar alterações'
    ])
  </form>
@endsection


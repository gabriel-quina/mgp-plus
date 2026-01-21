@extends('layouts.app')

@section('content')
  @include('partials.messages')

  <header class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="mb-0">Novo vínculo — {{ $teacher->display_name }}</h3>
    <div class="d-flex gap-2">
      <a href="{{ route('admin.teachers.show', $teacher) }}" class="btn btn-outline-secondary">Voltar</a>
    </div>
  </header>

  <form method="POST" action="{{ route('admin.teacher-engagements.store', $teacher) }}">
    @csrf
    @include('company.teacher_engagements._form', ['teacher' => $teacher, 'engagement' => $engagement, 'cities' => $cities, 'submitLabel' => 'Salvar'])
  </form>
@endsection


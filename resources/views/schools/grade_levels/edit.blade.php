@extends('layouts.app')

@section('title', 'Editar ano escolar')

@section('content')
  <div class="d-flex justify-content-between align-items-start mb-3">
    <h1 class="mb-0">Editar: {{ $gradeLevel->name }}</h1>
    <a class="btn btn-link" href="{{ route('grade-levels.index') }}">← Voltar</a>
  </div>

  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('grade-levels.update', $gradeLevel) }}">
    @csrf
    @method('PUT')
    @include('grade_levels._form', ['submitLabel' => 'Salvar alterações'])
  </form>
@endsection


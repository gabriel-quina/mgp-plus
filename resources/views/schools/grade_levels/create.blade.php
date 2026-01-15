@extends('layouts.app')

@section('title', 'Novo ano escolar')

@section('content')
  <h1 class="mb-3">Novo ano escolar</h1>

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('grade-levels.store') }}">
    @csrf
    @include('grade_levels._form', ['submitLabel' => 'Criar'])
  </form>

  <div class="mt-3">
    <a class="btn btn-link" href="{{ route('grade-levels.index') }}">← Voltar</a>
  </div>
@endsection


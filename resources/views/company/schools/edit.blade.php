@extends('layouts.app')

@section('content')
<h1 class="h3 mb-3">Editar Escola</h1>

@if (session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if ($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

<form method="POST" action="{{ route('schools.update', $school) }}">
  @csrf
  @method('PUT')
  @include('schools._form', ['submitLabel' => 'Salvar alterações'])
</form>
@endsection


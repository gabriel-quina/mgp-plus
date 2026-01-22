@extends('layouts.app')

@section('content')
<h1 class="h3 mb-3">Editar Oficina</h1>
@if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if ($errors->any())
  <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<form method="POST" action="{{ route('admin.workshops.update', $workshop) }}">
  @csrf
  @method('PUT')
  @include('company.workshops._form', ['submitLabel' => 'Salvar alterações'])
</form>
@endsection


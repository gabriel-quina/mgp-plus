@extends('layouts.app')

@section('content')
<h1>Editar Aluno</h1>

@if (session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if ($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

<form method="POST" action="{{ route('students.update', $student) }}">
  @csrf
  @method('PUT')
  @include('students._form', [
    'submitLabel' => 'Salvar alterações',
    // $student e $schools vêm do controller
  ])
</form>
@endsection

@push('scripts')
  {{-- o _form empurra o <script> --}}
@endpush


@extends('layouts.app')

@section('content')
<h1>Novo Aluno</h1>

@if ($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

<form method="POST" action="{{ route('admin.students.store') }}">
  @csrf
  @include('company.students._form', [
    'submitLabel' => 'Criar',
    // $schools vem do controller
  ])
</form>
@endsection

@push('scripts')
  {{-- o _form empurra o <script> --}}
@endpush


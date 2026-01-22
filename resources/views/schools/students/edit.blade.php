@extends('layouts.app')

@section('title', 'Editar aluno')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">Editar aluno</h1>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('schools.students.update', ['school' => $school, 'student' => $student]) }}">
        @csrf
        @method('PUT')
        @include('schools.students._form', ['submitLabel' => 'Salvar aluno'])
    </form>
@endsection


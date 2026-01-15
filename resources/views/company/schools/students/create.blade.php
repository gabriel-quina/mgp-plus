@extends('layouts.app')

@section('title', 'Novo aluno')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">Novo aluno</h1>
            <small class="text-muted">{{ $school->name }}</small>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('schools.students.store', $school) }}">
        @csrf
        @include('schools.students._form', ['submitLabel' => 'Cadastrar aluno'])
    </form>
@endsection


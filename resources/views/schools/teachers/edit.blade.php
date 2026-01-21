@extends('layouts.app')

@section('content')
    @include('partials.messages')

    <header class="d-flex align-items-center justify-content-between mb-4">
        <h3 class="mb-0">Editar Professor</h3>

        <div class="d-flex gap-2">
            <a href="{{ route('teachers.show', $teacher) }}" class="btn btn-outline-secondary">
                Voltar
            </a>
        </div>
    </header>

    <form method="POST" action="{{ route('teachers.update', $teacher) }}">
        @csrf
        @method('PUT')

        @include('teachers._form', [
            'teacher' => $teacher,
            'submitLabel' => 'Salvar alterações',
        ])
    </form>
@endsection

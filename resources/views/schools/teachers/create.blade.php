@extends('layouts.app')

@section('content')
    @include('partials.messages')

    <header class="d-flex align-items-center justify-content-between mb-4">
        <h3 class="mb-0">Novo Professor</h3>

        <div class="d-flex gap-2">
            <a href="{{ route('teachers.index') }}" class="btn btn-outline-secondary">
                Voltar
            </a>
        </div>
    </header>

    <form method="POST" action="{{ route('teachers.store') }}">
        @csrf
        @include('teachers._form', [
            'submitLabel' => 'Salvar',
        ])
    </form>
@endsection

@extends('layouts.app')

@section('title', 'Minhas Escolas')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1">Minhas Escolas</h1>
            <div class="text-muted">
                {{ $user->name }} — {{ $user->cpf ?? $user->email }}
            </div>
        </div>

        <a class="btn btn-outline-secondary btn-sm" href="{{ route('profile.edit') }}">
            Perfil
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="h6 mb-3">Escolha onde você vai atuar</h2>

            @if ($schools->isEmpty())
                <div class="alert alert-warning mb-0">
                    Você ainda não possui acesso a nenhuma escola.
                </div>
            @else
                <div class="list-group">
                    @foreach ($schools as $school)
                        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                            href="{{ route('admin.schools.dashboard', $school) }}">
                            <span>
                                {{ $school->short_name ?? $school->name }}
                                @if ($school->city?->name)
                                    <span class="text-muted small">— {{ $school->city->name }}</span>
                                @endif
                            </span>
                            <span class="badge text-bg-dark">Entrar</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Dashboard Empresa')

@section('content')
    <h1 class="h3 mb-2">Dashboard Empresa</h1>
    <div class="text-muted mb-3">
        {{ $user->name }} — {{ $user->email ?? $user->cpf }}
    </div>

    <div class="alert alert-secondary">
        Você está na visão de equipe da empresa.
    </div>

    <div class="list-group">
        <a class="list-group-item list-group-item-action" href="{{ route('admin.schools.index') }}">
            Escolas
        </a>
        <a class="list-group-item list-group-item-action" href="{{ route('admin.workshops.index') }}">
            Oficinas
        </a>
        <a class="list-group-item list-group-item-action" href="{{ route('teachers.index') }}">
            Professores
        </a>
    </div>
@endsection

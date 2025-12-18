@extends('layouts.app')

@section('title', 'Dashboard Master')

@section('content')
    <h1 class="h3 mb-2">Dashboard Master</h1>

    <div class="text-muted mb-3">
        {{ $user->name }} — {{ $user->email ?? $user->cpf }}
    </div>

    <div class="alert alert-dark mb-0">
        Você está em modo <strong>Master</strong>. Selecione um escopo no topo para começar.
    </div>
@endsection


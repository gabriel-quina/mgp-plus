@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h4 mb-2">Dashboard</h1>

            <div class="text-muted">
                Você está logado como:
                <strong>{{ auth()->user()->name }}</strong>
            </div>

            <div class="mt-2">
                Identificador:
                <strong>{{ auth()->user()->email ?? auth()->user()->cpf }}</strong>
            </div>

            @if (auth()->user()->is_master)
                <div class="alert alert-dark mt-3 mb-0">
                    Modo Master ativo.
                </div>
            @endif
        </div>
    </div>
@endsection

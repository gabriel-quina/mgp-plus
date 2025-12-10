@extends('layouts.app')

@section('title', 'Dashboard Master')

@section('content')
    <h1 class="h3 mb-2">Dashboard Master</h1>
    <div class="text-muted mb-3">
        {{ $user->name }} — {{ $user->email ?? $user->cpf }}
    </div>

    <div class="alert alert-dark">
        Você está em modo <strong>Master</strong>. Acesso total.
    </div>

    <div class="list-group">
        <a class="list-group-item list-group-item-action" href="{{ route('schools.index') }}">
            Escolas (visão master)
        </a>
        <a class="list-group-item list-group-item-action" href="{{ route('teachers.index') }}">
            Professores
        </a>
        <a class="list-group-item list-group-item-action" href="{{ route('students.index') }}">
            Alunos
        </a>
        <a class="list-group-item list-group-item-action" href="{{ route('enrollments.index') }}">
            Matrículas
        </a>
        <a class="list-group-item list-group-item-action" href="{{ route('classrooms.index') }}">
            Turmas/Grupos
        </a>
    </div>
@endsection

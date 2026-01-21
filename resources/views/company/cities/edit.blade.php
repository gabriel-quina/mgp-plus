@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">✏️ Editar Cidade</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.cities.update', $city) }}" method="POST">
        @csrf
        @method('PUT')
        @include('company.cities._form')

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Atualizar</button>
            <a href="{{ route('admin.cities.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection


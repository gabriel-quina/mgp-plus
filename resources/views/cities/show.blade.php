@extends('layouts.app')

@section('content')
<header class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="mb-0">Detalhes da Cidade</h3>
    <div class="d-flex gap-2">
        <a href="{{ route('cities.edit', $city) }}" class="btn btn-primary">Editar</a>
        <form action="{{ route('cities.destroy', $city) }}" method="POST" class="d-inline"
              onsubmit="return confirm('Tem certeza que deseja excluir esta cidade?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">Excluir</button>
        </form>
        <a href="{{ route('cities.index') }}" class="btn btn-outline-secondary">Voltar</a>
    </div>
</header>

<div class="card">
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">ID</dt>
            <dd class="col-sm-9">{{ $city->id }}</dd>

            <dt class="col-sm-3">Nome</dt>
            <dd class="col-sm-9">{{ $city->name }}</dd>

            <dt class="col-sm-3">Estado</dt>
            <dd class="col-sm-9">{{ optional($city->state)->name ?? '-' }}</dd>

            <dt class="col-sm-3">UF</dt>
            <dd class="col-sm-9">{{ optional($city->state)->uf ?? '-' }}</dd>
        </dl>
    </div>
    <div class="card-footer d-flex gap-2">
        <a href="{{ route('cities.index') }}" class="btn btn-outline-secondary">Voltar</a>
        <a href="{{ route('cities.edit', $city) }}" class="btn btn-primary">Editar</a>
        <form action="{{ route('cities.destroy', $city) }}" method="POST" class="d-inline ms-auto"
              onsubmit="return confirm('Tem certeza que deseja excluir esta cidade?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">Excluir</button>
        </form>
    </div>
</div>
@endsection


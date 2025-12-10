@extends('layouts.app')

@section('content')
<header class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="mb-0">Cidades</h3>
    <div class="d-flex gap-2">
        <a href="{{ route('cities.create') }}" class="btn btn-primary">Nova Cidade</a>
        <a href="{{ url()->current() }}" class="btn btn-outline-secondary">Atualizar</a>
    </div>
</header>

@if (session('success'))
    <div class="alert alert-success mb-4">
        {{ session('success') }}
    </div>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Cidade</th>
                        <th>Estado</th>
                        <th>UF</th>
                        <th class="text-center" style="width:220px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cities as $city)
                        <tr>
                            <td>{{ $city->id }}</td>
                            <td>{{ $city->name }}</td>
                            <td>{{ $city->state->name ?? '-' }}</td>
                            <td>{{ $city->state->uf ?? '-' }}</td>
                            <td class="text-center">
                                <div class="d-flex flex-wrap justify-content-center gap-2">
                                    <a href="{{ route('cities.show', $city) }}" class="btn btn-sm btn-outline-info">Ver</a>
                                    <a href="{{ route('cities.edit', $city) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form action="{{ route('cities.destroy', $city) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Tem certeza que deseja excluir esta cidade?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Nenhuma cidade cadastrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if (method_exists($cities, 'links'))
        <div class="card-footer d-flex justify-content-end">
            {{ $cities->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection

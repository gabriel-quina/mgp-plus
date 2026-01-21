@extends('layouts.app')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h3 mb-0">Oficinas</h1>
        <a href="{{ route('admin.workshops.create') }}" class="btn btn-primary">Nova Oficina</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @php $q = request('q'); @endphp
    <form method="GET" action="{{ route('admin.workshops.index') }}" class="row g-2 mb-3">
        <div class="col-md-6">
            <input type="text" name="q" class="form-control" placeholder="Buscar por nome ou código…"
                value="{{ $q }}">
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-primary" type="submit">Buscar</button>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.workshops.index') }}" class="btn btn-outline-secondary">Limpar</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th style="width: 70px;">#</th>
                    <th>Oficina</th>
                    <th>Ativa</th>
                    <th style="width: 220px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($workshops as $wk)
                    <tr>
                        <td>{{ $wk->id }}</td>
                        <td>{{ $wk->name }}</td>
                        <td>{{ $wk->is_active ? 'Sim' : 'Não' }}</td>
                        <td class="d-flex flex-wrap gap-2">
                            <a href="{{ route('admin.workshops.edit', $wk) }}"
                                class="btn btn-sm btn-outline-secondary">Editar</a>

                            <form method="POST" action="{{ route('admin.workshops.destroy', $wk) }}"
                                onsubmit="return confirm('Excluir esta oficina?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">Nenhuma oficina encontrada.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if (method_exists($workshops, 'links'))
        <div class="mt-3">{{ $workshops->appends(['q' => $q])->links('pagination::bootstrap-5') }}</div>
    @endif
@endsection

@extends('layouts.app')

@section('content')
<header class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="mb-0">Anos escolares</h3>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.grade-levels.create') }}" class="btn btn-primary">Novo ano</a>
        <a href="{{ url()->current() }}" class="btn btn-outline-secondary">Atualizar</a>
    </div>
</header>

@if (session('success'))
    <div class="alert alert-success mb-4">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger mb-4">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nome</th>
                        <th>Abreviação</th>
                        <th>Sequência</th>
                        <th>Ativo</th>
                        <th class="text-center" style="width:220px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($levels as $l)
                        <tr>
                            <td>{{ $l->id }}</td>
                            <td>{{ $l->name }}</td>
                            <td>{{ $l->short_name ?? '-' }}</td>
                            <td>{{ $l->sequence ?? '-' }}</td>
                            <td>
                                @if($l->is_active)
                                    <span class="badge text-bg-success">Ativo</span>
                                @else
                                    <span class="badge text-bg-secondary">Inativo</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center flex-wrap gap-2">
                                    @if (Route::has('admin.grade-levels.show'))
                                        <a href="{{ route('admin.grade-levels.show', $l) }}" class="btn btn-sm btn-outline-info">Ver</a>
                                    @endif
                                    @if (Route::has('admin.grade-levels.edit'))
                                        <a href="{{ route('admin.grade-levels.edit', $l) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                    @endif
                                    @if (Route::has('admin.grade-levels.destroy'))
                                        <form action="{{ route('admin.grade-levels.destroy', $l) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Tem certeza que deseja excluir este ano escolar?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Nenhum ano escolar cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if (method_exists($levels, 'links'))
        <div class="card-footer d-flex justify-content-end">
            {{ $levels->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection


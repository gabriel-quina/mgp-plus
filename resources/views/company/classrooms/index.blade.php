@extends('layouts.app')

@section('content')
    <header class="d-flex align-items-center justify-content-between mb-4">
        <h3 class="mb-0">Turmas</h3>
        <div class="d-flex gap-2">
            <a href="{{ route('classrooms.create') }}" class="btn btn-primary">Nova Turma</a>
        </div>
    </header>

    {{-- mensagens --}}
    @include('partials.messages')

    <form method="GET" action="{{ route('classrooms.index') }}" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" name="q" value="{{ $q }}" class="form-control"
                placeholder="Buscar por nome/escola">
        </div>
        <div class="col-md-2">
            <input type="number" name="year" value="{{ $yr }}" class="form-control" placeholder="Ano letivo">
        </div>
        <div class="col-md-2">
            <select name="shift" class="form-select">
                <option value="">-- Turno --</option>
                <option value="morning" @selected($sh === 'morning')>Manhã</option>
                <option value="afternoon" @selected($sh === 'afternoon')>Tarde</option>
                <option value="evening" @selected($sh === 'evening')>Noite</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-secondary w-100">Filtrar</button>
        </div>
    </form>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th>Escola</th>
                            <th style="width: 110px;">Ano</th>
                            <th style="width: 110px;">Qtd. Alunos</th>
                            <th style="width: 110px;">Turno</th>
                            <th>Anos Atendidos</th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 220px;" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($classrooms as $c)
                            <tr>
                                <td>{{ $c->name }}</td>
                                <td>{{ $c->school->name }}</td>
                                <td>{{ $c->academic_year }}</td>
                                <td>{{ $c->total_all_students }}</td>
                                <td>
                                    @if ($c->shift === 'morning')
                                        Manhã
                                    @elseif($c->shift === 'afternoon')
                                        Tarde
                                    @else
                                        Noite
                                    @endif
                                </td>
                                <td>{{ $c->gradeLevels->pluck('name')->join(', ') }}</td>
                                <td>{{ $c->is_active ? 'Ativa' : 'Inativa' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('classrooms.show', $c) }}"
                                        class="btn btn-sm btn-outline-secondary">Ver</a>
                                    <a href="{{ route('classrooms.edit', $c) }}"
                                        class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form method="POST" action="{{ route('classrooms.destroy', $c) }}" class="d-inline"
                                        onsubmit="return confirm('Excluir turma?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Nenhuma turma cadastrada</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $classrooms->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection

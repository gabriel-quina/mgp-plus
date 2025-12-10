@extends('layouts.app')

@section('content')
  @include('partials.messages')

  <header class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="mb-0">Professores</h3>

    <div class="d-flex gap-2">
      <a href="{{ route('teachers.index', request()->query()) }}" class="btn btn-outline-secondary">
        Atualizar
      </a>
      <a href="{{ route('teachers.create') }}" class="btn btn-primary">
        Novo Professor
      </a>
    </div>
  </header>

  {{-- Filtros --}}
  <form method="GET" action="{{ route('teachers.index') }}" class="mb-3">
    <div class="row g-2">
      <div class="col-12 col-md-6">
        <input
          type="text"
          name="q"
          value="{{ old('q', $q) }}"
          class="form-control"
          placeholder="Buscar por nome, CPF ou e-mail">
      </div>

      <div class="col-12 col-md-3">
        @php
          $activeFilter = is_null($isActive) ? '' : ($isActive ? '1' : '0');
        @endphp
        <select name="is_active" class="form-select">
          <option value="" {{ $activeFilter === '' ? 'selected' : '' }}>Status — Todos</option>
          <option value="1" {{ $activeFilter === '1' ? 'selected' : '' }}>Ativos</option>
          <option value="0" {{ $activeFilter === '0' ? 'selected' : '' }}>Inativos</option>
        </select>
      </div>

      <div class="col-6 col-md-1">
        <button type="submit" class="btn btn-outline-secondary w-100">
          Filtrar
        </button>
      </div>

      <div class="col-6 col-md-2">
        <a href="{{ route('teachers.index') }}" class="btn btn-outline-secondary w-100">
          Limpar
        </a>
      </div>
    </div>
  </form>

  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped table-sm align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 70px;">#</th>
              <th>Professor</th>
              <th style="width: 160px;">CPF</th>
              <th style="width: 260px;">E-mail</th>
              <th style="width: 110px;" class="text-center">Ativo</th>
              <th style="width: 210px;" class="text-end">Ações</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($teachers as $teacher)
              <tr>
                <td>{{ $teacher->id }}</td>
                <td>{{ $teacher->display_name }}</td>
                <td>{{ $teacher->cpf_formatted ?? '—' }}</td>
                <td>
                  @if($teacher->email)
                    <a href="mailto:{{ $teacher->email }}">{{ $teacher->email }}</a>
                  @else
                    —
                  @endif
                </td>
                <td class="text-center">
                  @if($teacher->is_active)
                    <span class="badge bg-success">Ativo</span>
                  @else
                    <span class="badge bg-secondary">Inativo</span>
                  @endif
                </td>
                <td class="text-end">
                  <div class="d-inline-flex flex-wrap gap-2 justify-content-end">
                    <a href="{{ route('teachers.show', $teacher) }}" class="btn btn-sm btn-outline-info">
                      Ver
                    </a>
                    <a href="{{ route('teachers.edit', $teacher) }}" class="btn btn-sm btn-outline-primary">
                      Editar
                    </a>
                    <form action="{{ route('teachers.destroy', $teacher) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este professor?');" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger">
                        Excluir
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center py-4">Nenhum professor encontrado.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="card-footer d-flex align-items-center justify-content-between">
      <small class="text-muted">
        @if ($teachers->total() > 0)
          Mostrando {{ $teachers->firstItem() }}–{{ $teachers->lastItem() }} de {{ $teachers->total() }} registro(s)
        @else
          0 registro(s)
        @endif
      </small>
      {{ $teachers->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
  </div>
@endsection


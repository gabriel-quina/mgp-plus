@extends('layouts.app')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
  <h1 class="h3 mb-0">Dashboard</h1>
</div>

{{-- Cards de resumo --}}
<div class="row g-3 mb-4">
  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="text-muted">Escolas</div>
            <div class="display-6">{{ $schoolsCount }}</div>
          </div>
          <div class="d-flex gap-2">
            <a href="{{ route('schools.index') }}" class="btn btn-outline-primary btn-sm">Listar</a>
            <a href="{{ route('schools.create') }}" class="btn btn-primary btn-sm">Nova</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="text-muted">Oficinas</div>
            <div class="display-6">{{ $workshopsCount }}</div>
          </div>
          <div class="d-flex gap-2">
            <a href="{{ route('workshops.index') }}" class="btn btn-outline-primary btn-sm">Listar</a>
            <a href="{{ route('workshops.create') }}" class="btn btn-primary btn-sm">Nova</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Últimas escolas cadastradas --}}
<div class="card">
  <div class="card-header">Últimas escolas</div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table mb-0 align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Escola</th>
            <th>Cidade</th>
            <th>UF</th>
            <th class="text-end" style="width:160px;">Ações</th>
          </tr>
        </thead>
        <tbody>
          @forelse($latestSchools as $school)
            <tr>
              <td>{{ $school->id }}</td>
              <td>{{ $school->name }}</td>
              <td>{{ optional($school->city)->name ?? '—' }}</td>
              <td>{{ optional(optional($school->city)->state)->uf ?? '—' }}</td>
              <td class="text-end">
                <a href="{{ route('schools.show', $school) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                <a href="{{ route('schools.edit', $school) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted py-4">Nenhuma escola cadastrada ainda.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection


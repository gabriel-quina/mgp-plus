@extends('layouts.app')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
  <h1 class="h3 mb-0">Escolas</h1>
  <div class="d-flex gap-2">
    @if (Route::has('schools.create'))
      <a href="{{ route('schools.create') }}" class="btn btn-primary">Nova Escola</a>
    @endif
  </div>
</div>

@if (session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if ($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
  </div>
@endif

@php
  // fallback se o controller não enviar $q
  $q = isset($q) ? $q : request('q');
@endphp

<form method="GET" action="{{ route('schools.index') }}" class="row g-2 mb-3">
  <div class="col-md-6">
    <input type="text" name="q" class="form-control"
           placeholder="Buscar por escola, cidade ou UF…" value="{{ $q }}">
  </div>
  <div class="col-auto">
    <button class="btn btn-outline-primary" type="submit">Buscar</button>
  </div>
  <div class="col-auto">
    <a href="{{ route('schools.index') }}" class="btn btn-outline-secondary">Limpar</a>
  </div>
</form>

<div class="table-responsive">
  <table class="table table-hover align-middle">
    <thead>
      <tr>
        <th style="width: 70px;">#</th>
        <th>Escola</th>
        <th>Cidade</th>
        <th>UF</th>
        <th style="width: 260px;">Ações</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($schools as $school)
        <tr>
          <td>{{ $school->id }}</td>
          <td>{{ $school->name }}</td>
          <td>{{ optional($school->city)->name ?? '—' }}</td>
          <td>{{ optional(optional($school->city)->state)->uf ?? '—' }}</td>
          <td class="d-flex flex-wrap gap-2">
            @if (Route::has('schools.dashboard'))
              <a href="{{ route('schools.dashboard', $school) }}" class="btn btn-sm btn-outline-primary">Ver</a>
            @endif
            @if (Route::has('schools.edit'))
              <a href="{{ route('schools.edit', $school) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
            @endif
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="5" class="text-center text-muted py-4">Nenhuma escola encontrada.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

{{-- Paginação (só exibe se o objeto suportar links()) --}}
@if (is_object($schools) && method_exists($schools, 'links'))
  <div class="mt-3">
    {{ $schools->appends(['q' => $q])->links('pagination::bootstrap-5') }}
  </div>
@endif
@endsection

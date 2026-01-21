@extends('layouts.app')

@section('title', 'Ano escolar — '.$gradeLevel->name)

@section('content')
  <div class="d-flex justify-content-between align-items-start mb-3">
    <h1 class="mb-0">🏷️ {{ $gradeLevel->name }}</h1>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-primary" href="{{ route('admin.grade-levels.edit', $gradeLevel) }}">Editar</a>
      <a class="btn btn-link" href="{{ route('admin.grade-levels.index') }}">← Voltar</a>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="row g-3">
    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-header">Informações</div>
        <div class="card-body">
          <dl class="row mb-0">
            <dt class="col-5">Nome</dt>
            <dd class="col-7">{{ $gradeLevel->name }}</dd>

            <dt class="col-5">Abreviação</dt>
            <dd class="col-7">{{ $gradeLevel->short_name ?: '—' }}</dd>

            <dt class="col-5">Sequência</dt>
            <dd class="col-7">{{ $gradeLevel->sequence ?? '—' }}</dd>

            <dt class="col-5">Status</dt>
            <dd class="col-7">
              @if(isset($gradeLevel->is_active) ? $gradeLevel->is_active : true)
                <span class="badge bg-success">Ativo</span>
              @else
                <span class="badge bg-secondary">Inativo</span>
              @endif
            </dd>
          </dl>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-header">Ações rápidas</div>
        <div class="card-body">
          <form action="{{ route('admin.grade-levels.destroy', $gradeLevel) }}" method="POST" onsubmit="return confirm('Excluir este ano escolar?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">Excluir</button>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection


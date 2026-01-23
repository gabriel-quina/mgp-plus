@extends('layouts.app')

@section('content')
  @include('partials.messages')

  <header class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="mb-0">Professor: {{ $teacher->display_name }}</h3>
    <div class="d-flex flex-wrap gap-2">
      <a href="{{ route('schools.teachers.index', $school) }}" class="btn btn-outline-secondary">Voltar</a>
    </div>
  </header>

  {{-- DADOS PESSOAIS --}}
  <div class="card mb-4">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-4">
          <div class="small text-muted">Nome</div>
          <div class="fw-semibold">{{ $teacher->name }}</div>
        </div>
        <div class="col-md-2">
          <div class="small text-muted">CPF</div>
          <div class="fw-semibold">{{ $teacher->cpf_formatted ?? '—' }}</div>
        </div>
        <div class="col-md-2">
          <div class="small text-muted">Nascimento</div>
          <div class="fw-semibold">{{ optional($teacher->birthdate)->format('d/m/Y') ?? '—' }}</div>
        </div>
        <div class="col-md-4">
          <div class="small text-muted">E-mail</div>
          <div class="fw-semibold">{{ $teacher->email ?? '—' }}</div>
        </div>
        <div class="col-md-2">
          <div class="small text-muted">Ativo</div>
          <div class="fw-semibold">
            @if($teacher->is_active)
              <span class="badge bg-success">Sim</span>
            @else
              <span class="badge bg-secondary">Não</span>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>

@endsection



@extends('layouts.app')

@section('content')
  @include('partials.messages')

  <header class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="mb-0">Professor: {{ $teacher->display_name }}</h3>
    <div class="d-flex flex-wrap gap-2">
      <a href="{{ route('teachers.index') }}" class="btn btn-outline-secondary">Voltar</a>
      <a href="{{ route('teachers.edit', $teacher) }}" class="btn btn-outline-primary">Editar</a>
      <a href="{{ route('teacher-engagements.create', $teacher) }}" class="btn btn-primary">Novo vínculo</a>
      <a href="{{ route('teacher-city-access.create', $teacher) }}" class="btn btn-primary">Adicionar cidade</a>
      <a href="{{ route('teaching-assignments.create', $teacher) }}" class="btn btn-primary">Nova alocação</a>
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
        <div class="col-md-4">
          <div class="small text-muted">Nome social</div>
          <div class="fw-semibold">{{ $teacher->social_name ?? '—' }}</div>
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
        <div class="col-md-6">
          <div class="small text-muted">Resumo</div>
          <div class="fw-semibold">
            {{ $teacher->engagements_count ?? 0 }} vínculo(s) •
            {{ $teacher->city_accesses_count ?? $teacher->cityAccesses_count ?? 0 }} cidade(s) com acesso •
            {{ $teacher->assignments_count ?? 0 }} alocação(ões)
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- VÍNCULOS (TeacherEngagements) --}}
  <div class="card mb-4">
    <div class="p-3 d-flex align-items-center justify-content-between">
      <h5 class="mb-0">Vínculos</h5>
      <a href="{{ route('teacher-engagements.create', $teacher) }}" class="btn btn-sm btn-primary">Novo vínculo</a>
    </div>
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width: 18%">Tipo</th>
            <th style="width: 12%">Horas/sem</th>
            <th style="width: 14%">Status</th>
            <th style="width: 28%">Cidade (se municipal)</th>
            <th>Observações</th>
            <th class="text-end" style="width: 220px;">Ações</th>
          </tr>
        </thead>
        <tbody>
          @forelse($engagements as $e)
            <tr>
              <td>{{ $e->type_label ?? ucfirst($e->engagement_type) }}</td>
              <td>{{ $e->hours_per_week }}</td>
              <td>
                @php $statusMap = ['active'=>'Ativo','suspended'=>'Suspenso','ended'=>'Encerrado']; @endphp
                <span class="badge text-bg-{{ $e->status==='active' ? 'success' : ($e->status==='suspended' ? 'warning' : 'secondary') }}">
                  {{ $statusMap[$e->status] ?? $e->status }}
                </span>
              </td>
              <td>{{ $e->city?->name ?? '—' }}</td>
              <td class="text-nowrap">{{ $e->notes ?? '—' }}</td>
              <td class="text-end">
                <div class="d-flex flex-wrap justify-content-end gap-2">
                  <a href="{{ route('teacher-engagements.edit', [$teacher, $e]) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                  <form action="{{ route('teacher-engagements.destroy', [$teacher, $e]) }}" method="POST" onsubmit="return confirm('Remover este vínculo?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Excluir</button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Nenhum vínculo cadastrado.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">
      {{ $engagements->links('pagination::bootstrap-5') }}
    </div>
  </div>

  {{-- ACESSOS DE CIDADES (TeacherCityAccess) --}}
  <div class="card mb-4">
    <div class="p-3 d-flex align-items-center justify-content-between">
      <h5 class="mb-0">Acessos de cidades</h5>
      <a href="{{ route('teacher-city-access.create', $teacher) }}" class="btn btn-sm btn-primary">Adicionar cidade</a>
    </div>
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Cidade</th>
            <th style="width: 12%">UF</th>
            <th class="text-end" style="width: 220px;">Ações</th>
          </tr>
        </thead>
        <tbody>
          @forelse($cityAccesses as $acc)
            <tr>
              <td>{{ $acc->city?->name ?? '—' }}</td>
              <td>{{ $acc->city?->state?->uf ?? '—' }}</td>
              <td class="text-end">
                <form action="{{ route('teacher-city-access.destroy', [$teacher, $acc]) }}" method="POST" onsubmit="return confirm('Remover acesso a esta cidade?');">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger">Remover</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="3" class="text-center text-muted py-4">Nenhuma cidade com acesso.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">
      {{ $cityAccesses->links('pagination::bootstrap-5') }}
    </div>
  </div>

  {{-- ALOCAÇÕES (TeachingAssignments) --}}
  @php
    $shiftLabel = fn($s) => ['morning'=>'Manhã','afternoon'=>'Tarde','evening'=>'Noite'][strtolower($s ?? '')] ?? '—';
  @endphp
  <div class="card mb-4">
    <div class="p-3 d-flex align-items-center justify-content-between">
      <h5 class="mb-0">Alocações em escolas</h5>
      <a href="{{ route('teaching-assignments.create', $teacher) }}" class="btn btn-sm btn-primary">Nova alocação</a>
    </div>
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Escola</th>
            <th>Cidade</th>
            <th style="width: 10%">Ano</th>
            <th style="width: 12%">Turno</th>
            <th style="width: 12%">Horas/sem</th>
            <th>Vínculo</th>
            <th class="text-end" style="width: 220px;">Ações</th>
          </tr>
        </thead>
        <tbody>
          @forelse($assignments as $a)
            <tr>
              <td>{{ $a->school?->name ?? '—' }}</td>
              <td>{{ $a->school?->city?->name ?? '—' }}</td>
              <td>{{ $a->academic_year }}</td>
              <td>{{ $shiftLabel($a->shift) }}</td>
              <td>{{ $a->hours_per_week ?? '—' }}</td>
              <td>
                @if($a->engagement)
                  {{ $a->engagement->type_label ?? ucfirst($a->engagement->engagement_type) }}
                  @if($a->engagement->engagement_type === 'municipal' && $a->engagement->city)
                    — {{ $a->engagement->city->name }}
                  @endif
                @else
                  —
                @endif
              </td>
              <td class="text-end">
                <div class="d-flex flex-wrap justify-content-end gap-2">
                  <a href="{{ route('teaching-assignments.edit', [$teacher, $a]) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                  <form action="{{ route('teaching-assignments.destroy', [$teacher, $a]) }}" method="POST" onsubmit="return confirm('Remover esta alocação?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Excluir</button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Nenhuma alocação cadastrada.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">
      {{ $assignments->links('pagination::bootstrap-5') }}
    </div>
  </div>
@endsection



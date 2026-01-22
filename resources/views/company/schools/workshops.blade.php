{{-- resources/views/company/schools/workshops.blade.php --}}

@extends('layouts.app')

@section('content')
@php
    // Fallbacks defensivos (caso o controller ainda não esteja 100% ajustado)
    $activeToday = $activeToday ?? collect();
    $history     = $history ?? ($school->relationLoaded('schoolWorkshops') ? $school->schoolWorkshops : collect());
    $workshops   = $workshops ?? collect();
    $statuses    = $statuses ?? [
        \App\Models\SchoolWorkshop::STATUS_ACTIVE,
        \App\Models\SchoolWorkshop::STATUS_INACTIVE,
        \App\Models\SchoolWorkshop::STATUS_EXPIRED,
    ];

    // Botão Voltar robusto (não quebra se uma rota não existir)
    if (\Illuminate\Support\Facades\Route::has('admin.schools.show')) {
        $backUrl = route('admin.schools.show', $school);
    } elseif (\Illuminate\Support\Facades\Route::has('admin.schools.index')) {
        $backUrl = route('admin.schools.index');
    } else {
        $backUrl = url()->previous();
    }
@endphp

<header class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="mb-0">Oficinas da Escola</h3>
        <small class="text-muted d-block">
            {{ $school->name }}
            @if($school->city)
                — {{ $school->city->name }} / {{ $school->city->state->uf ?? '' }}
            @endif
        </small>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ $backUrl }}" class="btn btn-outline-secondary">Voltar</a>
    </div>
</header>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success mb-4">
        {{ session('success') }}
    </div>
@endif

<div class="card mb-4">
    <div class="card-body">
        <h5 class="mb-2">Novo vínculo (contrato)</h5>
        <p class="text-muted mb-3">
            Aqui você cria um vínculo <strong>com vigência</strong> e <strong>status</strong>.
            Observação: no sistema, <code>ends_at</code> é <strong>exclusivo</strong> (o contrato vale até o dia anterior).
        </p>

        <form method="POST" action="{{ route('admin.schools.workshops.store', $school) }}">
            @csrf

            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Oficina</label>
                    <select name="workshop_id" class="form-select" required>
                        <option value="">Selecione...</option>
                        @foreach($workshops as $w)
                            <option value="{{ $w->id }}" @selected(old('workshop_id') == $w->id)>
                                {{ $w->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Início</label>
                    <input type="date" name="starts_at" class="form-control" value="{{ old('starts_at') }}" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Fim</label>
                    <input type="date" name="ends_at" class="form-control" value="{{ old('ends_at') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        @foreach($statuses as $st)
                            <option value="{{ $st }}" @selected(old('status', \App\Models\SchoolWorkshop::STATUS_ACTIVE) === $st)>
                                {{ $st }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">Salvar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h5 class="mb-3">Ativas hoje</h5>

        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                <tr>
                    <th>Oficina</th>
                    <th>Início</th>
                    <th>Fim</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                @forelse($activeToday as $sw)
                    <tr>
                        <td>{{ $sw->workshop?->name ?? '—' }}</td>
                        <td>{{ $sw->starts_at ? $sw->starts_at->format('d/m/Y') : '—' }}</td>
                        <td>{{ $sw->ends_at ? $sw->ends_at->format('d/m/Y') : '—' }}</td>
                        <td><span class="badge text-bg-success">{{ $sw->status }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-muted">Nenhuma oficina ativa hoje.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="mb-3">Histórico de vínculos</h5>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                <tr>
                    <th>Oficina</th>
                    <th>Início</th>
                    <th>Fim</th>
                    <th>Status</th>
                    <th class="text-end" style="min-width: 380px;">Ações</th>
                </tr>
                </thead>
                <tbody>
                @forelse($history as $sw)
                    <tr>
                        <td>{{ $sw->workshop?->name ?? '—' }}</td>
                        <td>{{ $sw->starts_at ? $sw->starts_at->format('d/m/Y') : '—' }}</td>
                        <td>{{ $sw->ends_at ? $sw->ends_at->format('d/m/Y') : '—' }}</td>
                        <td>
                            @php
                                $badge = match ($sw->status) {
                                    \App\Models\SchoolWorkshop::STATUS_ACTIVE   => 'text-bg-success',
                                    \App\Models\SchoolWorkshop::STATUS_INACTIVE => 'text-bg-secondary',
                                    \App\Models\SchoolWorkshop::STATUS_EXPIRED  => 'text-bg-warning',
                                    default => 'text-bg-light',
                                };
                            @endphp
                            <span class="badge {{ $badge }}">{{ $sw->status }}</span>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2 flex-wrap">
                                <form method="POST" action="{{ route('admin.schools.workshops.update', [$school, $sw]) }}" class="d-flex gap-2 align-items-end flex-wrap">
                                    @csrf
                                    @method('PATCH')

                                    <div>
                                        <label class="form-label mb-1 small text-muted">Início</label>
                                        <input type="date" name="starts_at" class="form-control form-control-sm"
                                               value="{{ optional($sw->starts_at)->toDateString() }}">
                                    </div>

                                    <div>
                                        <label class="form-label mb-1 small text-muted">Fim</label>
                                        <input type="date" name="ends_at" class="form-control form-control-sm"
                                               value="{{ optional($sw->ends_at)->toDateString() }}">
                                    </div>

                                    <div>
                                        <label class="form-label mb-1 small text-muted">Status</label>
                                        <select name="status" class="form-select form-select-sm">
                                            @foreach($statuses as $st)
                                                <option value="{{ $st }}" @selected($sw->status === $st)>{{ $st }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        Atualizar
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.schools.workshops.destroy', [$school, $sw]) }}"
                                      onsubmit="return confirm('Remover este vínculo?');">
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
                        <td colspan="5" class="text-muted">Nenhum vínculo encontrado.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer d-flex gap-2">
        <a href="{{ $backUrl }}" class="btn btn-outline-secondary">Voltar</a>
    </div>
</div>
@endsection


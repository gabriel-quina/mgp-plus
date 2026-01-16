@extends('layouts.app')

@php
    $preview = $preview ?? null;
    $selectedGrades = collect(request('grade_level_ids', []))
        ->map(fn ($id) => (int) $id)
        ->all();
    $selectedWorkshop = request('workshop_id', $input['workshop_id'] ?? null);
    $selectedYear = request('academic_year', $input['academic_year'] ?? $defaultYear);
    $selectedShift = request('shift', $input['shift'] ?? '');
    $selectedMax = request('max_students', $input['max_students'] ?? '');
@endphp

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h1 class="h3 mb-1">Nova turma (helper)</h1>
            <div class="text-muted">{{ $school->short_name ?? $school->name }}</div>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('schools.classrooms.index', $school) }}">Voltar</a>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-header">Parâmetros</div>
                <div class="card-body">
                    <form method="GET" action="{{ route('schools.groups-wizard.create', $school) }}" class="mb-3">
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label">Oficina</label>
                                <select name="workshop_id" class="form-select">
                                    <option value="">-- Selecione --</option>
                                    @foreach ($workshops as $wk)
                                        <option value="{{ $wk->id }}" @selected($selectedWorkshop == $wk->id)>
                                            {{ $wk->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Anos escolares</label>
                                <select name="grade_level_ids[]" class="form-select" multiple>
                                    @foreach ($gradeLevels as $id => $name)
                                        <option value="{{ $id }}" @selected(in_array($id, $selectedGrades, true))>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Ano letivo</label>
                                <input type="number" name="academic_year" class="form-control"
                                    value="{{ $selectedYear }}" min="2000" max="2100">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Turno</label>
                                <select name="shift" class="form-select">
                                    <option value="">-- Selecione --</option>
                                    <option value="morning" @selected($selectedShift === 'morning')>Manhã</option>
                                    <option value="afternoon" @selected($selectedShift === 'afternoon')>Tarde</option>
                                    <option value="evening" @selected($selectedShift === 'evening')>Noite</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Capacidade por turma</label>
                                <input type="number" name="max_students" class="form-control" min="1"
                                    value="{{ $selectedMax }}" placeholder="Ex.: 20">
                            </div>

                            <div class="col-12 d-flex gap-2">
                                <button class="btn btn-outline-primary">Atualizar prévia</button>
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('schools.groups-wizard.create', $school) }}">Limpar</a>
                            </div>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('schools.groups-wizard.store', $school) }}">
                        @csrf
                        <input type="hidden" name="workshop_id" value="{{ $selectedWorkshop }}">
                        @foreach ($selectedGrades as $id)
                            <input type="hidden" name="grade_level_ids[]" value="{{ $id }}">
                        @endforeach
                        <input type="hidden" name="academic_year" value="{{ $selectedYear }}">
                        <input type="hidden" name="shift" value="{{ $selectedShift }}">
                        <input type="hidden" name="max_students" value="{{ $selectedMax }}">

                        <button class="btn btn-primary" @disabled($preview && $preview['conflict'])>
                            Criar/Adicionar turmas
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">Prévia</div>
                <div class="card-body">
                    @if (! $preview)
                        <div class="text-muted">Preencha os parâmetros e clique em “Atualizar prévia”.</div>
                    @elseif ($preview['error'])
                        <div class="alert alert-warning mb-0">{{ $preview['error'] }}</div>
                    @else
                        @if ($preview['conflict'])
                            <div class="alert alert-danger">
                                <div class="fw-semibold mb-1">Conflito detectado</div>
                                <div>{{ $preview['conflict'] }}</div>
                                @if ($preview['set_url'])
                                    <a class="btn btn-sm btn-outline-light mt-2"
                                        href="{{ $preview['set_url'] }}">Ver conjunto existente</a>
                                @elseif ($preview['set'])
                                    <div class="text-muted small mt-2">
                                        Conjunto existente: {{ $preview['set']->grade_levels_signature }}
                                    </div>
                                @endif
                            </div>
                        @endif

                        <dl class="row mb-0">
                            <dt class="col-7">Elegíveis</dt>
                            <dd class="col-5 text-end">{{ $preview['total_students'] ?? '—' }}</dd>

                            <dt class="col-7">Capacidade por turma</dt>
                            <dd class="col-5 text-end">{{ $preview['max_students'] ?? '—' }}</dd>

                            <dt class="col-7">Turmas necessárias</dt>
                            <dd class="col-5 text-end">{{ $preview['required_groups'] ?? '—' }}</dd>

                            <dt class="col-7">Turmas existentes</dt>
                            <dd class="col-5 text-end">{{ $preview['existing_groups'] ?? 0 }}</dd>

                            @if ($preview['allocated_students'] !== null)
                                <dt class="col-7">Alocados</dt>
                                <dd class="col-5 text-end">{{ $preview['allocated_students'] }}</dd>
                            @endif
                        </dl>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

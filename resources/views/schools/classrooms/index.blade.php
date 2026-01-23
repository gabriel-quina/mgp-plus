@extends('layouts.app')

@php
    // Compatível com controllers que passem ou não essas variáveis
    $q = $q ?? request('q', '');
    $yr = $yr ?? request('year');
    $sh = $sh ?? request('shift');
@endphp

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h1 class="h3 mb-1">Turmas da Escola</h1>
            <div class="text-muted">
                {{ $school->short_name ?? $school->name }}
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <a class="btn btn-primary" href="{{ route('schools.classrooms.create', $school) }}">
                Novo turma
            </a>
        </div>
    </div>

    <form method="GET" class="card card-body mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <label class="form-label mb-1">Buscar</label>
                <input type="text" name="q" class="form-control" placeholder="Ex.: nome da oficina"
                    value="{{ $q }}">
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label mb-1">Ano letivo</label>
                <input type="number" name="year" class="form-control" placeholder="Ex.: {{ date('Y') }}"
                    value="{{ $yr }}" min="2000" max="2100">
            </div>

            <div class="col-12 col-md-2">
                <label class="form-label mb-1">Turno</label>
                <input type="text" name="shift" class="form-control" placeholder="Manhã / Tarde / Noite"
                    value="{{ $sh }}">
            </div>

            <div class="col-12 col-md-2 d-flex gap-2">
                <button class="btn btn-primary w-100">
                    Filtrar
                </button>
                <a href="{{ route('schools.classrooms.index', $school) }}" class="btn btn-outline-secondary w-100"
                    title="Limpar filtros">
                    Limpar
                </a>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th class="text-nowrap">Ano letivo</th>
                        <th>Turno</th>
                        <th>Anos escolares</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($classrooms as $classroom)
                        @php
                            $isGroup = !is_null($classroom->school_workshop_id ?? null);
                            $gradeLevels = ($classroom->relationLoaded('gradeLevels') && $classroom->gradeLevels?->count())
                                ? $classroom->gradeLevels
                                : null;
                        @endphp
                        <tr>
                            <td class="fw-semibold">
                                {{ $classroom->name }}
                            </td>
                            <td class="text-nowrap">
                                {{ $classroom->academic_year ?? '—' }}
                            </td>
                            <td>
                                {{ $shiftLabels[$classroom->shift] ?? $classroom->shift }}
                            </td>
                            <td>
                                @if ($gradeLevels?->count())
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach ($gradeLevels as $gl)
                                            <span class="badge text-bg-light border">
                                                {{ $gl->short_name ?? $gl->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    —
                                @endif
                            </td>

                            <td class="text-end">
                                <a href="{{ route('schools.classrooms.show', [$school, $classroom]) }}"
                                    class="btn btn-sm btn-outline-primary">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Nenhuma turma encontrada para esta escola.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (method_exists($classrooms, 'links'))
            <div class="card-footer">
                {{ $classrooms->links() }}
            </div>
        @endif
    </div>
@endsection


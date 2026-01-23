@extends('layouts.app')

@section('title', 'Matrículas da Escola')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">Matrículas</h1>
            <small class="text-muted">{{ $school->name }}</small>
        </div>

    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('schools.enrollments.index', $school) }}"
        class="row gy-2 gx-2 align-items-end mb-3">
        <div class="col-md-4">
            <label class="form-label">Buscar</label>
            <input type="text" name="q" class="form-control" value="{{ $q }}"
                placeholder="Nome, CPF, e-mail">
        </div>

        <div class="col-md-2">
            <label class="form-label">Ano letivo</label>
            <input type="number" name="year" class="form-control" value="{{ $yr }}">
        </div>

        <div class="col-md-2">
            <label class="form-label">Turno</label>
            <select name="shift" class="form-select">
                <option value="">Todos</option>
                <option value="morning" @selected($sh === 'morning')>Manhã</option>
                <option value="afternoon" @selected($sh === 'afternoon')>Tarde</option>
                <option value="evening" @selected($sh === 'evening')>Noite</option>
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">Todos</option>
                @foreach ($allowedStatuses as $s)
                    <option value="{{ $s }}" @selected($st === $s)>{{ $statusLabels[$s] ?? $s }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <button class="btn btn-outline-primary w-100" type="submit">
                Buscar
            </button>
        </div>
    </form>

    @if ($enrollments->isEmpty())
        <div class="alert alert-info">Nenhuma matrícula encontrada.</div>
    @else
        <div class="table-responsive">
            <table class="table table-sm table-striped align-middle">
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Ano escolar</th>
                        <th>Ano letivo</th>
                        <th>Turno</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($enrollments as $enr)
                        <tr>
                            <td>{{ $enr->student?->name ?? '—' }}</td>
                            <td>{{ $enr->gradeLevel?->name ?? '—' }}</td>
                            <td>{{ $enr->academic_year }}</td>
                            <td>{{ $shiftLabels[$enr->shift] }}</td>
                            <td>
                                <span class="badge text-bg-secondary">
                                    {{ $statusLabels[$enr->status] ?? $enr->status }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <a href="{{ route('schools.enrollments.show', [$school, $enr]) }}"
                                        class="btn btn-sm btn-outline-secondary">
                                        Ver
                                    </a>

                                    <a href="{{ route('schools.enrollments.edit', [$school, $enr]) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        Status
                                    </a>

                                    {{-- Pré-matrícula -> matriculado --}}
                                    @if ($enr->status === \App\Models\StudentEnrollment::STATUS_PRE_ENROLLED)
                                        <form method="POST"
                                            action="{{ route('schools.enrollments.confirm', [$school, $enr]) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success" type="submit">Efetivar</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $enrollments->links() }}
        </div>
    @endif
@endsection

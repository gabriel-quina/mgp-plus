@extends('layouts.app')

@section('content')
    <header class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="mb-0">Matrículas da escola</h3>
            <small class="text-muted">{{ $school->name }}</small>
        </div>
        <div class="d-flex gap-2">
            {{-- futuro: criar matrícula no contexto da escola --}}
            {{--
            <a href="{{ route('schools.enrollments.create', $school) }}" class="btn btn-primary">
                Nova matrícula
            </a>
            --}}
        </div>
    </header>

    @include('partials.messages')

    <form method="GET" action="{{ route('schools.enrollments.index', $school) }}" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" name="q" value="{{ $q }}" class="form-control"
                placeholder="Buscar aluno (nome, CPF, e-mail)">
        </div>

        <div class="col-md-2">
            <input type="number" name="year" value="{{ $yr }}" class="form-control" placeholder="Ano letivo">
        </div>

        <div class="col-md-2">
            <select name="shift" class="form-select">
                <option value="">-- Turno --</option>
                <option value="morning" @selected($sh === 'morning')>Manhã</option>
                <option value="afternoon" @selected($sh === 'afternoon')>Tarde</option>
                <option value="evening" @selected($sh === 'evening')>Noite</option>
            </select>
        </div>

        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">-- Status --</option>
                <option value="active" @selected($st === 'active')>Cursando</option>
                <option value="completed" @selected($st === 'completed')>Concluída</option>
                <option value="failed" @selected($st === 'failed')>Reprovado</option>
                <option value="transferred" @selected($st === 'transferred')>Transferido</option>
                <option value="dropped" @selected($st === 'dropped')>Evasão/Cancelada</option>
                <option value="suspended" @selected($st === 'suspended')>Trancada</option>
            </select>
        </div>

        <div class="col-md-1">
            <button class="btn btn-outline-secondary w-100">Filtrar</button>
        </div>
    </form>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Aluno</th>
                            <th>Ano letivo</th>
                            <th>Turno</th>
                            <th>Ano escolar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($enrollments as $en)
                            <tr>
                                <td>{{ $en->student->display_name ?? ($en->student->name ?? '—') }}</td>
                                <td>{{ $en->academic_year }}</td>
                                <td>
                                    @if ($en->shift === 'morning')
                                        Manhã
                                    @elseif($en->shift === 'afternoon')
                                        Tarde
                                    @elseif($en->shift === 'evening')
                                        Noite
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $en->gradeLevel->name ?? '—' }}</td>
                                <td>
                                    @switch($en->status)
                                        @case('active')
                                            Cursando
                                        @break

                                        @case('completed')
                                            Concluída
                                        @break

                                        @case('failed')
                                            Reprovado
                                        @break

                                        @case('transferred')
                                            Transferido
                                        @break

                                        @case('dropped')
                                            Evasão/Cancelada
                                        @break

                                        @case('suspended')
                                            Trancada
                                        @break

                                        @default
                                            —
                                    @endswitch
                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Nenhuma matrícula encontrada</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer">
                {{ $enrollments->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endsection

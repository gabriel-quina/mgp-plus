@extends('layouts.app')

@section('title', 'Aulas lançadas')

@section('content')
    <div class="container">
        {{-- Cabeçalho --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h4 mb-1">
                    Aulas – {{ $classroom->name }}
                </h1>
                <div class="text-muted small">
                    {{ $classroom->school->name ?? '—' }} ·
                    Ano {{ $classroom->academic_year }} ·
                    {{ $classroom->shift ?? '—' }}<br>
                    Oficina: <strong>{{ $workshop->name }}</strong>
                </div>
            </div>

            <div class="d-flex gap-2">
<a href="{{ route('schools.lessons.create', [
    'school' => $school->id,
    'classroom' => $classroom->id,
    'workshop' => $workshop->id,
]) }}"
                    class="btn btn-outline-secondary btn-sm">
                    Lançar nova aula
                </a>

                <a href="{{ $backUrl }}" class="btn btn-outline-primary btn-sm">
                    Voltar para o grupo
                </a>
            </div>
        </div>

        {{-- Mensagem de status --}}
        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        {{-- Card de lista de aulas --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Aulas lançadas</span>
                <span class="text-muted small">
                    Total: {{ $lessons->total() }}
                </span>
            </div>

            <div class="card-body p-0">
                @if ($lessons->isEmpty())
                    <p class="p-3 mb-0 text-muted">
                        Nenhuma aula lançada ainda para este grupo de oficina.
                    </p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 15%">Data</th>
                                    <th style="width: 35%">Conteúdo / Tema</th>
                                    <th style="width: 25%" class="text-center">Presença</th>
                                    <th style="width: 25%" class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lessons as $lesson)
                                    @php
                                        $total = $lesson->attendances_count;
                                        $present = $lesson->present_count;
                                        $absent = $total - $present;
                                    @endphp
                                    <tr>
                                        <td>
                                            {{ optional($lesson->taught_at)->format('d/m/Y') ?? '—' }}
                                        </td>
                                        <td>
                                            {{ $lesson->topic ?: '—' }}<br>
                                            @if ($lesson->notes)
                                                <small class="text-muted">
                                                    {{ \Illuminate\Support\Str::limit($lesson->notes, 80) }}
                                                </small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($total > 0)
                                                <small>
                                                    <span class="text-success fw-semibold">
                                                        {{ $present }} presente(s)
                                                    </span>
                                                    @if ($absent > 0)
                                                        ·
                                                        <span class="text-danger fw-semibold">
                                                            {{ $absent }} falta(s)
                                                        </span>
                                                    @endif
                                                </small>
                                            @else
                                                <span class="text-muted small">Sem registros</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('schools.lessons.show', ['school' => $school->id, 'classroom' => $classroom->id, 'workshop' => $workshop->id, 'lesson' => $lesson->id]) }}"
                                                class="btn btn-outline-primary btn-sm">
                                                Ver presença
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            @if ($lessons->hasPages())
                <div class="card-footer">
                    {{ $lessons->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

{{-- resources/views/classrooms/workshops/group.blade.php --}}
@extends('layouts.app')

@section('title', $pageTitle ?? 'Oficina')

@section('content')
    <div class="container-xxl">

        @include('partials.messages')

        {{-- Cabeçalho --}}
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h1 class="h3 mb-1">
                    {{ $headerTitle ?? $pageTitle }}
                </h1>

                @if (!empty($contextLine))
                    <div class="text-muted small">
                        {!! $contextLine !!}
                    </div>
                @endif

                @if (!empty($workshopLine))
                    <div class="text-muted small">
                        {!! $workshopLine !!}
                    </div>
                @endif
            </div>

            <div class="d-flex gap-2">
                {{-- Avaliações --}}
                @if (!empty($assessmentsIndexUrl))
                    <a href="{{ $assessmentsIndexUrl }}" class="btn btn-outline-secondary btn-sm">
                        Ver avaliações
                    </a>
                @endif

                @if (!empty($launchAssessmentUrl))
                    <a href="{{ $launchAssessmentUrl }}" class="btn btn-outline-secondary btn-sm">
                        Lançar avaliação
                    </a>
                @endif

                {{-- Aulas --}}
                @if (!empty($lessonsIndexUrl))
                    <a href="{{ $lessonsIndexUrl }}" class="btn btn-outline-secondary btn-sm">
                        Ver aulas lançadas
                    </a>
                @endif

                @if (!empty($launchLessonUrl))
                    <a href="{{ $launchLessonUrl }}" class="btn btn-primary btn-sm">
                        Lançar aula / presença
                    </a>
                @endif

                @if (!empty($backUrl))
                    <a href="{{ $backUrl }}" class="btn btn-outline-primary btn-sm">
                        Voltar
                    </a>
                @endif
            </div>
        </div>

        {{-- Estatísticas rápidas --}}
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">
                            {{ $studentsLabel ?? 'Alunos neste grupo' }}
                        </div>
                        <div class="display-6">
                            {{ $studentsCount ?? (method_exists($enrollments, 'total') ? $enrollments->total() : $enrollments->count()) }}
                        </div>
                    </div>
                </div>
            </div>

            @if (!is_null($capacity ?? null))
                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="text-muted small">
                                {{ $capacityLabel ?? 'Capacidade' }}
                            </div>
                            <div class="display-6">
                                @if ($capacity > 0)
                                    {{ $capacity }}
                                @else
                                    <span class="text-muted small">Sem capacidade máxima</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Lista de alunos (quando fizer sentido listar) --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>{{ $tableTitle ?? 'Alunos' }}</strong>

                {{-- Protege contra $workshop = null --}}
                @if (!empty($workshop) && !empty($workshop->name))
                    <span class="text-muted small">Oficina: {{ $workshop->name }}</span>
                @endif
            </div>

            <div class="card-body p-0">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Aluno</th>
                            <th>CPF</th>
                            <th>Ano escolar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($enrollments as $en)
                            @php $st = optional($en->student); @endphp
                            <tr>
                                <td>{{ $st->display_name ?? ($st->name ?? '—') }}</td>
                                <td>{{ $st->cpf_formatted ?? ($st->cpf ?? '—') }}</td>
                                <td>
                                    {{ optional($en->gradeLevel)->short_name ?? (optional($en->gradeLevel)->name ?? '—') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    {{ $emptyMessage ?? 'Nenhum aluno encontrado.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Se vier paginator, mostra paginação; se for coleção, ignora --}}
            @if (method_exists($enrollments, 'withQueryString'))
                <div class="card-footer">
                    {{ $enrollments->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

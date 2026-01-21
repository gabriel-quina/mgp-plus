@extends('layouts.app')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h1 class="h3 mb-1">Relatórios</h1>
            <div class="text-muted">
                {{ $school->short_name ?? $school->name }}
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Bloco 1: Distribuição / Operação de oficinas (oficina-first) --}}
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <strong>Oficinas e Grupos</strong>
                    <div class="text-muted small">
                        Diagnóstico de distribuição e estrutura dos grupos
                    </div>
                </div>

                <div class="list-group list-group-flush">
                    <a class="list-group-item list-group-item-action"
                        href="{{ route('admin.schools.reports.groups.index', $school) }}">
                        Visão geral de Grupos
                    </a>

                    <a class="list-group-item list-group-item-action"
                        href="{{ route('admin.schools.reports.workshops.index', $school) }}">
                        Visão geral de Oficinas ativas
                    </a>

                    <a class="list-group-item list-group-item-action"
                        href="{{ route('admin.schools.reports.workshops.capacity', $school) }}">
                        Capacidade x Alocação por Oficina
                    </a>

                    <a class="list-group-item list-group-item-action"
                        href="{{ route('admin.schools.reports.students.unallocated', $school) }}">
                        Alunos não alocados em oficinas
                    </a>
                </div>
            </div>
        </div>

        {{-- Bloco 2: Alunos / Matrículas --}}
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <strong>Alunos e Matrículas</strong>
                    <div class="text-muted small">
                        Recortes administrativos e visão escolar
                    </div>
                </div>

                <div class="list-group list-group-flush">
                    <a class="list-group-item list-group-item-action" href="{{ route('admin.schools.students.index', $school) }}">
                        Lista de Alunos da Escola
                    </a>

                    <a class="list-group-item list-group-item-action"
                        href="{{ route('admin.schools.enrollments.index', $school) }}">
                        Matrículas da Escola
                    </a>

                    {{-- Alunos por Ano Escolar (dinâmico, sem id fixo) --}}
                    @if (isset($gradeLevels) && $gradeLevels->count())
                        <div class="list-group-item">
                            <div class="fw-semibold mb-2">Alunos por Ano Escolar</div>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($gradeLevels as $gl)
                                    <a class="btn btn-sm btn-outline-secondary"
                                        href="{{ route('admin.schools.grade-level-students.index', [$school, $gl]) }}">
                                        {{ $gl->short_name ?? $gl->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Subturmas — ' . ($workshop->name ?? ''))

@section('content')
    <div class="container-xxl">

        @php
            $total = $eligibleCount ?? 0; // total de alunos elegíveis
            $maxCapacity = $capacity ?? 0; // limite atual (max_students no pivot)

            // fallback mínimo
            if ($maxCapacity <= 0) {
                $maxCapacity = $total > 0 ? $total : 1;
            }

            // Quantas subturmas seriam necessárias com o limite atual
            $strictClasses = $maxCapacity > 0 ? max(1, (int) ceil($total / $maxCapacity)) : 1;
            $strictSize = $strictClasses > 0 ? (int) ceil($total / $strictClasses) : $total;

            // Já existem subturmas?
            $subclassroomsCount = $stats['subclassrooms_count'] ?? $children->count();
            $hasSubclasses = $subclassroomsCount > 0;

            // Mostrar botão "Distribuir turmas" só quando realmente precisa de subturmas
            $showDistributionButton = $total > $maxCapacity;

            // Opção “não criar subturmas, só ajustar limite”:
            // só faz sentido se ainda NÃO houver subturmas e total > limite
            $canOfferNoSubclassesOption = !$hasSubclasses && $total > $maxCapacity;
            $suggestedNewCapacity = $total; // ajustar limite pra caber todo mundo
        @endphp
        {{-- Mensagens de status/alertas --}}
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Erro:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Cabeçalho --}}
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h1 class="h3 mb-1">
                    Subturmas — {{ $workshop->name }}
                </h1>
                <div class="text-muted small">
                    Turma PAI: <strong>{{ $classroom->name }}</strong> ·
                    Escola: <strong>{{ optional($classroom->school)->name ?? '—' }}</strong> ·
                    Ano letivo: <strong>{{ $classroom->academic_year }}</strong> ·
                    Turno: <strong>{{ $classroom->shift ?? '—' }}</strong>
                </div>
                <div class="text-muted small">
                    Alunos elegíveis: <strong>{{ $total }}</strong> ·
                    Limite por subturma: <strong>{{ $maxCapacity }}</strong>
                </div>
            </div>
            <div class="d-flex gap-2">
                @if ($showDistributionButton)
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                        data-bs-target="#distributionModal">
                        Distribuir turmas
                    </button>
                @endif

                <a href="{{ route('classrooms.show', $classroom) }}" class="btn btn-outline-primary btn-sm">
                    Voltar para Turma
                </a>
            </div>
        </div>

        {{-- Estatísticas rápidas --}}
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">Número de subturmas</div>
                        <div class="display-6">{{ $stats['subclassrooms_count'] ?? $children->count() }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">Total de alunos alocados</div>
                        <div class="display-6">{{ $stats['total_allocated'] ?? $children->sum('students_count') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lista de subturmas --}}
        <div class="card">
            <div class="card-body p-0">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Subturma</th>
                            <th style="width: 20%;">Alunos alocados</th>
                            <th style="width: 20%;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($children as $child)
                            <tr>
                                <td>{{ $child->name }}</td>
                                <td>{{ $child->students_count ?? 0 }}</td>
                                <td class="text-end">
                                    <a href="{{ route('subclassrooms.show', [$classroom, $child]) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        Ver subturma
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    Nenhuma subturma cadastrada para esta oficina.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal de distribuição de turmas --}}
        @if ($showDistributionButton)
            <div class="modal fade" id="distributionModal" tabindex="-1" aria-labelledby="distributionModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="distributionModalLabel">
                                Distribuir alunos em subturmas
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>

                        <div class="modal-body">
                            <p class="small text-muted mb-3">
                                Você tem <strong>{{ $total }}</strong> aluno(s) elegíveis para esta oficina.<br>
                                Limite por subturma: <strong>{{ $maxCapacity }}</strong> aluno(s).
                            </p>

                            <div class="list-group">

                                {{-- Opção 1: manter limite atual e dividir em subturmas --}}
                                <form method="POST"
                                    action="{{ route('classrooms.workshops.apply', [$classroom, $workshop->id]) }}"
                                    class="list-group-item list-group-item-action mb-2">
                                    @csrf

                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div><strong>Opção 1</strong></div>
                                            <div class="small text-muted">
                                                Manter limite de <strong>{{ $maxCapacity }}</strong> aluno(s)
                                                e dividir em <strong>{{ $strictClasses }}</strong> subturma(s)
                                                com aproximadamente
                                                <strong>{{ $strictSize }}</strong> aluno(s) cada.
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                            Escolher
                                        </button>
                                    </div>
                                </form>

                                {{-- Opção 2: não criar subturmas, ajustar limite para usar a turma inteira --}}
                                @if ($canOfferNoSubclassesOption)
                                    <form method="POST"
                                        action="{{ route('classrooms.workshops.adjust_capacity', [$classroom, $workshop->id]) }}"
                                        class="list-group-item list-group-item-action">
                                        @csrf
                                        <input type="hidden" name="new_capacity" value="{{ $suggestedNewCapacity }}">

                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div><strong>Opção 2</strong></div>
                                                <div class="small text-muted">
                                                    Não criar subturmas.<br>
                                                    Atualizar o limite desta oficina de
                                                    <strong>{{ $maxCapacity }}</strong> para
                                                    <strong>{{ $suggestedNewCapacity }}</strong> aluno(s)
                                                    e considerar que toda a turma participa diretamente
                                                    na Turma PAI.
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                Ajustar limite
                                            </button>
                                        </div>
                                    </form>
                                @endif

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

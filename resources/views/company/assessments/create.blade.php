@extends('layouts.app')

@section('title', 'Lançar avaliação')

@section('content')
    @php
        // Escala atual (para manter estado em caso de erro de validação)
        $scale = old('scale_type', 'points');
    @endphp

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h4 mb-1">Lançar avaliação – {{ $classroom->name }}</h1>
                <div class="text-muted small">
                    {{ $classroom->school->name ?? '—' }} ·
                    Ano {{ $classroom->academic_year }} ·
                    {{ $classroom->shift ?? '—' }}<br>
                    Oficina: <strong>{{ $workshop->name }}</strong>
                </div>
            </div>

            <a href="{{ route('admin.schools.assessments.index', ['school' => $school->id, 'classroom' => $classroom->id, 'workshop' => $workshop->id]) }}"
                class="btn btn-outline-secondary btn-sm">
                Voltar
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.schools.assessments.store', ['school' => $school->id, 'classroom' => $classroom->id, 'workshop' => $workshop->id]) }}" method="POST">
            @csrf

            {{-- Card de dados da avaliação --}}
            <div class="card mb-3">
                <div class="card-header">Dados da avaliação</div>
                <div class="card-body row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Título</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Data</label>
                        <input type="date" name="due_at" class="form-control" value="{{ old('due_at') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Escala</label>
                        <select name="scale_type" class="form-select" id="scale_type">
                            <option value="points" {{ $scale === 'points' ? 'selected' : '' }}>
                                Pontos (0–100)
                            </option>
                            <option value="concept" {{ $scale === 'concept' ? 'selected' : '' }}>
                                Conceito (ruim–excelente)
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Máx. pontos</label>
                        <input type="number" name="max_points" class="form-control" min="0" max="100"
                            step="0.1" value="{{ old('max_points', 100) }}" id="max_points">
                        <small class="text-muted">
                            Defina o valor máximo da avaliação (0 a 100).
                        </small>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Descrição</label>
                        <textarea name="description" rows="2" class="form-control">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Card de notas --}}
            <div class="card">
                <div class="card-header">
                    Notas dos alunos ({{ $enrollments->count() }})
                </div>
                <div class="card-body p-0">
                    @if ($enrollments->isEmpty())
                        <p class="p-3 mb-0 text-muted">
                            Nenhum aluno neste grupo.
                        </p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>Aluno</th>
                                        <th>CPF</th>
                                        <th>Ano</th>

                                        {{-- Cabeçalho de pontos / conceito, alternando por escala --}}
                                        <th class="col-points" style="{{ $scale === 'points' ? '' : 'display:none;' }}">
                                            Pontos
                                        </th>
                                        <th class="col-concept" style="{{ $scale === 'concept' ? '' : 'display:none;' }}">
                                            Conceito
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($enrollments as $enrollment)
                                        @php $st = $enrollment->student; @endphp
                                        <tr>
                                            <td>{{ $st->name }}</td>
                                            <td>{{ $st->cpf_formatted ?? ($st->cpf ?? '—') }}</td>
                                            <td>
                                                {{ optional($enrollment->gradeLevel)->short_name ?? (optional($enrollment->gradeLevel)->name ?? '—') }}
                                            </td>

                                            {{-- Coluna de pontos --}}
                                            <td class="col-points"
                                                style="{{ $scale === 'points' ? '' : 'display:none;' }}">
                                                <input type="number" name="grades_points[{{ $enrollment->id }}]"
                                                    class="form-control form-control-sm" min="0" max="100"
                                                    step="0.1" value="{{ old('grades_points.' . $enrollment->id) }}">
                                            </td>

                                            {{-- Coluna de conceito --}}
                                            <td class="col-concept"
                                                style="{{ $scale === 'concept' ? '' : 'display:none;' }}">
                                                <select name="grades_concept[{{ $enrollment->id }}]"
                                                    class="form-select form-select-sm">
                                                    <option value="">—</option>
                                                    @foreach (\App\Models\AssessmentGrade::CONCEPTS as $concept)
                                                        <option value="{{ $concept }}" @selected(old('grades_concept.' . $enrollment->id) === $concept)>
                                                            {{ ucfirst(str_replace('_', ' ', $concept)) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">
                        Salvar avaliação
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Script simples pra alternar pontos x conceito e travar max_points quando for conceito --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const scaleSelect = document.getElementById('scale_type');
            const maxPointsInput = document.getElementById('max_points');

            function applyScale() {
                if (!scaleSelect) return;
                const type = scaleSelect.value;

                // mostra/esconde colunas
                document.querySelectorAll('.col-points').forEach(function(el) {
                    el.style.display = (type === 'points') ? '' : 'none';
                });
                document.querySelectorAll('.col-concept').forEach(function(el) {
                    el.style.display = (type === 'concept') ? '' : 'none';
                });

                // habilita/desabilita (readonly) max_points
                if (maxPointsInput) {
                    maxPointsInput.readOnly = (type !== 'points');
                }
            }

            applyScale();
            if (scaleSelect) {
                scaleSelect.addEventListener('change', applyScale);
            }
        });
    </script>
@endsection

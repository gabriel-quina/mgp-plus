@extends('layouts.app')

@section('title', 'Lançar avaliação')

@section('content')
    @php
        $classroom->loadMissing(['school', 'gradeLevels', 'schoolWorkshop.workshop']);
        $school = $school ?? $classroom->school;
        $workshop = $classroom->workshop;

        $scale = old('scale_type', 'points');
        $dueAtValue = old('due_at', ($dueAt ?? now())->toDateString());
    @endphp

    <div class="container-xxl">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h3 mb-1">Lançar avaliação – {{ $classroom->name }}</h1>
                <div class="text-muted small">
                    Escola: <strong>{{ $school->name ?? '—' }}</strong> ·
                    Ano letivo: <strong>{{ $classroom->academic_year ?? '—' }}</strong> ·
                    Turno: <strong>{{ $classroom->shift ?? '—' }}</strong><br>
                    Oficina: <strong>{{ $workshop?->name ?? '—' }}</strong>
                </div>
            </div>

            <a href="{{ route('schools.classrooms.assessments.index', [$school, $classroom]) }}"
               class="btn btn-outline-secondary btn-sm">
                Voltar
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Erro:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('schools.classrooms.assessments.store', [$school, $classroom]) }}" method="POST">
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
                        <input type="date" name="due_at" class="form-control" value="{{ $dueAtValue }}" required>
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
                        <label class="form-label">Valor da avaliação</label>
                        <input type="number" name="max_points" class="form-control"
                               min="0" max="100" step="1"
                               value="{{ old('max_points', 100) }}" id="max_points">
                        <small class="text-muted">
                            Valor máximo (usado apenas na escala por pontos).
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
                    Notas dos alunos ({{ is_countable($roster) ? count($roster) : (method_exists($roster,'count') ? $roster->count() : 0) }})
                </div>

                <div class="card-body p-0">
                    @if (empty($roster) || (is_countable($roster) && count($roster) === 0) || (method_exists($roster,'count') && $roster->count() === 0))
                        <p class="p-3 mb-0 text-muted">
                            Nenhum aluno neste grupo na data informada.
                        </p>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Aluno</th>
                                        <th style="width: 18%;">CPF</th>
                                        <th style="width: 14%;">Ano</th>
                                        <th class="col-points" style="{{ $scale === 'points' ? '' : 'display:none;' }}">
                                            Pontos
                                        </th>
                                        <th class="col-concept" style="{{ $scale === 'concept' ? '' : 'display:none;' }}">
                                            Conceito
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($roster as $enrollment)
                                        @php $st = $enrollment->student; @endphp
                                        <tr>
                                            <td>{{ $st->display_name ?? ($st->name ?? '—') }}</td>
                                            <td>{{ $st->cpf_formatted ?? ($st->cpf ?? '—') }}</td>
                                            <td>
                                                {{ optional($enrollment->gradeLevel)->short_name ?? (optional($enrollment->gradeLevel)->name ?? '—') }}
                                            </td>

                                            {{-- Pontos --}}
                                            <td class="col-points" style="{{ $scale === 'points' ? '' : 'display:none;' }}">
                                                <input type="number"
                                                       name="grades_points[{{ $enrollment->id }}]"
                                                       class="form-control form-control-sm"
                                                       min="0" max="100" step="0.1"
                                                       value="{{ old('grades_points.' . $enrollment->id) }}">
                                            </td>

                                            {{-- Conceito --}}
                                            <td class="col-concept" style="{{ $scale === 'concept' ? '' : 'display:none;' }}">
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

                <div class="card-footer d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Você pode deixar notas em branco; apenas notas preenchidas serão registradas.
                    </div>
                    <button type="submit" class="btn btn-primary">
                        Salvar avaliação
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Alterna pontos x conceito e trava max_points quando for conceito --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const scaleSelect = document.getElementById('scale_type');
            const maxPointsInput = document.getElementById('max_points');

            function applyScale() {
                const type = scaleSelect ? scaleSelect.value : 'points';

                document.querySelectorAll('.col-points').forEach(function(el) {
                    el.style.display = (type === 'points') ? '' : 'none';
                });
                document.querySelectorAll('.col-concept').forEach(function(el) {
                    el.style.display = (type === 'concept') ? '' : 'none';
                });

                if (maxPointsInput) {
                    maxPointsInput.readOnly = (type !== 'points');
                }
            }

            applyScale();
            if (scaleSelect) scaleSelect.addEventListener('change', applyScale);
        });
    </script>
@endsection


@extends('layouts.app')

@section('title', $pageTitle ?? 'Lançar aula / presença')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h1 class="h4 mb-1">{{ $headerTitle ?? $classroom->name }}</h1>
                <div class="text-muted small">
                    {{ $contextLine ?? '' }}<br>
                    Oficina: {{ $classroom->workshop?->name ?? '—' }}
                </div>
            </div>

            <div class="text-end">
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">
                    Voltar
                </a>
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('schools.lessons.store', ['school' => $school->id, 'classroom' => $classroom->id]) }}" method="POST">
            @csrf

            {{-- Dados da aula --}}
            <div class="card mb-4">
                <div class="card-header">
                    Dados da aula
                </div>
                <div class="card-body row g-3">
                    <div class="col-md-3">
                        <label for="lesson_at" class="form-label">Data e hora</label>
                        <input type="datetime-local" id="lesson_at" name="lesson_at" class="form-control"
                            value="{{ old('lesson_at', now()->format('Y-m-d\\TH:i')) }}">
                    </div>

                    <div class="col-md-5">
                        <label for="topic" class="form-label">Conteúdo / Tema</label>
                        <input type="text" id="topic" name="topic" class="form-control"
                            value="{{ old('topic') }}">
                    </div>

                    <div class="col-12">
                        <label for="notes" class="form-label">Observações</label>
                        <textarea id="notes" name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Grade de presença --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Presença dos alunos ({{ $enrollments->count() }})</span>

                    {{-- Mark-all simples; JS opcional depois --}}
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="check_all" checked>
                        <label class="form-check-label" for="check_all">
                            Marcar todos como presentes
                        </label>
                    </div>
                </div>

                <div class="card-body p-0">
                    @if ($enrollments->isEmpty())
                        <p class="p-3 mb-0 text-muted">
                            Nenhum aluno alocado para este grupo de oficina.
                        </p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 40%">Aluno</th>
                                        <th style="width: 20%">Matrícula / Ano</th>
                                        <th style="width: 15%" class="text-center">Presente?</th>
                                        <th style="width: 25%">Observação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($enrollments as $enrollment)
                                        <tr>
                                            <td>
                                                {{ $enrollment->student->name }}
                                                @if (method_exists($enrollment->student, 'cpf_formatted'))
                                                    <br>
                                                    <small class="text-muted">
                                                        CPF: {{ $enrollment->student->cpf_formatted }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    Matrícula #{{ $enrollment->id }}<br>
                                                    Ano:
                                                    {{ $enrollment->gradeLevel->short_name ?? ($enrollment->gradeLevel->name ?? $classroom->grade_level_names ?? '—') }}
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" name="attendance_present[{{ $enrollment->id }}]"
                                                    value="1" class="form-check-input attendance-checkbox" checked>
                                            </td>
                                            <td>
                                                <input type="text" name="attendance_note[{{ $enrollment->id }}]"
                                                    class="form-control form-control-sm" placeholder="Observação (opcional)"
                                                    value="{{ old('attendance_note.' . $enrollment->id) }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <div class="card-footer d-flex justify-content-between">
                    <span class="text-muted small">
                        Desmarque quem **faltou**. Observações são opcionais.
                    </span>

                    <button type="submit" class="btn btn-primary">
                        Salvar aula e presença
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Script simples pra "Marcar todos" --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const checkAll = document.getElementById('check_all');
                const boxes = document.querySelectorAll('.attendance-checkbox');

                if (checkAll) {
                    checkAll.addEventListener('change', function() {
                        boxes.forEach(function(box) {
                            box.checked = checkAll.checked;
                        });
                    });
                }
            });
        </script>
    @endpush
@endsection

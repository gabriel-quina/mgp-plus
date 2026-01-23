@extends('layouts.app')

@section('title', 'Lançamento de aula')

@section('content')
    <div class="container-xxl">

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

        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h1 class="h3 mb-1">Lançamento de aula</h1>
                <div class="text-muted small">
                    Escola: <strong>{{ $school->name }}</strong> ·
                    Turma: <strong>{{ $classroom->name }}</strong>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('schools.classrooms.lessons.index', [$school, $classroom]) }}"
                    class="btn btn-outline-secondary btn-sm">
                    Voltar para Aulas
                </a>
                <a href="{{ route('schools.classrooms.show', [$school, $classroom]) }}"
                    class="btn btn-outline-secondary btn-sm">
                    Voltar para Turma
                </a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Detalhes da aula</strong>
                <span class="text-muted small">Preencha a data, conteúdo e presença antes de salvar.</span>
            </div>

            <form action="{{ route('schools.classrooms.lessons.store', [$school, $classroom]) }}" method="POST">
                @csrf

                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Data da aula</label>
                            <input type="date" name="taught_at" class="form-control"
                                value="{{ old('taught_at', $taughtAt->toDateString()) }}" required>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label">Professor(a)</label>

                            @if (!empty($teacherLocked) && $teacherLocked)
                                <input type="text" class="form-control" value="{{ $teacher?->name ?? '—' }}" readonly>
                            @else
                                <select name="teacher_id" class="form-select" required>
                                    <option value="">— Selecione —</option>
                                    @foreach ($teachers ?? collect() as $t)
                                        <option value="{{ $t->id }}" @selected(old('teacher_id') == $t->id)>
                                            {{ $t->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">
                                    Você está como <strong>master</strong> sem vínculo com Teacher. Selecione um professor
                                    para registrar a aula.
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label">Conteúdo / tópico</label>
                            <textarea name="topic" rows="3" class="form-control"
                                placeholder="Ex.: Present Simple, revisão, atividade em duplas.">{{ old('topic') }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Observações</label>
                            <textarea name="notes" rows="3" class="form-control"
                                placeholder="Ex.: Boa participação, 2 alunos com dificuldade.">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>Presença dos alunos</strong>
                        </div>
                        @if ($roster->isEmpty())
                            <div class="alert alert-warning mb-0">
                                Nenhum aluno encontrado na turma para esta data. Você pode salvar a aula sem presenças.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 40%;">Aluno</th>
                                            <th style="width: 15%;">CPF</th>
                                            <th style="width: 25%;">Situação</th>
                                            <th style="width: 20%;">Justificativa/Observação</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($roster as $enrollment)
                                            @php
                                                $cpf = $enrollment->student?->cpf ?? '';
                                                $statusOld = old("attendances.{$enrollment->id}.status", 'present');
                                                $justOld = old("attendances.{$enrollment->id}.justification");
                                            @endphp

                                            <tr>
                                                <td>{{ $enrollment->student?->name ?? '—' }}</td>
                                                <td>{{ $cpf ?: '—' }}</td>
                                                <td>
                                                    <select name="attendances[{{ $enrollment->id }}][status]"
                                                        class="form-select form-select-sm" required>
                                                        <option value="present" @selected($statusOld === 'present')>Presente
                                                        </option>
                                                        <option value="absent" @selected($statusOld === 'absent')>Falta</option>
                                                        <option value="justified" @selected($statusOld === 'justified')>Falta
                                                            justificada</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text"
                                                        name="attendances[{{ $enrollment->id }}][justification]"
                                                        class="form-control form-control-sm" value="{{ $justOld }}"
                                                        placeholder="Opcional">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('schools.classrooms.lessons.index', [$school, $classroom]) }}"
                            class="btn btn-outline-secondary btn-sm">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary btn-sm">
                            Salvar lançamento
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>
@endsection

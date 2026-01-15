@extends('layouts.app')

@section('title', 'Lançamento de aula')

@section('content')
    <div class="container-xxl">

        {{-- Mensagens de status/alertas (mesma lógica da tela de Turma) --}}
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
                <h1 class="h3 mb-1">Lançamento de aula</h1>
                <div class="text-muted small">
                    Escola: <strong>EM Doutor João Penido</strong> ·
                    Ano letivo: <strong>2025</strong> ·
                    Turno: <strong>Manhã</strong>
                </div>
                <div class="text-muted small">
                    Turma/Subturma:
                    <strong>Turma 6º A — Oficina Inglês 6º A - Grupo 1</strong>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="#" class="btn btn-outline-secondary btn-sm">
                    Voltar para Subturma
                </a>
                <a href="#" class="btn btn-outline-secondary btn-sm">
                    Voltar para Turma PAI
                </a>
            </div>
        </div>

        {{-- Resumo rápido / contexto --}}
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1">Informações da turma</div>
                        <div class="fw-semibold">Inglês 6º A - Grupo 1</div>
                        <div class="small text-muted">
                            Oficina: <strong>Inglês</strong><br>
                            Professor(a): <strong>Fulano(a) de Tal</strong><br>
                            Dia da semana: <strong>Quarta-feira</strong><br>
                            Horário padrão: <strong>09:00–10:00</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Estatísticas da subturma (estático por enquanto) --}}
            <div class="col-md-8">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-2">Resumo da subturma</div>
                        <div class="d-flex flex-wrap gap-3">
                            <div>
                                <div class="text-muted small">Alunos na subturma</div>
                                <div class="display-6 mb-0">18</div>
                            </div>
                            <div>
                                <div class="text-muted small">Presenças lançadas</div>
                                <div class="h4 mb-0">0 / 18</div>
                            </div>
                            <div>
                                <div class="text-muted small">Última aula lançada</div>
                                <div class="h6 mb-0">08/11/2025</div>
                                <div class="small text-muted">Conteúdo: Present Simple</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Formulário de lançamento de aula --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Detalhes da aula</strong>
                <span class="text-muted small">
                    Preencha a data, situação e conteúdo da aula antes de salvar.
                </span>
            </div>

            <form action="#" method="POST">
                @csrf

                <div class="card-body">
                    {{-- Data e horário da aula --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Data da aula</label>
                            <input type="date" name="lesson_date" class="form-control" value="2025-11-13">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Início</label>
                            <input type="time" name="start_time" class="form-control" value="09:00">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Término</label>
                            <input type="time" name="end_time" class="form-control" value="10:00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Situação da aula</label>
                            <select name="status" class="form-select">
                                <option value="realizada" selected>Realizada</option>
                                <option value="nao_realizada">Não realizada</option>
                                <option value="reposicao">Reposição</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Número da aula</label>
                            <input type="number" name="lesson_number" class="form-control" value="5" min="1">
                        </div>
                    </div>

                    {{-- Conteúdo e observações --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label">Conteúdo trabalhado</label>
                            <textarea name="content" rows="3" class="form-control"
                                placeholder="Ex.: Revisão de verb to be, vocabulário de rotina diária, atividade em duplas."></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Observações gerais</label>
                            <textarea name="notes" rows="3" class="form-control"
                                placeholder="Ex.: Aula com boa participação, 2 alunos com dificuldade na atividade escrita."></textarea>
                        </div>
                    </div>

                    {{-- Lista de alunos e presença --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>Presença dos alunos</strong>
                            <div class="small text-muted">
                                Marque a situação de cada aluno nesta aula.
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40%;">Aluno</th>
                                        <th style="width: 15%;">CPF</th>
                                        <th style="width: 25%;">Situação</th>
                                        <th style="width: 20%;">Observação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Linhas estáticas de exemplo --}}
                                    <tr>
                                        <td>Maria da Silva</td>
                                        <td>123.456.789-01</td>
                                        <td>
                                            <select name="attendance[1][status]" class="form-select form-select-sm">
                                                <option value="presente" selected>Presente</option>
                                                <option value="falta">Falta</option>
                                                <option value="falta_justificada">Falta justificada</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="attendance[1][note]"
                                                class="form-control form-control-sm" placeholder="Opcional">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>João Pereira</td>
                                        <td>987.654.321-00</td>
                                        <td>
                                            <select name="attendance[2][status]" class="form-select form-select-sm">
                                                <option value="presente">Presente</option>
                                                <option value="falta" selected>Falta</option>
                                                <option value="falta_justificada">Falta justificada</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="attendance[2][note]"
                                                class="form-control form-control-sm"
                                                placeholder="Ex.: Ausente sem justificativa.">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Ana Souza</td>
                                        <td>111.222.333-44</td>
                                        <td>
                                            <select name="attendance[3][status]" class="form-select form-select-sm">
                                                <option value="presente">Presente</option>
                                                <option value="falta">Falta</option>
                                                <option value="falta_justificada" selected>Falta justificada</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="attendance[3][note]"
                                                class="form-control form-control-sm" placeholder="Ex.: Atestado médico.">
                                        </td>
                                    </tr>

                                    {{-- ...demais alunos entram aqui depois, via backend --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Botões de ação --}}
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Campos obrigatórios: data da aula, situação e presença dos alunos.
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            Salvar lançamento
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>
@endsection

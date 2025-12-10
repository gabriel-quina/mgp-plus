{{-- resources/views/students/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Alunos')

@section('content')
    <header class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="mb-0">Alunos</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('students.create') }}" class="btn btn-primary">Novo aluno</a>
            <a href="{{ route('students.index') }}" class="btn btn-outline-secondary">Atualizar</a>
        </div>
    </header>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="get" action="{{ route('students.index') }}" class="row gy-2 gx-2 align-items-end mb-3">
        <div class="col-12 col-sm-6 col-lg-4">
            <label for="q" class="form-label">Buscar (nome, CPF, e-mail)</label>
            <input type="text" id="q" name="q" value="{{ $q ?? '' }}" class="form-control"
                placeholder="ex.: Maria, 123.456..., @dominio.com">
        </div>
        <div class="col-12 col-sm-auto">
            <button class="btn btn-outline-primary">Filtrar</button>
        </div>
        @if (($q ?? '') !== '')
            <div class="col-12 col-sm-auto">
                <a class="btn btn-link" href="{{ route('students.index') }}">Limpar</a>
            </div>
        @endif
    </form>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Aluno</th>
                            <th>CPF</th>
                            <th>Escola</th>
                            <th>Cor/raça</th>
                            <th>PcD</th>
                            <th style="width: 220px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                            @php
                                $enr = $student->currentEnrollment;
                                $escola = optional(optional($enr)->school)->name;
                            @endphp
                            <tr>
                                <td class="fw-semibold">
                                    {{ $student->display_name }}
                                    @if ($student->social_name && $student->social_name !== $student->name)
                                        <div class="text-muted small">Nome civil: {{ $student->name }}</div>
                                    @endif
                                </td>
                                <td class="text-nowrap">{{ $student->cpf_formatted ?? '—' }}</td>
                                <td class="text-muted">{{ $escola ?? '—' }}</td>
                                <td class="text-capitalize">{{ $student->race_color ?? '—' }}</td>
                                <td>
                                    @if ($student->has_disability)
                                        <span class="badge bg-warning text-dark">Sim</span>
                                    @else
                                        <span class="badge bg-secondary">Não</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a class="btn btn-sm btn-outline-info"
                                            href="{{ route('students.show', $student->id) }}">Ver</a>
                                        <a class="btn btn-sm btn-outline-primary"
                                            href="{{ route('students.edit', $student->id) }}">Editar</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">Nenhum aluno encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if (method_exists($students, 'links'))
            <div class="card-footer">
                {{ $students->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection

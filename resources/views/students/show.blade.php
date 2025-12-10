{{-- resources/views/students/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Aluno — ' . $student->display_name)

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3">
        <h1 class="mb-0">👤 {{ $student->display_name }}</h1>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-primary" href="{{ route('students.edit', $student->id) }}">Editar</a>
            <a class="btn btn-link" href="{{ route('students.index') }}">← Voltar</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @php
        $enr = $student->currentEnrollment; // episódio ativo
        $schoolName = optional(optional($enr)->school)->name;
        $gradeName = optional(optional($enr)->gradeLevel)->name;
        $year = optional($enr)->academic_year;
        $shiftLabel = optional($enr)->shift_label ?? null;
        $scopeLabel = optional($enr)->transfer_scope_label ?? null;
        $originName = optional(optional($enr)->originSchool)->name;
    @endphp

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Dados básicos</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Nome civil</dt>
                        <dd class="col-7">{{ $student->name ?? '—' }}</dd>
                        <dt class="col-5">CPF</dt>
                        <dd class="col-7">{{ $student->cpf_formatted ?? '—' }}</dd>
                        <dt class="col-5">Nascimento</dt>
                        <dd class="col-7">{{ optional($student->birthdate)->format('d/m/Y') ?? '—' }}</dd>
                        <dt class="col-5">E-mail</dt>
                        <dd class="col-7">{{ $student->email ?? '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Perfil</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Cor/raça (IBGE)</dt>
                        <dd class="col-7 text-capitalize">{{ $student->race_color ?? '—' }}</dd>
                        <dt class="col-5">Pessoa com Deficiência</dt>
                        <dd class="col-7">
                            @if ($student->has_disability)
                                <span class="badge bg-warning text-dark">Sim</span>
                                @php
                                    $types = collect($student->disability_types ?? [])
                                        ->map(function ($t) {
                                            return is_numeric($t) ? $t : null;
                                        })
                                        ->filter()
                                        ->all();
                                @endphp
                                @if (!empty($types))
                                    <div class="small text-muted mt-1">Tipos: {{ implode(', ', $types) }}</div>
                                @endif
                            @else
                                <span class="badge bg-secondary">Não</span>
                            @endif
                        </dd>
                        <dt class="col-5">Alergias</dt>
                        <dd class="col-7">{{ $student->allergies ?? '—' }}</dd>
                        <dt class="col-5">Contato emergência</dt>
                        <dd class="col-7">{{ $student->emergency_contact_name ?? '—' }}</dd>
                        <dt class="col-5">Telefone (emerg.)</dt>
                        <dd class="col-7">{{ $student->emergency_contact_phone ?? '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Dados da Matricula Ativa</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Escola</dt>
                        <dd class="col-7">{{ $schoolName ?? '—' }}</dd>
                        <dt class="col-5">Série / Ano letivo</dt>
                        <dd class="col-7">
                            {{ $gradeName ?? '—' }}
                            @if ($year)
                                <span class="text-muted">• {{ $year }}</span>
                            @endif
                        </dd>
                        <dt class="col-5">Turno</dt>
                        <dd class="col-7">{{ $shiftLabel ?? '—' }}</dd>
                        <dt class="col-5">Origem</dt>
                        <dd class="col-7">
                            @if ($scopeLabel)
                                <span class="badge bg-light text-dark">{{ $scopeLabel }}</span>
                            @endif
                            {{ $originName ? '• ' . $originName : '' }}
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Histórico Escolar</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Escola</dt>
                        <dd class="col-7">{{ $schoolName ?? '—' }}</dd>
                        <dt class="col-5">Série / Ano letivo</dt>
                        <dd class="col-7">
                            {{ $gradeName ?? '—' }}
                            @if ($year)
                                <span class="text-muted">• {{ $year }}</span>
                            @endif
                        </dd>
                        <dt class="col-5">Turno</dt>
                        <dd class="col-7">{{ $shiftLabel ?? '—' }}</dd>
                        <dt class="col-5">Origem</dt>
                        <dd class="col-7">
                            @if ($scopeLabel)
                                <span class="badge bg-light text-dark">{{ $scopeLabel }}</span>
                            @endif
                            {{ $originName ? '• ' . $originName : '' }}
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection

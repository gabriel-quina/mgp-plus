{{-- resources/views/schools/students/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Aluno — ' . ($student->display_name ?? $student->name ?? 'Aluno'))

@section('content')
    @php
        $backUrl = route('schools.students.index', $school);

        if (request('back') === 'students') {
            $query = array_filter([
                'grade_level' => request('grade_level'),
                'q' => request('q'),
            ], fn ($value) => ! is_null($value) && $value !== '');

            if (! empty($query)) {
                $backUrl .= '?' . http_build_query($query);
            }
        }
    @endphp

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h1 class="mb-0">👤 {{ $student->display_name }}</h1>
            <small class="text-muted">{{ $school->name }}</small>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-link" href="{{ $backUrl }}">← Voltar</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @php
        $enr = $currentEnrollment;
        $gradeName = optional(optional($enr)->gradeLevel)->name;
        $year = optional($enr)->academic_year;
        $shiftLabel = optional($enr)->shift_label ?? null;
        $scopeLabel = optional($enr)->transfer_scope_label ?? null;
        $originName = optional(optional($enr)->originSchool)->name;
    @endphp

    <div class="row g-3 mb-3">
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
                <div class="card-header">Matrícula atual nesta escola</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Série / Ano letivo</dt>
                        <dd class="col-7">
                            {{ $gradeName ?? '—' }}
                            @if ($year)
                                <span class="text-muted">• {{ $year }}</span>
                            @endif
                        </dd>
                        <dt class="col-5">Turno</dt>
                        <dd class="col-7">{{ $shiftLabel ?? '—' }}</dd>
                        <dt class="col-5">Status</dt>
                        <dd class="col-7">{{ optional($enr)->status_label ?? '—' }}</dd>
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

    <div class="card mb-3">
        <div class="card-header">Contato e observações</div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-4">Contato emergência</dt>
                <dd class="col-8">{{ $student->emergency_contact_name ?? '—' }}</dd>
                <dt class="col-4">Telefone (emerg.)</dt>
                <dd class="col-8">{{ $student->emergency_contact_phone ?? '—' }}</dd>
                <dt class="col-4">Alergias</dt>
                <dd class="col-8">{{ $student->allergies ?? '—' }}</dd>
            </dl>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Histórico de matrículas nesta escola</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Série</th>
                            <th>Ano letivo</th>
                            <th>Turno</th>
                            <th>Status</th>
                            <th>Início</th>
                            <th>Fim</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($enrollments as $enrollment)
                            <tr>
                                <td>{{ $enrollment->gradeLevel->name ?? '—' }}</td>
                                <td>{{ $enrollment->academic_year ?? '—' }}</td>
                                <td>{{ $enrollment->shift_label ?? '—' }}</td>
                                <td>{{ $enrollment->status_label ?? '—' }}</td>
                                <td>{{ optional($enrollment->started_at)->format('d/m/Y') ?? '—' }}</td>
                                <td>{{ optional($enrollment->ended_at)->format('d/m/Y') ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

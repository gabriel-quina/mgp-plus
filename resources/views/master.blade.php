@extends('layouts.app')

@section('title', 'Dashboard Master')

@section('content')
    @php
        $selectedScope = old('scope', request('scope', 'company'));
        $selectedSchoolId = old('school_id', request('school_id'));
        $selectedRole = old('acting_role', request('acting_role'));
        $actingRoles = [
            'gestor_rede' => 'Gestor da rede',
            'gestor_escola' => 'Gestor da escola',
        ];
    @endphp

    <h1 class="h3 mb-2">Dashboard Master</h1>

    <div class="text-muted mb-3">
        {{ $user->name }} — {{ $user->email ?? $user->cpf }}
    </div>

    <div class="alert alert-dark">
        Você está em modo <strong>Master</strong>. Defina abaixo o escopo e role em que deseja atuar.
    </div>

    <div class="card">
        <div class="card-header">Atuar como</div>

        <div class="card-body">
            <form class="row gy-3" method="GET" action="{{ route('admin.dashboard') }}">
                <div class="col-md-4">
                    <label for="scope" class="form-label">Escopo</label>
                    <select id="scope" name="scope" class="form-select" required>
                        <option value="company" @selected($selectedScope === 'company')>Empresa (Rede)</option>
                        <option value="school" @selected($selectedScope === 'school')>Escola</option>
                    </select>
                </div>

                <div class="col-md-4" id="school-id-field">
                    <label for="school_id" class="form-label">Escola</label>
                    <select id="school_id" name="school_id" class="form-select" @disabled($selectedScope !== 'school')>
                        <option value="">Selecione uma escola</option>
                        @foreach ($schools as $school)
                            <option value="{{ $school->id }}" @selected((string) $selectedSchoolId === (string) $school->id)>
                                {{ $school->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Obrigatório quando o escopo for escola.</div>
                </div>

                <div class="col-md-4">
                    <label for="acting_role" class="form-label">Role em atuação</label>
                    <select id="acting_role" name="acting_role" class="form-select">
                        <option value="">Selecione a role</option>
                        @foreach ($actingRoles as $roleValue => $roleLabel)
                            <option value="{{ $roleValue }}" @selected($selectedRole === $roleValue)>
                                {{ $roleLabel }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">As duas opções têm o mesmo acesso no momento.</div>
                </div>

                <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Aplicar</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            (() => {
                const scopeSelect = document.getElementById('scope');
                const schoolField = document.getElementById('school-id-field');
                const schoolSelect = document.getElementById('school_id');

                const toggleSchoolField = () => {
                    const isSchool = scopeSelect.value === 'school';
                    schoolField.classList.toggle('d-none', !isSchool);
                    schoolSelect.toggleAttribute('disabled', !isSchool);
                    schoolSelect.toggleAttribute('required', isSchool);

                    if (!isSchool) {
                        schoolSelect.value = '';
                    }
                };

                scopeSelect.addEventListener('change', toggleSchoolField);
                toggleSchoolField();
            })();
        </script>
    @endpush
@endsection


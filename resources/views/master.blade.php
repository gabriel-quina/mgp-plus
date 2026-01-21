@extends('layouts.app')

@section('title', 'Dashboard Master')

@section('content')
    @php
        // Estado vindo de querystring (GET) e/ou sessão (middleware ActingScope)
        $sessionScope = $actingScope ?? session('acting_scope');
        $sessionSchoolId = $actingSchoolId ?? session('acting_school_id');

        $selectedScope = old('scope', request('scope', $sessionScope ?? 'company'));
        $selectedSchoolId = old('school_id', request('school_id', $sessionSchoolId));
        $selectedRole = old('acting_role', request('acting_role'));

        $actingRoles = [
            'gestor_rede' => 'Gestor da rede',
            'gestor_escola' => 'Gestor da escola',
        ];

        // Tenta resolver nome da escola atual (se estiver em school)
        $currentSchoolName = null;
        if (!empty($selectedSchoolId) && isset($schools)) {
            $found = $schools->firstWhere('id', (int) $selectedSchoolId);
            $currentSchoolName = $found?->name;
        }

        // Template para montar a URL do dashboard da escola via JS
        // (gera algo como /escola/0 e depois substituímos o 0 pelo id escolhido)
        $schoolDashTemplate = route('schools.dashboard', ['school' => 0], absolute: false);
    @endphp

    <h1 class="h3 mb-2">Dashboard Master</h1>

    <div class="text-muted mb-3">
        {{ $user->name }} — {{ $user->email ?? $user->cpf }}
    </div>

    {{-- Estado atual --}}
    @if ($sessionScope === 'school' && $sessionSchoolId)
        <div class="alert alert-info d-flex align-items-center justify-content-between">
            <div>
                Você está atuando no escopo <strong>Escola</strong>
                @if ($currentSchoolName)
                    — <strong>{{ $currentSchoolName }}</strong>
                @else
                    — <strong>#{{ $sessionSchoolId }}</strong>
                @endif
            </div>

            {{-- Voltar para Admin: GET com scope=company --}}
            <a class="btn btn-sm btn-outline-primary"
               href="{{ route('admin.dashboard', ['scope' => 'company']) }}">
                Voltar para Admin
            </a>
        </div>
    @else
        <div class="alert alert-dark">
            Você está em modo <strong>Admin/Empresa</strong>. Selecione uma escola abaixo para atuar nela.
        </div>
    @endif

    {{-- Card de atuação --}}
    <div class="card mb-3">
        <div class="card-header">Atuar como</div>

        <div class="card-body">
            <form class="row gy-3" method="GET" action="{{ route('admin.dashboard') }}">
                <div class="col-md-4">
                    <label for="scope" class="form-label">Escopo</label>
                    <select id="scope" name="scope" class="form-select" required>
                        <option value="school" @selected($selectedScope === 'school')>Escola</option>
                    </select>
                </div>

                <div class="col-md-4" id="school-id-field">
                    <label for="school_id" class="form-label">Escola</label>
                    <select id="school_id" name="school_id" class="form-select">
                        <option value="">Selecione uma escola</option>
                        @foreach ($schools as $school)
                            <option value="{{ $school->id }}" @selected((string) $selectedSchoolId === (string) $school->id)>
                                {{ $school->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Obrigatório quando o escopo for escola.</div>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2">
                    <a class="btn btn-outline-secondary"
                       id="go-school"
                       href="#">
                        Ir para a escola
                    </a>
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
                const goBtn = document.getElementById('go-school');

                const template = @json($schoolDashTemplate);

                const buildSchoolUrl = (id) => {
                    // template termina com "/0"
                    return template.replace(/\/0$/, '/' + id);
                };

                const refreshGoLink = () => {
                    const id = (schoolSelect.value || '').trim();
                    if (!id) {
                        goBtn.setAttribute('href', '#');
                        return;
                    }
                    goBtn.setAttribute('href', buildSchoolUrl(id));
                };

                const toggleSchoolField = () => {
                    const isSchool = scopeSelect.value === 'school';
                    schoolField.classList.toggle('d-none', !isSchool);
                    schoolSelect.toggleAttribute('disabled', !isSchool);
                    schoolSelect.toggleAttribute('required', isSchool);

                    if (!isSchool) {
                        schoolSelect.value = '';
                    }
                    refreshGoLink();
                };

                goBtn.addEventListener('click', (e) => {
                    // impede navegar se não tiver escola selecionada
                    const id = (schoolSelect.value || '').trim();
                    if (!id) {
                        e.preventDefault();
                    }
                });

                schoolSelect.addEventListener('change', refreshGoLink);

                scopeSelect.addEventListener('change', toggleSchoolField);
                toggleSchoolField();
            })();
        </script>
    @endpush
@endsection


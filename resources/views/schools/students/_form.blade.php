@php
    // Normaliza gradeLevels para array (pluck() costuma vir como Collection)
    $gradeLevels = $gradeLevels ?? [];
    if ($gradeLevels instanceof \Illuminate\Support\Collection) {
        $gradeLevels = $gradeLevels->all();
    }

    // Helpers de preenchimento (prioriza old())
    $sval = fn($k, $default = null) => old("student.$k", isset($student) ? data_get($student, $k, $default) : $default);
    $eval = fn($k, $default = null) => old("enrollment.$k", $default);

    // PcD
    $hasDisability = (bool) $sval('has_disability', false);

    // Tipos de PcD: ideal é o controller passar [id => label].
    // Fallback com IDs "estáveis":
    $pcdOptions = $pcdOptions ?? [
        1 => 'Visual',
        2 => 'Auditiva',
        3 => 'Física',
        4 => 'Intelectual',
        5 => 'Psicossocial',
        6 => 'TEA',
        7 => 'Múltipla',
        8 => 'Outra',
    ];

    // Selecionados (em edição, pegue do $student->disability_types que é JSON/array de IDs)
    $selectedTypes = collect(old('student.disability_type_ids', data_get($student ?? null, 'disability_types', [])))
        ->map(fn($v) => (string) $v)
        ->all();

    // Estamos editando?
    $isEdit = isset($student) && $student->exists;
@endphp

<div id="student-form" class="row g-3">

    {{-- ====================== DADOS DO ALUNO ====================== --}}
    <div class="col-12">
        <h5 class="border-bottom pb-2 mb-0">Dados do aluno</h5>
    </div>

    <div class="col-md-6">
        <label class="form-label">Nome *</label>
        <input type="text" name="student[name]" class="form-control" value="{{ $sval('name') }}" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">CPF</label>
        <input type="text" name="student[cpf]" class="form-control" value="{{ $sval('cpf') }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Nascimento</label>
        <input
            type="date"
            name="student[birthdate]"
            class="form-control"
            value="{{ $sval('birthdate') }}"
        >
    </div>

    <div class="col-md-6">
        <label class="form-label">E-mail</label>
        <input type="email" name="student[email]" class="form-control" value="{{ $sval('email') }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Cor/raça (IBGE)</label>
        <input type="text" name="student[race_color]" class="form-control text-capitalize"
            value="{{ $sval('race_color') }}" placeholder="branca, parda, preta, amarela, indígena...">
    </div>

    <div class="col-12">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="has_disability" name="student[has_disability]"
                value="1" @checked($hasDisability)>
            <label class="form-check-label" for="has_disability">Pessoa com Deficiência (PcD)</label>
        </div>
    </div>

    <div class="col-12">
        <fieldset id="pcd_types_fieldset" class="border rounded p-3">
            <legend class="float-none w-auto px-2 small mb-0">Tipos de PcD</legend>

            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-2 mt-1">
                @foreach ($pcdOptions as $id => $label)
                    <div class="col">
                        <div class="form-check">
                            <input class="form-check-input pcd-type" type="checkbox"
                                name="student[disability_type_ids][]" value="{{ $id }}"
                                id="pcd_{{ $id }}" @checked(in_array((string) $id, $selectedTypes, true))>
                            <label class="form-check-label" for="pcd_{{ $id }}">{{ $label }}</label>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-2">
                <label class="form-label">Detalhes (opcional)</label>
                <textarea class="form-control" name="student[disability_details]" rows="2">{{ $sval('disability_details') }}</textarea>
            </div>
        </fieldset>

        <div class="form-text">Os tipos acima só ficam ativos se “PcD” estiver marcado.</div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Alergias (opcional)</label>
        <input type="text" name="student[allergies]" class="form-control" value="{{ $sval('allergies') }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Contato de emergência</label>
        <input type="text" name="student[emergency_contact_name]" class="form-control"
            value="{{ $sval('emergency_contact_name') }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Telefone (emergência)</label>
        <input type="text" name="student[emergency_contact_phone]" class="form-control"
            value="{{ $sval('emergency_contact_phone') }}">
    </div>

    {{-- ====================== MATRÍCULA (somente CREATE) ====================== --}}
    @if (! $isEdit)
        <div class="col-12 mt-2">
            <h5 class="border-bottom pb-2 mb-0">Matrícula inicial</h5>
        </div>

        <div class="col-md-5">
            <label class="form-label">Ano escolar / Série *</label>
            <select name="enrollment[grade_level_id]" class="form-select" required>
                <option value="">Selecione…</option>
                @foreach ($gradeLevels as $id => $name)
                    <option value="{{ $id }}" @selected((string) $eval('grade_level_id') === (string) $id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Ano letivo *</label>
            <input type="number" name="enrollment[academic_year]" class="form-control"
                value="{{ $eval('academic_year', now()->year) }}" min="1900" max="9999" required>
        </div>

        <div class="col-md-3">
            <label class="form-label">Turno</label>
            <select name="enrollment[shift]" class="form-select">
                @php $shift = $eval('shift','morning'); @endphp
                <option value="morning" @selected($shift === 'morning')>Manhã</option>
                <option value="afternoon" @selected($shift === 'afternoon')>Tarde</option>
                <option value="evening" @selected($shift === 'evening')>Noite</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Início do vínculo</label>
            <input type="date" name="enrollment[started_at]" class="form-control"
                value="{{ $eval('started_at', now()->toDateString()) }}">
        </div>
    @endif

    {{-- ====================== AÇÕES ====================== --}}
    <div class="col-12 d-flex gap-2">
        <button class="btn btn-primary" type="submit">{{ $submitLabel ?? 'Salvar' }}</button>
        <a href="{{ route('schools.students.index', $school) }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</div>

@push('scripts')
    <script>
        (function() {
            // ---------- PcD toggle ----------
            const chk = document.getElementById('has_disability');
            const fieldset = document.getElementById('pcd_types_fieldset');

            function togglePcd() {
                if (!fieldset) return;
                const enabled = !!(chk && chk.checked);

                fieldset.classList.toggle('opacity-50', !enabled);
                fieldset.querySelectorAll('input,textarea,select').forEach(el => {
                    // não apaga valores automaticamente; apenas desabilita
                    el.disabled = !enabled;
                });
            }

            if (chk && fieldset) {
                togglePcd();
                chk.addEventListener('change', togglePcd);
            }
        })();
    </script>
@endpush


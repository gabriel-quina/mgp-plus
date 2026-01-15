@php
    // fallback para quando o controller não passar a lista
    $gradeLevels = $gradeLevels ?? [];

    // Helpers de preenchimento (prioriza old())
    $oval = fn($k, $default = null) => old($k, $default);
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

    // selecionados (em edição, pegue do $student->disability_types que é JSON de IDs)
    $selectedTypes = collect(old('student.disability_type_ids', data_get($student ?? null, 'disability_types', [])))
        ->map(fn($v) => (string) $v)
        ->all();

    // radios do escopo
    $scope = $eval('transfer_scope', 'first'); // first|internal|external

    // estamos editando?
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

    <div class="col-md-6">
        <label class="form-label">Nome social</label>
        <input type="text" name="student[social_name]" class="form-control" value="{{ $sval('social_name') }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">CPF</label>
        <input type="text" name="student[cpf]" class="form-control" value="{{ $sval('cpf') }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Nascimento</label>
        <input type="date" name="student[birthdate]" class="form-control"
            value="{{ old('student.birthdate', optional($student->birthdate ?? null)->format('Y-m-d')) }}">
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
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-2">
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

    @if (!$isEdit)
        {{-- ====================== MATRÍCULA (EPISÓDIO) — SOMENTE NA CRIAÇÃO ====================== --}}
        <div class="col-12 mt-2">
            <h5 class="border-bottom pb-2 mb-0">Matrícula inicial</h5>
        </div>

        {{-- DESTINO (typeahead obrigatório) --}}
        <div class="col-md-7 position-relative">
            <label class="form-label">Escola de destino *</label>
            <input type="text" class="form-control" id="dest_school_search" placeholder="Digite 2+ letras..."
                autocomplete="off">
            <input type="hidden" name="enrollment[destination_school_id]" id="destination_school_id"
                value="{{ $eval('destination_school_id') }}">
            <ul id="dest_results" class="list-group position-absolute w-100 shadow" style="z-index:10;display:none;">
            </ul>
            <div class="form-text">Selecione a escola no autocomplete. (No futuro, virá preenchido pelo login da
                escola.)
            </div>
        </div>

        <div class="col-md-5">
            <label class="form-label">Ano escolar / Série *</label>
            <select name="enrollment[grade_level_id]" class="form-select" required>
                @if (empty($gradeLevels))
                    <option value="">{{ $oval('grade_placeholder', 'Selecione…') }}</option>
                @endif
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

        <div class="col-md-12">
            <label class="form-label d-block mb-1">Tipo de matrícula *</label>
            <div class="d-flex flex-wrap gap-3">
                @php $rad = fn($v,$lbl)=>"<label class='form-check'><input class='form-check-input' type='radio' name='enrollment[transfer_scope]' value='$v' ".($scope===$v?'checked':'')."> <span class='form-check-label'>$lbl</span></label>"; @endphp
                {!! $rad('first', 'Primeira matrícula') !!}
                {!! $rad('internal', 'Transferência interna') !!}
                {!! $rad('external', 'Transferência externa') !!}
            </div>
        </div>

        {{-- ORIGEM — INTERNA (mesma cidade): busca travada até escolher o destino --}}
        <div id="origin_internal" class="col-12 border rounded p-3" style="display: none;">
            <h6 class="mb-3">Origem — interna (mesma cidade)</h6>
            <div class="row g-3">
                <div class="col-md-6 position-relative">
                    <label class="form-label">Buscar escola de origem (opcional)</label>
                    <input type="text" class="form-control" id="origin_school_search"
                        placeholder="Digite 2+ letras…" autocomplete="off" disabled>
                    <input type="hidden" id="destination_city_id" name="enrollment[destination_city_id]">
                    <input type="hidden" name="enrollment[origin_school_id]" id="origin_school_id"
                        value="{{ $eval('origin_school_id') }}">
                    <ul id="origin_results" class="list-group position-absolute w-100 shadow"
                        style="z-index:10;display:none;"></ul>
                    <div class="form-text">A busca será habilitada após selecionar a escola de destino.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nome da escola (origem)</label>
                    <input type="text" class="form-control origin-text" name="enrollment[origin_school_name]"
                        id="origin_school_name" value="{{ $eval('origin_school_name') }}"
                        placeholder="Ex.: EMEF João XX" disabled>
                </div>
            </div>
        </div>

        {{-- ORIGEM — EXTERNA (outra cidade): NOME + CIDADE + UF (select) + typeahead opcional --}}
        <div id="origin_external" class="col-12 border rounded p-3" style="display:none;">
            <h6 class="mb-3">Origem — externa (outra cidade)</h6>

            <div class="row g-3 align-items-end">
                <div class="col-md-7 position-relative">
                    <label class="form-label">Buscar escola de origem (opcional)</label>
                    <input type="text" class="form-control" id="ext_school_search"
                        placeholder="Digite 2+ letras…" autocomplete="off">
                    {{-- Reaproveita o mesmo hidden de origem --}}
                    <input type="hidden" name="enrollment[origin_school_id]" id="ext_school_id">
                    <ul id="ext_results" class="list-group position-absolute w-100 shadow"
                        style="z-index:10;display:none;"></ul>
                    <div class="form-text">
                        Se encontrar e selecionar, os campos abaixo são travados.
                        Se não, preencha manualmente — criaremos uma Escola com <code>is_historical=1</code>.
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label d-block">&nbsp;</label>
                    <button type="button" class="btn btn-outline-secondary" id="ext_clear">Limpar seleção</button>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-5">
                    <label class="form-label">Nome da escola (origem)</label>
                    <input type="text" class="form-control ext-field" name="enrollment[origin_school_name]"
                        id="ext_school_name" value="{{ $eval('origin_school_name') }}" placeholder="Ex.: Colégio X">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Cidade (origem)</label>
                    <input type="text" class="form-control ext-field" name="enrollment[origin_city_name]"
                        id="ext_city_name" value="{{ $eval('origin_city_name') }}" placeholder="Ex.: Curitiba">
                </div>
                </div<div class="col-md-3">
                <label class="form-label">UF</label>
                <select class="form-select ext-field" name="enrollment[origin_state_id]" id="ext_state_id">
                    <option value="">Selecione…</option>
                    @foreach ($states ?? [] as $id => $name)
                        <option value="{{ $id }}" @selected(old('enrollment.origin_state_id') == $id)>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif

    {{-- AÇÕES --}}
    <div class="col-12 d-flex gap-2">
        <button class="btn btn-primary" type="submit">{{ $submitLabel ?? 'Salvar' }}</button>
        <a href="{{ route('students.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</div>

@push('scripts')
    <script>
        (function() {
            // ---------- PcD toggle (sempre disponível) ----------
            const chk = document.getElementById('has_disability');
            const fieldset = document.getElementById('pcd_types_fieldset');

            function togglePcd() {
                const enabled = chk && chk.checked;
                if (!fieldset) return;
                fieldset.classList.toggle('opacity-50', !enabled);
                fieldset.querySelectorAll('input,textarea,select').forEach(el => {
                    el.disabled = !enabled;
                    if (!enabled && el.type === 'checkbox') el.checked = false;
                    if (!enabled && (el.tagName === 'TEXTAREA' || el.tagName === 'INPUT')) el.value = '';
                });
            }
            if (chk && fieldset) {
                togglePcd();
                chk.addEventListener('change', togglePcd);
            }
        })();
    </script>
@endpush

@if (!$isEdit)
    @push('scripts')
        <script>
            (function() {
                // ---------- Helpers ----------
                const $ = (sel) => document.querySelector(sel);
                const show = (el) => el && (el.style.display = '');
                const hide = (el) => el && (el.style.display = 'none');
                const disableWithin = (containerSel, disabled) => {
                    document.querySelectorAll(
                            `${containerSel} input, ${containerSel} button, ${containerSel} select, ${containerSel} textarea`
                        )
                        .forEach(el => el.disabled = disabled);
                };

                // ---------- Escopo (first/internal/external) ----------
                const scopeRadios = document.querySelectorAll('input[name="enrollment[transfer_scope]"]');
                const paneInternal = document.getElementById('origin_internal');
                const paneExternal = document.getElementById('origin_external');

                function applyScope(scope) {
                    if (scope === 'internal') {
                        show(paneInternal);
                        hide(paneExternal);
                    } else if (scope === 'external') {
                        show(paneExternal);
                        hide(paneInternal);
                    } else {
                        hide(paneInternal);
                        hide(paneExternal);
                    }
                }
                scopeRadios.forEach(r => r.addEventListener('change', e => applyScope(e.target.value)));
                // inicial
                const checkedScope = Array.from(scopeRadios).find(r => r.checked)?.value || 'first';
                applyScope(checkedScope);

                // ---------- Typeahead (destino, origem interna, origem externa) ----------
                function attachSchoolTypeahead(cfg) {
                    const input = document.getElementById(cfg.inputId);
                    const results = document.getElementById(cfg.resultsId);
                    const hidden = document.getElementById(cfg.hiddenId);
                    if (!input || !results || !hidden) return;

                    let timer = null,
                        lastQ = '';
                    input.addEventListener('input', () => {
                        const q = input.value.trim();
                        if (q.length < 2) {
                            results.style.display = 'none';
                            results.innerHTML = '';
                            hidden.value = '';
                            return;
                        }
                        if (q === lastQ) return;
                        lastQ = q;
                        clearTimeout(timer);
                        timer = setTimeout(async () => {
                            try {
                                const base = `/api/escolas/buscar?q=${encodeURIComponent(q)}`;
                                const extra = cfg.extraParams ? cfg.extraParams() : {};
                                const qs = Object.entries(extra).map(([k, v]) =>
                                    `${k}=${encodeURIComponent(v)}`).join('&');
                                const url = qs ? `${base}&${qs}` : base;

                                const resp = await fetch(url);
                                const data = resp.ok ? await resp.json() : [];
                                render(data);
                            } catch (_e) {
                                results.innerHTML =
                                    `<li class="list-group-item text-muted">Sem resultados</li>`;
                                results.style.display = '';
                            }
                        }, 150);
                    });

                    function render(items) {
                        results.innerHTML = '';
                        if (!items || !items.length) {
                            results.innerHTML = `<li class="list-group-item text-muted">Sem resultados</li>`;
                        } else {
                            items.forEach(it => {
                                const uf = it.state_code || it.state_uf || '';
                                const li = document.createElement('li');
                                li.className = 'list-group-item list-group-item-action';
                                li.innerHTML =
                                    `${it.name} <small class="text-muted">${it.city_name ?? ''}${uf ? ' - ' + uf : ''}${it.is_historical ? ' (histórica)' : ''}</small>`;
                                li.addEventListener('click', () => {
                                    const tag = uf ? ` (${it.city_name ?? ''} - ${uf})` : (it.city_name ?
                                        ` (${it.city_name})` : '');
                                    input.value = `${it.name}${tag}`;
                                    hidden.value = it.id;
                                    cfg.onPick && cfg.onPick(it);
                                    results.style.display = 'none';
                                    results.innerHTML = '';
                                });
                                results.appendChild(li);
                            });
                        }
                        results.style.display = '';
                    }

                    // fechar ao clicar fora
                    document.addEventListener('click', (e) => {
                        if (!results.contains(e.target) && e.target !== input) {
                            results.style.display = 'none';
                        }
                    });
                }

                // --- Inicialmente, ORIGEM INTERNA fica DESABILITADA até escolher destino ---
                disableWithin('#origin_internal', true);

                // Destino
                attachSchoolTypeahead({
                    inputId: 'dest_school_search',
                    resultsId: 'dest_results',
                    hiddenId: 'destination_school_id',
                    onPick: (it) => {
                        // guarda a cidade do destino
                        const dc = document.getElementById('destination_city_id');
                        if (dc) dc.value = it.city_id || '';

                        // limpa qualquer seleção de origem
                        const hid = document.getElementById('origin_school_id');
                        if (hid) hid.value = '';
                        const originName = document.getElementById('origin_school_name');
                        if (originName) {
                            originName.value = '';
                        }

                        // destrava origem interna
                        disableWithin('#origin_internal', false);
                    }
                });

                // Origem interna (filtra por city_id do destino)
                attachSchoolTypeahead({
                    inputId: 'origin_school_search',
                    resultsId: 'origin_results',
                    hiddenId: 'origin_school_id',
                    onPick: () => {
                        // trava campo de texto manual ao escolher uma escola
                        const t = document.getElementById('origin_school_name');
                        if (t) {
                            t.value = '';
                            t.disabled = true;
                        }
                    },
                    extraParams: () => {
                        const dc = document.getElementById('destination_city_id')?.value;
                        return dc ? {
                            city_id: dc
                        } : {};
                    }
                });

                // Se digitar no nome manual, limpa seleção do typeahead e permite digitar
                const originName = document.getElementById('origin_school_name');
                if (originName) {
                    originName.addEventListener('input', () => {
                        const hid = document.getElementById('origin_school_id');
                        if (hid) hid.value = '';
                        originName.disabled = false;
                    });
                }

                // Origem externa
                attachSchoolTypeahead({
                    inputId: 'ext_school_search',
                    resultsId: 'ext_results',
                    hiddenId: 'ext_school_id',
                    onPick: (it) => {
                        // Reaproveita hidden "oficial"
                        const hid = document.getElementById('origin_school_id');
                        if (hid) hid.value = it.id;
                        // trava os campos manuais
                        ['ext_school_name', 'ext_city_name', 'ext_state_id'].forEach(id => {
                            const el = document.getElementById(id);
                            if (el) {
                                el.value = '';
                                el.disabled = true;
                            }
                        });
                    }
                });
                const extClear = document.getElementById('ext_clear');
                if (extClear) {
                    extClear.addEventListener('click', () => {
                        const hid = document.getElementById('origin_school_id');
                        if (hid) hid.value = '';
                        ['ext_school_search', 'ext_school_name', 'ext_city_name', 'ext_state_id'].forEach(id => {
                            const el = document.getElementById(id);
                            if (el) {
                                el.value = '';
                                el.disabled = false;
                            }
                        });
                        const ul = document.getElementById('ext_results');
                        if (ul) {
                            ul.style.display = 'none';
                            ul.innerHTML = '';
                        }
                    });
                }
            })();
        </script>
    @endpush
@endif

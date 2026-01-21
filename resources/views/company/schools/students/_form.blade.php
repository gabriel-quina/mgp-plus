@php
  $val = fn($k, $d = null) => old($k, $d);
@endphp

<input type="hidden" name="enrollment[destination_school_id]" value="{{ $school->id }}">

<input type="hidden" name="enrollment[origin_school_id]" id="origin_school_id" value="{{ $val('enrollment.origin_school_id') }}">
<input type="hidden" name="enrollment[origin_school_name]" id="origin_school_name" value="{{ $val('enrollment.origin_school_name') }}">

<div class="row g-3">

  <div class="col-12">
    <h2 class="h5 mb-0">Dados do aluno</h2>
  </div>

  <div class="col-md-8">
    <label class="form-label">Nome</label>
    <input type="text" name="student[name]" class="form-control" value="{{ $val('student.name') }}" required>
  </div>

  <div class="col-md-4">
    <label class="form-label">Nome social</label>
    <input type="text" name="student[social_name]" class="form-control" value="{{ $val('student.social_name') }}">
  </div>

  <div class="col-md-4">
    <label class="form-label">CPF</label>
    <input type="text" name="student[cpf]" class="form-control" value="{{ $val('student.cpf') }}">
  </div>

  <div class="col-md-4">
    <label class="form-label">E-mail</label>
    <input type="email" name="student[email]" class="form-control" value="{{ $val('student.email') }}">
  </div>

  <div class="col-md-4">
    <label class="form-label">Data de nascimento</label>
    <input type="date" name="student[birthdate]" class="form-control" value="{{ $val('student.birthdate') }}">
  </div>

  <div class="col-12"><hr class="my-2"></div>

  <div class="col-12">
    <h2 class="h5 mb-0">Matrícula na escola</h2>
  </div>

  <div class="col-md-4">
    <label class="form-label">Ano escolar</label>
    <select name="enrollment[grade_level_id]" class="form-select" required>
      <option value="">Selecione…</option>
      @foreach($gradeLevels as $id => $name)
        <option value="{{ $id }}" @selected($val('enrollment.grade_level_id') == $id)>{{ $name }}</option>
      @endforeach
    </select>
  </div>

  <div class="col-md-4">
    <label class="form-label">Ano letivo</label>
    <input type="number" name="enrollment[academic_year]" class="form-control"
           value="{{ $val('enrollment.academic_year', now()->year) }}" required>
  </div>

  <div class="col-md-4">
    <label class="form-label">Turno</label>
    @php $shift = $val('enrollment.shift', 'morning'); @endphp
    <select name="enrollment[shift]" class="form-select">
      <option value="morning" @selected($shift==='morning')>Manhã</option>
      <option value="afternoon" @selected($shift==='afternoon')>Tarde</option>
      <option value="evening" @selected($shift==='evening')>Noite</option>
    </select>
  </div>

  <div class="col-md-4">
    <label class="form-label">Início</label>
    <input type="date" name="enrollment[started_at]" class="form-control" value="{{ $val('enrollment.started_at') }}">
  </div>

  <div class="col-md-8">
    <label class="form-label">Tipo de entrada</label>
    @php $ts = $val('enrollment.transfer_scope', 'first'); @endphp
    <select name="enrollment[transfer_scope]" id="transfer_scope" class="form-select">
      <option value="first" @selected($ts==='first')>Primeira matrícula</option>
      <option value="internal" @selected($ts==='internal')>Transferência (mesma cidade)</option>
      <option value="external" @selected($ts==='external')>Transferência (outra cidade/UF)</option>
    </select>
  </div>

  <div class="col-12" id="origin_block">
    <fieldset class="border rounded p-3">
      <legend class="float-none w-auto px-2 small mb-0">Origem (se transferência)</legend>

      <div class="row g-3 mt-1">

        <div class="col-md-12 position-relative">
          <label class="form-label">Escola de origem</label>

          <input
            type="text"
            id="origin_school_search"
            class="form-control"
            value="{{ $val('enrollment.origin_school_name') }}"
            placeholder="Digite pelo menos 2 letras…"
            autocomplete="off"
          >

          <div id="origin_suggestions"
               class="list-group position-absolute w-100"
               style="z-index: 1000; display:none;"></div>

          <div class="small mt-2 text-muted" id="origin_selected_meta" style="display:none;"></div>

          <div class="form-text">
            Transferência interna mostra apenas escolas da mesma cidade. Transferência externa mostra apenas escolas de outras cidades.
            Se você não selecionar nenhuma sugestão, o nome digitado será usado para criar escola histórica.
          </div>
        </div>

        <div class="col-md-6" id="origin_city_col">
          <label class="form-label">Cidade de origem (apenas externa)</label>
          <input type="text" name="enrollment[origin_city_name]" id="origin_city_name" class="form-control"
                 value="{{ $val('enrollment.origin_city_name') }}">
        </div>

        <div class="col-md-6" id="origin_state_col">
          <label class="form-label">Estado (apenas externa)</label>
          <select name="enrollment[origin_state_id]" id="origin_state_id" class="form-select">
            <option value="">Selecione…</option>
            @foreach($states as $st)
              <option value="{{ $st->id }}"
                      data-uf="{{ $st->uf }}"
                      @selected((string)$val('enrollment.origin_state_id') === (string)$st->id)>
                {{ $st->name }} ({{ $st->uf }})
              </option>
            @endforeach
          </select>
        </div>

      </div>
    </fieldset>
  </div>

  <div class="col-12 d-flex gap-2">
    <button class="btn btn-primary" type="submit">{{ $submitLabel ?? 'Salvar' }}</button>
    <a href="{{ route('admin.schools.students.index', $school) }}" class="btn btn-outline-secondary">Cancelar</a>
  </div>
</div>

<script>
(function () {
  const apiUrl = @json(url('/api/escolas/buscar'));
  const destCityId = @json($school->city_id);

  const transferScope = document.getElementById('transfer_scope');
  const originBlock = document.getElementById('origin_block');

  const searchInput = document.getElementById('origin_school_search');
  const suggestions = document.getElementById('origin_suggestions');
  const selectedMeta = document.getElementById('origin_selected_meta');

  const originId = document.getElementById('origin_school_id');
  const originName = document.getElementById('origin_school_name');

  const originCityCol = document.getElementById('origin_city_col');
  const originStateCol = document.getElementById('origin_state_col');
  const originCityName = document.getElementById('origin_city_name');
  const originStateId = document.getElementById('origin_state_id');

  let debounceTimer = null;

  function hideSuggestions() {
    suggestions.style.display = 'none';
    suggestions.innerHTML = '';
  }

  function clearSelectedMeta() {
    selectedMeta.style.display = 'none';
    selectedMeta.textContent = '';
  }

  function clearSelection() {
    originId.value = '';
    originName.value = '';
    clearSelectedMeta();
  }

  function stateIdByUf(uf) {
    if (!uf) return '';
    uf = String(uf).toUpperCase().trim();

    for (const opt of originStateId.options) {
      if (opt?.dataset?.uf && String(opt.dataset.uf).toUpperCase() === uf) {
        return opt.value;
      }
    }
    return '';
  }

  function updateOriginVisibility() {
    const scope = transferScope.value;

    if (scope === 'first') {
      originBlock.style.display = 'none';
      searchInput.disabled = true;
      hideSuggestions();

      searchInput.value = '';
      clearSelection();

      originCityName.value = '';
      originStateId.value = '';
      originCityName.disabled = true;
      originStateId.disabled = true;
      return;
    }

    originBlock.style.display = '';
    searchInput.disabled = false;

    const isExternal = scope === 'external';
    originCityCol.style.display = isExternal ? '' : 'none';
    originStateCol.style.display = isExternal ? '' : 'none';

    originCityName.disabled = !isExternal;
    originStateId.disabled = !isExternal;

    if (!isExternal) {
      originCityName.value = '';
      originStateId.value = '';
    }
  }

  async function fetchSchools(q) {
    const scope = transferScope.value;

    const url = new URL(apiUrl, window.location.origin);
    url.searchParams.set('q', q);

    if (scope === 'internal') {
      url.searchParams.set('city_id', String(destCityId));
    }

    const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
    if (!res.ok) return [];

    let items = await res.json();

    if (scope === 'external') {
      items = (items || []).filter(it => String(it.city_id || '') !== String(destCityId));
    } else if (scope === 'internal') {
      items = (items || []).filter(it => String(it.city_id || '') === String(destCityId));
    }

    return items;
  }

  function renderSuggestions(items) {
    suggestions.innerHTML = '';

    if (!items || !items.length) {
      hideSuggestions();
      return;
    }

    items.forEach((it) => {
      const meta = [it.city_name, it.state_uf].filter(Boolean).join('/');
      const hist = it.is_historical ? ' (histórica)' : '';

      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'list-group-item list-group-item-action';
      btn.textContent = `${it.name}${meta ? ' — ' + meta : ''}${hist}`;

      btn.addEventListener('click', () => {
        originId.value = String(it.id);
        originName.value = it.name;

        searchInput.value = `${it.name}${meta ? ' — ' + meta : ''}${hist}`;

        selectedMeta.style.display = '';
        selectedMeta.textContent = `Selecionada: ${it.name}${meta ? ' — ' + meta : ''}${hist}`;

        // Preenche automaticamente cidade/estado (útil principalmente no external)
        if (it.city_name) originCityName.value = it.city_name;
        const sid = stateIdByUf(it.state_uf);
        if (sid) originStateId.value = sid;

        hideSuggestions();
      });

      suggestions.appendChild(btn);
    });

    suggestions.style.display = '';
  }

  searchInput.addEventListener('focus', () => {
    hideSuggestions();
    if (originId.value) {
      clearSelection();
      searchInput.value = '';
      originCityName.value = '';
      originStateId.value = '';
    }
  });

  searchInput.addEventListener('input', () => {
    const q = (searchInput.value || '').trim();

    if (originId.value) {
      clearSelection();
    }

    originName.value = q;

    if (q.length < 2) {
      hideSuggestions();
      return;
    }

    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(async () => {
      const items = await fetchSchools(q);
      renderSuggestions(items);
    }, 250);
  });

  searchInput.addEventListener('blur', () => {
    setTimeout(hideSuggestions, 150);
    if (!originId.value) {
      originName.value = (searchInput.value || '').trim();
    }
  });

  transferScope.addEventListener('change', () => {
    hideSuggestions();
    clearSelection();
    originCityName.value = '';
    originStateId.value = '';
    updateOriginVisibility();
  });

  updateOriginVisibility();
})();
</script>


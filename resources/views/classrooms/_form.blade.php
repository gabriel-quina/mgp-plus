{{-- resources/views/classrooms/_form.blade.php --}}

{{-- Mensagens de sucesso/erro (padrão do projeto) --}}
@include('partials.messages')

<div class="card mb-3">
  <div class="card-header">Dados da Turma</div>
  <div class="card-body">
    <div class="mb-3">
      <label class="form-label">Escola</label>
      <select name="school_id" class="form-select @error('school_id') is-invalid @enderror" required>
        <option value="">-- Selecione --</option>
        @foreach($schools as $id => $name)
          <option value="{{ $id }}"
            @selected(old('school_id', isset($classroom) ? $classroom->school_id : null) == $id)>{{ $name }}</option>
        @endforeach
      </select>
      @error('school_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
      <label class="form-label">Turma Pai</label>
      <select name="parent_classroom_id" class="form-select @error('parent_classroom_id') is-invalid @enderror">
        <option value="">-- Nenhuma --</option>
        @foreach($parentClassrooms as $id => $name)
          <option value="{{ $id }}"
            @selected(old('parent_classroom_id', isset($classroom) ? $classroom->parent_classroom_id : null) == $id)>{{ $name }}</option>
        @endforeach
      </select>
      @error('parent_classroom_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Nome</label>
        <input name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', isset($classroom) ? $classroom->name : '') }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-md-3 mb-3">
        <label class="form-label">Turno</label>
        @php $shiftVal = old('shift', isset($classroom) ? $classroom->shift : ''); @endphp
        <select name="shift" class="form-select @error('shift') is-invalid @enderror" required>
          <option value="">-- Selecione --</option>
          <option value="morning"   @selected($shiftVal==='morning')>Manhã</option>
          <option value="afternoon" @selected($shiftVal==='afternoon')>Tarde</option>
          <option value="evening"   @selected($shiftVal==='evening')>Noite</option>
        </select>
        @error('shift') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-md-3 mb-3">
        <label class="form-label">Ano Letivo</label>
        <input type="number" name="academic_year"
               class="form-control @error('academic_year') is-invalid @enderror"
               value="{{ old('academic_year', isset($classroom) ? $classroom->academic_year : ($defaultYear ?? date('Y'))) }}"
               min="2000" max="2100" required>
        @error('academic_year') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>
    </div>

    <div class="form-check mb-3">
      <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input"
             @checked(old('is_active', isset($classroom) ? (bool)$classroom->is_active : true))>
      <label for="is_active" class="form-check-label">Ativa</label>
    </div>
  </div>
</div>

<div class="card mb-3">
  <div class="card-header">Anos Atendidos</div>
  <div class="card-body">
    @php
      $selectedGrades = isset($selectedGrades)
          ? $selectedGrades
          : (array) old('grade_level_ids', isset($classroom) ? $classroom->gradeLevels->pluck('id')->all() : []);
    @endphp
    <select name="grade_level_ids[]" class="form-select @error('grade_level_ids') is-invalid @enderror" multiple required>
      @foreach($gradeLevels as $id => $name)
        <option value="{{ $id }}" @selected(in_array($id, $selectedGrades, true))>{{ $name }}</option>
      @endforeach
    </select>
    <div class="form-text">Selecione um ou mais anos escolares.</div>
    @error('grade_level_ids') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
  </div>
</div>

<div class="card mb-3">
  <div class="card-header">Oficinas da Turma</div>
  <div class="card-body" id="workshop-list">
    @php
      $existingWorkshops = $existingWorkshops
        ?? old('workshops', [['id'=>'','max_students'=>'']]);
    @endphp

    @foreach($existingWorkshops as $i => $row)
      <div class="row g-2 mb-2 workshop-row">
        <div class="col-md-8">
          <select name="workshops[{{ $i }}][id]" class="form-select">
            <option value="">-- Selecione --</option>
            @foreach($workshops as $wid => $wname)
              <option value="{{ $wid }}" @selected(($row['id'] ?? null) == $wid)>{{ $wname }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <input type="number" min="0" name="workshops[{{ $i }}][max_students]"
                 class="form-control"
                 value="{{ $row['max_students'] ?? '' }}"
                 placeholder="Capacidade (vazio = sem limite)">
        </div>
      </div>
    @endforeach

    <button type="button" class="btn btn-sm btn-outline-secondary" id="add-workshop">Adicionar Oficina</button>
  </div>
</div>

@push('scripts')
<script>
(() => {
  const btn = document.getElementById('add-workshop');
  const list = document.getElementById('workshop-list');
  let i = {{ max(1, is_countable($existingWorkshops ?? []) ? count($existingWorkshops ?? []) : 1) }};
  btn?.addEventListener('click', () => {
    const tpl = document.querySelector('.workshop-row').cloneNode(true);
    tpl.querySelectorAll('select,input').forEach(el => {
      el.name = el.name.replace(/\[\d+\]/, `[${i}]`);
      if (el.tagName === 'SELECT') el.selectedIndex = 0;
      if (el.tagName === 'INPUT')  el.value = '';
    });
    list.insertBefore(tpl, btn);
    i++;
  });
})();
</script>
@endpush


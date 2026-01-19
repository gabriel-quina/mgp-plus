{{-- resources/views/classrooms/_form.blade.php --}}

{{-- Mensagens de sucesso/erro (padrão do projeto) --}}
@include('partials.messages')

<div class="card mb-3">
  <div class="card-header">Dados da Turma</div>
  <div class="card-body">
    @php
      $showSchoolSelect = $showSchoolSelect ?? true;
      $schoolInputValue = old('school_id', $fixedSchoolId ?? (isset($classroom) ? $classroom->school_id : null));
    @endphp
    @if($showSchoolSelect)
      <div class="mb-3">
        <label class="form-label">Escola</label>
        <select name="school_id" class="form-select @error('school_id') is-invalid @enderror" required>
          <option value="">-- Selecione --</option>
          @foreach($schools as $id => $name)
            <option value="{{ $id }}"
              @selected($schoolInputValue == $id)>{{ $name }}</option>
          @endforeach
        </select>
        @error('school_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>
    @else
      <input type="hidden" name="school_id" value="{{ $schoolInputValue }}">
      <div class="mb-3">
        <label class="form-label">Escola</label>
        <div class="form-control-plaintext">{{ $schoolName ?? ($schools[$schoolInputValue] ?? 'Escola selecionada') }}</div>
      </div>
    @endif

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Oficina</label>
        <select name="workshop_id" class="form-select @error('workshop_id') is-invalid @enderror" required>
          <option value="">-- Selecione --</option>
          @foreach($workshops as $id => $name)
            <option value="{{ $id }}" @selected(old('workshop_id', isset($classroom) ? $classroom->workshop_id : null) == $id)>{{ $name }}</option>
          @endforeach
        </select>
        @error('workshop_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
        <input type="number" name="academic_year_id"
               class="form-control @error('academic_year_id') is-invalid @enderror"
               value="{{ old('academic_year_id', isset($classroom) ? $classroom->academic_year_id : ($defaultYear ?? date('Y'))) }}"
               min="2000" max="2100" required>
        @error('academic_year_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>
    </div>

    <div class="row">
      <div class="col-md-3 mb-3">
        <label class="form-label">Grupo</label>
        <input type="number" name="group_number" min="1"
               class="form-control @error('group_number') is-invalid @enderror"
               value="{{ old('group_number', isset($classroom) ? $classroom->group_number : 1) }}" required>
        @error('group_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-md-3 mb-3">
        <label class="form-label">Capacidade sugerida</label>
        <input type="number" name="capacity_hint" min="1"
               class="form-control @error('capacity_hint') is-invalid @enderror"
               value="{{ old('capacity_hint', isset($classroom) ? $classroom->capacity_hint : '') }}">
        @error('capacity_hint') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-md-3 mb-3">
        <label class="form-label">Status</label>
        <input name="status" class="form-control @error('status') is-invalid @enderror"
               value="{{ old('status', isset($classroom) ? $classroom->status : 'active') }}" required>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>
    </div>
  </div>
</div>

<div class="card mb-3">
  <div class="card-header">Anos Atendidos</div>
  <div class="card-body">
    @php
      $selectedGrades = isset($selectedGrades)
          ? $selectedGrades
          : (array) old('grade_level_ids', isset($classroom) ? ($classroom->grade_level_ids ?? []) : []);
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


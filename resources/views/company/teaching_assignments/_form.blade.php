<div class="card">
  <div class="card-body">
    <div class="row g-3">

      <div class="col-12 col-md-6">
        <label for="school_id" class="form-label">Escola <span class="text-danger">*</span></label>
        <select id="school_id" name="school_id" class="form-select @error('school_id') is-invalid @enderror" required>
          <option value="">Selecione...</option>
          @foreach($schools as $school)
            @php
              $optVal = (string)$school->id;
              $selected = (string)old('school_id', $assignment->school_id ?? '') === $optVal ? 'selected' : '';
            @endphp
            <option value="{{ $school->id }}" {{ $selected }}>
              {{ $school->name }} — {{ $school->city?->name }}
            </option>
          @endforeach
        </select>
        @error('school_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-12 col-md-6">
        <label for="engagement_id" class="form-label">Vínculo (opcional)</label>
        <select id="engagement_id" name="engagement_id" class="form-select @error('engagement_id') is-invalid @enderror">
          <option value="">—</option>
          @foreach($engagements as $e)
            @php
              $label = match($e->engagement_type){
                'our_clt'       => 'CLT (nosso)',
                'our_pj'        => 'PJ (nosso)',
                'our_temporary' => 'Temporário (nosso)',
                'municipal'     => 'Municipal: ' . ($e->city?->name ?? '—'),
                default         => ucfirst($e->engagement_type),
              };
              $optVal = (string)$e->id;
              $selected = (string)old('engagement_id', $assignment->engagement_id ?? '') === $optVal ? 'selected' : '';
            @endphp
            <option value="{{ $e->id }}" {{ $selected }}>
              {{ $label }} — {{ $e->hours_per_week }}h/sem
            </option>
          @endforeach
        </select>
        @error('engagement_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-12 col-md-6">
        <label for="academic_year" class="form-label">Ano letivo <span class="text-danger">*</span></label>
        <input
          type="number"
          id="academic_year"
          name="academic_year"
          min="2000" max="2100" step="1"
          value="{{ old('academic_year', $assignment->academic_year ?? date('Y')) }}"
          class="form-control @error('academic_year') is-invalid @enderror"
          required
        >
        @error('academic_year') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-12 col-md-6">
        <label for="hours_per_week" class="form-label">Horas por semana</label>
        <input
          type="number"
          id="hours_per_week"
          name="hours_per_week"
          min="1" max="44" step="1"
          value="{{ old('hours_per_week', $assignment->hours_per_week ?? '') }}"
          class="form-control @error('hours_per_week') is-invalid @enderror"
        >
        @error('hours_per_week') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-12">
        <label for="notes" class="form-label">Observações</label>
        <textarea id="notes" name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $assignment->notes ?? '') }}</textarea>
        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

    </div>
  </div>

  <div class="card-footer d-flex justify-content-between">
    <a href="{{ route('admin.teachers.show', $teacher) }}" class="btn btn-outline-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel ?? 'Salvar' }}</button>
  </div>
</div>


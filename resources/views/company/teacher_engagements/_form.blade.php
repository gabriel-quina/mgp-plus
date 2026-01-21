<div class="card">
  <div class="card-body">
    <div class="row g-3">

      <div class="col-12 col-md-4">
        <label for="engagement_type" class="form-label">Tipo de vínculo <span class="text-danger">*</span></label>
        <select
          id="engagement_type"
          name="engagement_type"
          class="form-select @error('engagement_type') is-invalid @enderror"
          required
        >
          @php
            $type = old('engagement_type', $engagement->engagement_type ?? '');
          @endphp
          <option value="">Selecione...</option>
          <option value="our_clt" {{ $type === 'our_clt' ? 'selected' : '' }}>CLT (nossa empresa)</option>
          <option value="our_pj" {{ $type === 'our_pj' ? 'selected' : '' }}>PJ (nossa empresa)</option>
          <option value="our_temporary" {{ $type === 'our_temporary' ? 'selected' : '' }}>Temporário (nossa empresa)</option>
          <option value="municipal" {{ $type === 'municipal' ? 'selected' : '' }}>Municipal (prefeitura)</option>
        </select>
        @error('engagement_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-12 col-md-4">
        <label for="hours_per_week" class="form-label">Horas por semana <span class="text-danger">*</span></label>
        <input
          type="number"
          id="hours_per_week"
          name="hours_per_week"
          min="1" max="44" step="1"
          value="{{ old('hours_per_week', $engagement->hours_per_week ?? '') }}"
          class="form-control @error('hours_per_week') is-invalid @enderror"
          required
        >
        @error('hours_per_week') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-12 col-md-4">
        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
        @php $status = old('status', $engagement->status ?? 'active'); @endphp
        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
          <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Ativo</option>
          <option value="suspended" {{ $status === 'suspended' ? 'selected' : '' }}>Suspenso</option>
          <option value="ended" {{ $status === 'ended' ? 'selected' : '' }}>Encerrado</option>
        </select>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-12 col-md-6">
        <label for="start_date" class="form-label">Início</label>
        <input
          type="date"
          id="start_date"
          name="start_date"
          value="{{ old('start_date', optional($engagement->start_date ?? null)->format('Y-m-d')) }}"
          class="form-control @error('start_date') is-invalid @enderror"
        >
        @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-12 col-md-6">
        <label for="end_date" class="form-label">Término</label>
        <input
          type="date"
          id="end_date"
          name="end_date"
          value="{{ old('end_date', optional($engagement->end_date ?? null)->format('Y-m-d')) }}"
          class="form-control @error('end_date') is-invalid @enderror"
        >
        @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-12 col-md-6" id="city-wrapper">
        <label for="city_id" class="form-label">Cidade (apenas para municipal)</label>
        <select id="city_id" name="city_id" class="form-select @error('city_id') is-invalid @enderror">
          <option value="">—</option>
          @foreach($cities as $id => $name)
            <option value="{{ $id }}" {{ (string)old('city_id', $engagement->city_id ?? '') === (string)$id ? 'selected' : '' }}>
              {{ $name }}
            </option>
          @endforeach
        </select>
        @error('city_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-12">
        <label for="notes" class="form-label">Observações</label>
        <textarea id="notes" name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $engagement->notes ?? '') }}</textarea>
        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

    </div>
  </div>

  <div class="card-footer d-flex justify-content-between">
    <a href="{{ route('admin.teachers.show', $teacher) }}" class="btn btn-outline-secondary">Cancelar</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel ?? 'Salvar' }}</button>
  </div>

</div>

@push('scripts')
<script>
  (function() {
    const typeSel = document.getElementById('engagement_type');
    const cityWrap = document.getElementById('city-wrapper');
    function toggleCity() {
      const isMunicipal = typeSel && typeSel.value === 'municipal';
      if (cityWrap) {
        cityWrap.style.display = isMunicipal ? '' : 'none';
      }
    }
    toggleCity();
    typeSel && typeSel.addEventListener('change', toggleCity);
  })();
</script>
@endpush


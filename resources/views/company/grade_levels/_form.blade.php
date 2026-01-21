@php
  // helper pra pegar old() ou o valor do model
  $val = fn($k, $default = null) =>
      old($k, isset($gradeLevel) ? data_get($gradeLevel, $k, $default) : $default);
@endphp

<div class="row g-3">
  {{-- sequence (opcional / int 0-255) --}}
  <div class="col-12 col-sm-3">
    <label for="sequence" class="form-label">Sequência</label>
    <input
      type="number"
      name="sequence"
      id="sequence"
      class="form-control @error('sequence') is-invalid @enderror"
      value="{{ $val('sequence') }}"
      min="0" max="255" step="1" inputmode="numeric" pattern="[0-9]*">
    @error('sequence') <div class="invalid-feedback">{{ $message }}</div> @enderror
    <div class="form-text">Usado para ordenar (0–255). Deixe vazio se não quiser ordenar.</div>
  </div>

  {{-- name (obrigatório) --}}
  <div class="col-12 col-sm-5">
    <label for="name" class="form-label">Nome <span class="text-danger">*</span></label>
    <input
      type="text"
      name="name"
      id="name"
      class="form-control @error('name') is-invalid @enderror"
      value="{{ $val('name') }}"
      required maxlength="255"
      placeholder="ex.: 1º Ano, 2ª Série, Infantil II">
    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  {{-- short_name (opcional) --}}
  <div class="col-12 col-sm-4">
    <label for="short_name" class="form-label">Abreviação</label>
    <input
      type="text"
      name="short_name"
      id="short_name"
      class="form-control @error('short_name') is-invalid @enderror"
      value="{{ $val('short_name') }}"
      maxlength="50"
      placeholder="ex.: 1A, 2S, INF-II">
    @error('short_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  {{-- is_active (boolean) --}}
  <div class="col-12">
    {{-- Importante: inclua um hidden pra enviar 0 quando desmarcado --}}
    <input type="hidden" name="is_active" value="0">
    <div class="form-check">
      <input
        class="form-check-input @error('is_active') is-invalid @enderror"
        type="checkbox"
        id="is_active"
        name="is_active"
        value="1"
        @checked((bool) $val('is_active', true))>
      <label class="form-check-label" for="is_active">Ativo</label>
      @error('is_active') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>
  </div>

  <div class="col-12 d-flex gap-2">
    <button class="btn btn-primary" type="submit">{{ $submitLabel ?? 'Salvar' }}</button>
    <a href="{{ route('admin.grade-levels.index') }}" class="btn btn-outline-secondary">Cancelar</a>
  </div>
</div>


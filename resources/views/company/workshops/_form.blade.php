@php
  $val = fn($k, $d = null) => old($k, isset($workshop) ? data_get($workshop, $k, $d) : $d);
@endphp

<div class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Nome da oficina</label>
    <input type="text" name="name" class="form-control" value="{{ $val('name') }}" required>
  </div>
  <div class="col-md-3">
    <label class="form-label d-block">Ativa</label>
    <input type="hidden" name="is_active" value="0">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="is_active" value="1" id="wk_active"
             @checked((bool)$val('is_active', true))>
      <label for="wk_active" class="form-check-label">Oferecida atualmente</label>
    </div>
  </div>

  <div class="col-12">
    <label class="form-label">Descrição</label>
    <textarea name="description" class="form-control" rows="3">{{ $val('description') }}</textarea>
  </div>

  <div class="col-12 d-flex gap-2">
    <button class="btn btn-primary" type="submit">{{ $submitLabel ?? 'Salvar' }}</button>
    <a href="{{ route('admin.workshops.index') }}" class="btn btn-outline-secondary">Cancelar</a>
  </div>
</div>


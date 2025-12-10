@php
  $val = fn($k, $d = null) => old($k, isset($school) ? data_get($school, $k, $d) : $d);
@endphp

<div class="row g-3">

  <div class="col-md-8">
    <label class="form-label">Nome da escola</label>
    <input type="text" name="name" class="form-control" value="{{ $val('name') }}" required>
  </div>

  <div class="col-md-4">
    <label class="form-label">Cidade</label>
    <select name="city_id" class="form-select" required>
      <option value="">Selecione…</option>
      @foreach($cities as $id => $name)
        <option value="{{ $id }}" @selected($val('city_id') == $id)>{{ $name }}</option>
      @endforeach
    </select>
  </div>

  <div class="col-md-6">
    <label class="form-label">Logradouro</label>
    <input type="text" name="street" class="form-control" value="{{ $val('street') }}">
  </div>

  <div class="col-md-2">
    <label class="form-label">Número</label>
    <input type="text" name="number" class="form-control" value="{{ $val('number') }}">
  </div>

  <div class="col-md-4">
    <label class="form-label">Bairro</label>
    <input type="text" name="neighborhood" class="form-control" value="{{ $val('neighborhood') }}">
  </div>

  <div class="col-md-8">
    <label class="form-label">Complemento</label>
    <input type="text" name="complement" class="form-control" value="{{ $val('complement') }}">
  </div>

  <div class="col-md-4">
    <label class="form-label">CEP</label>
    <input type="text" name="cep" class="form-control" placeholder="12345-678" value="{{ $val('cep') }}">
  </div>

    @if (isset($workshops) && count($workshops))
      @php
        $selectedWorkshops = collect(old('workshop_ids', isset($school) ? $school->workshops->pluck('id')->all() : []))
          ->map(fn($v)=>(int)$v)->all();
      @endphp

      <div class="col-12">
        <fieldset class="border rounded p-3">
          <legend class="float-none w-auto px-2 small mb-0">Oficinas oferecidas pela escola</legend>
          <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-2">
            @foreach($workshops as $wid => $wname)
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input"
                         type="checkbox"
                         id="wk_{{ $wid }}"
                         name="workshop_ids[]"
                         value="{{ $wid }}"
                         @checked(in_array($wid, $selectedWorkshops, true))>
                  <label class="form-check-label" for="wk_{{ $wid }}">{{ $wname }}</label>
                </div>
              </div>
            @endforeach
          </div>
          <div class="form-text">Marque as oficinas que esta escola oferece.</div>
        </fieldset>
      </div>
    @endif

  <div class="col-12 d-flex gap-2">
    <button class="btn btn-primary" type="submit">{{ $submitLabel ?? 'Salvar' }}</button>
    <a href="{{ route('schools.index') }}" class="btn btn-outline-secondary">Cancelar</a>
  </div>
</div>


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
            @foreach ($cities as $id => $name)
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

    <div class="col-12 d-flex gap-2">
        <button class="btn btn-primary" type="submit">{{ $submitLabel ?? 'Salvar' }}</button>
        <a href="{{ route('admin.schools.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</div>

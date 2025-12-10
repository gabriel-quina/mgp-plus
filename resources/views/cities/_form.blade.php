{{-- Parcial de formulário de Cidades --}}
<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label">Nome da Cidade</label>
        <input
            type="text"
            name="name"
            id="name"
            class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $city->name ?? '') }}"
            required
        >
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="state_id" class="form-label">Estado</label>
        <select
            name="state_id"
            id="state_id"
            class="form-select @error('state_id') is-invalid @enderror"
            required
        >
            <option value="">-- Selecione um estado --</option>
            @foreach($states as $id => $name)
                <option value="{{ $id }}" {{ (string)old('state_id', $city->state_id ?? '') === (string)$id ? 'selected' : '' }}>
                    {{ $name }}
                </option>
            @endforeach
        </select>
        @error('state_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>


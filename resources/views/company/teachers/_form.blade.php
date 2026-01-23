@php
    /** @var \App\Models\Teacher|null $teacher */
    $isEdit = isset($teacher) && ($teacher->id ?? false);
@endphp

<div class="card">
    <div class="card-body">
        <div class="row g-3">

            {{-- Nome --}}
            <div class="col-12 col-md-6">
                <label for="name" class="form-label">
                    Nome <span class="text-danger">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $teacher->name ?? '') }}"
                    class="form-control @error('name') is-invalid @enderror" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- CPF --}}
            <div class="col-12 col-md-6">
                <label for="cpf" class="form-label">
                    CPF <span class="text-danger">*</span>
                </label>
                <input type="text" id="cpf" name="cpf" value="{{ old('cpf', $teacher->cpf ?? '') }}"
                    class="form-control @error('cpf') is-invalid @enderror" placeholder="Somente números"
                    inputmode="numeric" {{ $isEdit ? 'readonly' : 'required' }}>

                <div class="form-text">
                    @if (!$isEdit)
                        Senha inicial do acesso: <strong>6 primeiros dígitos do CPF</strong>.
                    @else
                        CPF não pode ser alterado após a criação.
                    @endif
                </div>

                @error('cpf')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- E-mail --}}
            <div class="col-12 col-md-6">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" id="email" name="email" value="{{ old('email', $teacher->email ?? '') }}"
                    class="form-control @error('email') is-invalid @enderror">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Nascimento --}}
            <div class="col-12 col-md-6">
                <label for="birthdate" class="form-label">Data de nascimento</label>
                <input type="date" id="birthdate" name="birthdate"
                    value="{{ old('birthdate', optional($teacher->birthdate ?? null)->format('Y-m-d')) }}"
                    class="form-control @error('birthdate') is-invalid @enderror">
                @error('birthdate')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Ativo --}}
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input @error('is_active') is-invalid @enderror" type="checkbox"
                        value="1" id="is_active" name="is_active" @checked(old('is_active', $isEdit ? (bool) ($teacher->is_active ?? true) : true))>
                    <label class="form-check-label" for="is_active">
                        Ativo
                    </label>
                    @error('is_active')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

        </div>
    </div>

    <div class="card-footer d-flex justify-content-between">
        <a href="{{ route('admin.teachers.index') }}" class="btn btn-outline-secondary">
            Cancelar
        </a>
        <button type="submit" class="btn btn-primary">
            {{ $submitLabel ?? ($isEdit ? 'Salvar alterações' : 'Salvar') }}
        </button>
    </div>
</div>

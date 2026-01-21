@extends('layouts.app')

@section('content')
  @include('partials.messages')

  <header class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="mb-0">Adicionar cidade — {{ $teacher->display_name }}</h3>
    <div class="d-flex gap-2">
      <a href="{{ route('admin.teachers.show', $teacher) }}" class="btn btn-outline-secondary">Voltar</a>
    </div>
  </header>

  <form method="POST" action="{{ route('admin.teacher-city-access.store', $teacher) }}">
    @csrf

    <div class="card">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-12 col-md-6">
            <label for="city_id" class="form-label">Cidade <span class="text-danger">*</span></label>
            <select id="city_id" name="city_id" class="form-select @error('city_id') is-invalid @enderror" required>
              <option value="">Selecione...</option>
              @foreach($cities as $id => $name)
                <option value="{{ $id }}" {{ (string)old('city_id') === (string)$id ? 'selected' : '' }}>
                  {{ $name }}
                </option>
              @endforeach
            </select>
            @error('city_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>
      </div>

      <div class="card-footer d-flex justify-content-between">
        <a href="{{ route('admin.teachers.show', $teacher) }}" class="btn btn-outline-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Salvar</button>
      </div>
    </div>
  </form>
@endsection


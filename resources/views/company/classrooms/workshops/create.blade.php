@extends('layouts.app')

@section('title', 'Vincular oficina — '.$classroom->name)

@section('content')
  <h1 class="mb-3">Vincular oficina à turma: {{ $classroom->name }}</h1>

  @if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif

  <form method="POST" action="{{ route('classrooms.workshops.store', $classroom) }}" class="row g-3">
    @csrf
    <div class="col-12 col-md-6">
      <label class="form-label">Oficina <span class="text-danger">*</span></label>
      <select name="workshop_id" class="form-select @error('workshop_id') is-invalid @enderror" required>
        <option value="">Selecione…</option>
        @foreach($workshops as $id => $name)
          <option value="{{ $id }}" @selected(old('workshop_id')==$id)>{{ $name }}</option>
        @endforeach
      </select>
      @error('workshop_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12 col-md-3">
      <label class="form-label">Capacidade <span class="text-danger">*</span></label>
      <input type="number" name="max_students" class="form-control @error('max_students') is-invalid @enderror"
             value="{{ old('max_students') }}" min="1" max="9999" step="1" required>
      @error('max_students') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12 d-flex gap-2">
      <button class="btn btn-primary" type="submit">Vincular</button>
      <a href="{{ route('classrooms.show', $classroom) }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
  </form>
@endsection


@extends('layouts.app')

@section('title', 'Capacidade da oficina — '.$classroom->name)

@section('content')
  <h1 class="mb-3">Capacidade da oficina "{{ $workshop->name }}" na turma "{{ $classroom->name }}"</h1>

  @if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif

  <form method="POST" action="{{ route('classrooms.workshops.update', [$classroom, $workshop]) }}" class="row g-3">
    @csrf @method('PUT')
    <div class="col-12 col-md-3">
      <label class="form-label">Capacidade <span class="text-danger">*</span></label>
      <input type="number" name="max_students" class="form-control @error('max_students') is-invalid @enderror"
             value="{{ old('max_students', $pivot->max_students) }}" min="1" max="9999" step="1" required>
      @error('max_students') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12 d-flex gap-2">
      <button class="btn btn-primary" type="submit">Salvar</button>
      <a href="{{ route('classrooms.show', $classroom) }}" class="btn btn-outline-secondary">Voltar</a>
    </div>
  </form>
@endsection


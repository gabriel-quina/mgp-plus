@extends('layouts.app')

@section('title', 'Anos por oficina — '.$classroom->name)

@section('content')
  <h1 class="mb-3">Definir anos atendidos — Turma "{{ $classroom->name }}" × Oficina "{{ $workshop->name }}"</h1>

  @if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif

  <form method="POST" action="{{ route('classrooms.workshops.grades.update', [$classroom, $workshop]) }}">
    @csrf @method('PUT')

    <div class="card mb-3">
      <div class="card-body">
        <div class="row">
          @foreach($grades as $g)
            <div class="col-12 col-md-4">
              <div class="form-check">
                <input class="form-check-input" type="checkbox"
                       id="grade_{{ $g->id }}" name="grade_level_ids[]"
                       value="{{ $g->id }}" @checked(in_array($g->id, $selectedId))>
                <label class="form-check-label" for="grade_{{ $g->id }}">
                  {{ $g->sequence !== null ? ($g->sequence.'. ') : '' }}{{ $g->name }}
                </label>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    <div class="d-flex gap-2">
      <button class="btn btn-primary" type="submit">Salvar</button>
      <a href="{{ route('classrooms.show', $classroom) }}" class="btn btn-outline-secondary">Voltar</a>
    </div>
  </form>
@endsection


@extends('layouts.app')

@section('content')
<header class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="mb-0">Oficinas da Escola</h3>
        <small class="text-muted d-block">
            {{ $school->name }}
            @if($school->city)
                — {{ $school->city->name }} / {{ $school->city->state->uf ?? '' }}
            @endif
        </small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.schools.dashboard', $school) }}" class="btn btn-outline-secondary">Voltar</a>
    </div>
</header>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<form method="post" action="{{ route('admin.schools.workshops.update', $school->id) }}">
    @csrf

    <div class="card">
        <div class="card-body">
            <p class="mb-3">
                Marque as oficinas oferecidas por <strong>{{ $school->name }}</strong>.
            </p>

            <div class="row">
                @foreach($workshops as $w)
                    <div class="col-md-4 mb-2">
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="workshops[]"
                                id="workshop_{{ $w->id }}"
                                value="{{ $w->id }}"
                                {{ ($school->workshops && $school->workshops->contains($w->id)) ? 'checked' : '' }}
                            >
                            <label class="form-check-label" for="workshop_{{ $w->id }}">
                                {{ $w->name }}
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card-footer d-flex gap-2">
            <button type="submit" class="btn btn-primary">Salvar</button>
            <a href="{{ route('admin.schools.dashboard', $school) }}" class="btn btn-outline-secondary">Voltar</a>
        </div>
    </div>
</form>
@endsection

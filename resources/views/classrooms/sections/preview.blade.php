@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.messages')

        <div class="d-flex align-items-center justify-content-between mb-3">
            <h1 class="h5 mb-0">
                Distribuição — {{ $classroom->name }}
                <small class="text-muted">/ {{ $workshop->name }}</small>
            </h1>
            <div class="d-flex gap-2">
                <a href="{{ route('classrooms.show', $classroom) }}" class="btn btn-outline-secondary">Voltar</a>
                <form action="{{ route('classrooms.workshops.apply', [$classroom, $workshop]) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary">Aplicar distribuição</button>
                </form>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body d-flex flex-wrap gap-4">
                <div><strong>Capacidade por subturma:</strong> {{ $capacity ?: '—' }}</div>
                <div><strong>Total elegíveis:</strong> {{ $eligible->count() }}</div>
                @php
                    $n = $capacity > 0 ? (int) ceil($eligible->count() / $capacity) : 1;
                    $n = max($n, 1);
                @endphp
                <div><strong>Subturmas calculadas:</strong> {{ $n }}</div>
            </div>
        </div>

        <div class="row g-3">
            @foreach ($buckets as $i => $bucket)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <strong>Subturma #{{ $i + 1 }}</strong>
                            <span class="text-muted small ms-2">({{ count($bucket) }} alunos)</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Aluno</th>
                                            <th style="width:140px;">Ano/Série</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($bucket as $e)
                                            <tr>
                                                <td>{{ optional($e->student)->name ?? '—' }}</td>
                                                <td>{{ optional($e->gradeLevel)->short_name ?? (optional($e->gradeLevel)->name ?? '—') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-center text-muted py-3">Sem alunos</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer text-muted small">
                            Capacidade: {{ $capacity ?: '—' }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

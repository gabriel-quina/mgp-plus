@extends('layouts.app')

@section('title', 'Aulas')

@section('content')
    <div class="container-xxl">

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Erro:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Cabeçalho --}}
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h1 class="h3 mb-1">Aulas</h1>
                <div class="text-muted small">
                    Escola: <strong>{{ $school->name }}</strong> ·
                    Turma: <strong>{{ $classroom->name }}</strong>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('schools.classrooms.show', [$school, $classroom]) }}"
                   class="btn btn-outline-secondary btn-sm">
                    Voltar para Turma
                </a>

                @if (!empty($canLaunch) && $canLaunch)
                    <a href="{{ route('schools.classrooms.lessons.create', [$school, $classroom]) }}"
                       class="btn btn-primary btn-sm">
                        Lançar aula
                    </a>
                @endif
            </div>
        </div>

        {{-- Card da listagem (fora do flex do cabeçalho) --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Lista de aulas</strong>
                <span class="text-muted small">Ordenado por data e horário de lançamento.</span>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 15%;">Data</th>
                            <th style="width: 20%;">Professor(a)</th>
                            <th>Conteúdo (tópico)</th>
                            <th style="width: 15%;" class="text-end">Presenças</th>
                            <th style="width: 15%;" class="text-end">Lançada em</th>
                            <th style="width: 10%;" class="text-end"></th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($lessons as $lesson)
                            <tr>
                                <td>{{ $lesson->taught_at?->format('d/m/Y') ?? '—' }}</td>
                                <td>{{ $lesson->teacher?->name ?? '—' }}</td>

                                <td class="text-truncate" style="max-width: 520px;">
                                    {{ $lesson->topic ?: '—' }}
                                </td>

                                <td class="text-end">{{ $lesson->attendances_count }}</td>
                                <td class="text-end">{{ $lesson->created_at?->format('d/m/Y H:i') ?? '—' }}</td>

                                <td class="text-end">
                                    <a href="{{ route('schools.classrooms.lessons.show', [$school, $classroom, $lesson]) }}"
                                       class="btn btn-sm btn-outline-secondary">
                                        Ver
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-muted text-center py-4">
                                    Nenhuma aula lançada ainda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($lessons->hasPages())
                <div class="card-footer">
                    {{ $lessons->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection


<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Matriz Curricular — {{ $school->name }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
  <h1 class="mb-4">Matriz Curricular — {{ $school->name }}</h1>

  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">
      @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
    </ul></div>
  @endif

  <div class="card mb-4">
    <div class="card-body">
      <form method="get" action="{{ route('admin.schools.curriculum.edit', $school->id) }}" class="row g-3">
        <div class="col-auto">
          <label for="period" class="col-form-label">Período letivo</label>
        </div>
        <div class="col-auto">
          <input type="text" name="period" id="period" value="{{ $period }}" class="form-control" placeholder="ex.: 2025.2">
        </div>
        <div class="col-auto">
          <button class="btn btn-outline-primary">Carregar</button>
        </div>
        <div class="col-auto">
          <a href="{{ route('admin.schools.index') }}" class="btn btn-link">Voltar às escolas</a>
        </div>
      </form>
    </div>
  </div>

  @if (!$period)
    <div class="alert alert-info">Informe o período letivo acima para configurar a matriz.</div>
  @else
    <form method="post" action="{{ route('admin.schools.curriculum.update', $school->id) }}">
      @csrf
      <input type="hidden" name="academic_period" value="{{ $period }}">

      <div class="card">
        <div class="card-header">
          Oficinas oferecidas neste período: <strong>{{ $offeredWorkshops->count() }}</strong>
        </div>
        <div class="card-body">
          @if ($offeredWorkshops->isEmpty())
            <p class="text-muted mb-0">
              Nenhuma oficina marcada para a escola. Primeiro acesse <em>Ações → Oficinas</em> da escola.
            </p>
          @else
            <div class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr>
                    <th style="width:220px">Ano escolar</th>
                    <th>Oficinas obrigatórias no período {{ $period }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($gradeLevels as $g)
                    @php
                      $checked = collect($current[$g->id] ?? []);
                    @endphp
                    <tr>
                      <td>
                        <div class="fw-semibold">{{ $g->name }}</div>
                        @if ($g->short_name)
                          <div class="text-muted small">{{ $g->short_name }}</div>
                        @endif
                      </td>
                      <td>
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-2">
                          @foreach ($offeredWorkshops as $w)
                            <div class="col">
                              <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="g{{ $g->id }}w{{ $w->id }}"
                                       name="grade_{{ $g->id }}_workshops[]"
                                       value="{{ $w->id }}"
                                       @checked($checked->contains($w->id))>
                                <label class="form-check-label" for="g{{ $g->id }}w{{ $w->id }}">
                                  {{ $w->name }}
                                  @if (!$w->is_active)
                                    <span class="badge bg-secondary">inativa</span>
                                  @endif
                                </label>
                              </div>
                            </div>
                          @endforeach
                        </div>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            <div class="text-end">
              <button class="btn btn-primary">Salvar matriz</button>
            </div>
          @endif
        </div>
      </div>
    </form>
  @endif
</body>
</html>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cidades</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; }
        th { background: #f3f4f6; text-align: left; }
    </style>
</head>
<body>
<h1>Cidades</h1>

<a href="{{ route('cities.create') }}">+ Nova Cidade</a>

@if (session('success'))
    <div style="color: green; margin: 10px 0;">{{ session('success') }}</div>
@endif

@if ($cities->isEmpty())
    <p>Nenhuma cidade encontrada.</p>
@else
    <table>
        <thead>
            <tr>
                <th>Cidade</th>
                <th>Estado</th>
                <th>UF</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cities as $city)
                <tr>
                    <td>{{ $city->name }}</td>
                    <td>{{ $city->state->name ?? '-' }}</td>
                    <td>{{ $city->state->uf ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
</body>
</html>


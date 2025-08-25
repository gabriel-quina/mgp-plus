<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Escolas</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; }
        th { background: #f3f4f6; text-align: left; }
    </style>
</head>
<body>
<h1>Escolas</h1>

<a href="{{ route('schools.create') }}">+ Nova Escola</a>

@if (session('success'))
    <div style="color: green; margin: 10px 0;">{{ session('success') }}</div>
@endif

@if ($schools->isEmpty())
    <p>Nenhuma escola encontrada.</p>
@else
    <table>
        <thead>
            <tr>
                <th>Escola</th>
                <th>Cidade</th>
                <th>UF</th>
                <th>CEP</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($schools as $school)
                <tr>
                    <td>{{ $school->name }}</td>
                    <td>{{ $school->city->name ?? '-' }}</td>
                    <td>{{ $school->city->state->uf ?? '-' }}</td>
                    <td>{{ $school->cep ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
</body>
</html>


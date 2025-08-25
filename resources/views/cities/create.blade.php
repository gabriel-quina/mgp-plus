<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Nova Cidade</title>
</head>
<body>
<h1>Nova Cidade</h1>

@if ($errors->any())
    <div style="color:#b91c1c;">
        <ul>
            @foreach ($errors->all() as $erro)
                <li>{{ $erro }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('cities.store') }}" method="POST">
    @csrf

    <label>Nome da Cidade</label><br>
    <input type="text" name="name" value="{{ old('name') }}" required><br><br>

    <label>Estado (UF)</label><br>
    <select name="state_id" required>
        <option value="">-- Selecione --</option>
        @foreach ($states as $state)
            <option value="{{ $state->id }}" @selected(old('state_id') == $state->id)>
                {{ $state->name }} ({{ $state->uf }})
            </option>
        @endforeach
    </select><br><br>

    <button type="submit">Salvar</button>
</form>

<br>
<a href="{{ route('cities.index') }}">← Voltar para a lista</a>
</body>
</html>


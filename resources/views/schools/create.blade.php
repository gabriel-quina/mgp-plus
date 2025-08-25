<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Nova Escola</title>
</head>
<body>
<h1>Nova Escola</h1>

@if ($errors->any())
    <div style="color:#b91c1c;">
        <ul>
            @foreach ($errors->all() as $erro)
                <li>{{ $erro }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('schools.store') }}" method="POST">
    @csrf

    <label>Nome da Escola</label><br>
    <input type="text" name="name" value="{{ old('name') }}" required><br><br>

    <label>Cidade</label><br>
    <select name="city_id" required>
        <option value="">-- Selecione --</option>
        @foreach ($cities as $city)
            <option value="{{ $city->id }}" @selected(old('city_id') == $city->id)>
                {{ $city->name }} ({{ $city->state->uf ?? '' }})
            </option>
        @endforeach
    </select><br><br>

    <label>CEP</label><br>
    <input type="text" name="cep" value="{{ old('cep') }}" placeholder="Somente números" maxlength="8"><br><br>

    <button type="submit">Salvar</button>
</form>

<br>
<a href="{{ route('schools.index') }}">← Voltar para a lista</a>
</body>
</html>


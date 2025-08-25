<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Novo Aluno</title>
</head>
<body>
    <h1>Novo Aluno</h1>

    @if ($errors->any())
        <div style="color: #b91c1c;">
            <ul>
                @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('students.store') }}" method="POST">
        @csrf

        <label>Nome</label><br>
        <input type="text" name="name" value="{{ old('name') }}" required><br><br>

        <label>CPF</label><br>
        <input type="text" name="cpf" value="{{ old('cpf') }}"><br><br>

        <label>E-mail</label><br>
        <input type="email" name="email" value="{{ old('email') }}"><br><br>

        <label>Data de Nascimento</label><br>
        <input type="date" name="birthdate" value="{{ old('birthdate') }}"><br><br>

        <label>Escola</label><br>
        <select name="school_id" required>
            <option value="">-- Selecione --</option>
            @foreach ($schools as $school)
                <option value="{{ $school->id }}" @selected(old('school_id') == $school->id)>
                    {{ $school->name }}
                </option>
            @endforeach
        </select><br><br>

        <button type="submit">Salvar</button>
    </form>

    <br>
    <a href="{{ route('students.index') }}">← Voltar para a lista</a>
</body>
</html>


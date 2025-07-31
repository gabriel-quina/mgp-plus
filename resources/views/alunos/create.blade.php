<!DOCTYPE html>
<html>
<head>
    <title>Cadastrar Aluno</title>
    <meta charset="UTF-8">
</head>
<body>
    <h1>Cadastrar Novo Aluno</h1>

    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('alunos.store') }}" method="POST">
        @csrf

        <label>Nome:</label><br>
        <input type="text" name="nome" value="{{ old('nome') }}"><br><br>

        <label>CPF:</label><br>
        <input type="text" name="cpf" value="{{ old('cpf') }}"><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" value="{{ old('email') }}"><br><br>

        <label>Matrícula:</label><br>
        <input type="text" name="matricula" value="{{ old('matricula') }}"><br><br>

        <label>Ano Escolar:</label><br>
        <input type="text" name="ano_escolar" value="{{ old('ano_escolar') }}"><br><br>

        <button type="submit">Cadastrar</button>
    </form>

    <br>
    <a href="{{ route('alunos.index') }}">← Voltar para lista</a>
</body>
</html>


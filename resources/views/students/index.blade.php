<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Alunos</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; }
        th { background: #f3f4f6; text-align: left; }
    </style>
</head>
<body>
    <h1>Alunos</h1>

    <a href="{{ route('students.create') }}">+ Novo Aluno</a>

    @if ($students->isEmpty())
        <p>Nenhum aluno encontrado.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>E-mail</th>
                    <th>Nascimento</th>
                    <th>Escola</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($students as $student)
                    <tr>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->cpf }}</td>
                        <td>{{ $student->email }}</td>
                        <td>{{ optional($student->birthdate)->format('d/m/Y') }}</td>
                        <td>{{ $student->school->name ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>


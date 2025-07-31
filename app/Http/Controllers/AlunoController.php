<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use Illuminate\Http\Request;

class AlunoController extends Controller
{
    public function index()
    {
        $alunos = Aluno::all();
        return view('alunos.index', compact('alunos'));
    }
    public function create()
    {
        return view('alunos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome'  =>  'required|string|max:255',
            'cpf'   =>  'required|string|max:14|unique:alunos,cpf',
            'email' =>  'required|email|unique:alunos,email',
            'matricula' =>  'required|string|unique:alunos,matricula',
            'ano_escolar'   =>  'required|string|max:50',
        ]);

        Aluno::create($validated);

        return redirect()->route('alunos.index')->with('success', 'Aluno cadastrado com sucesso!');
    }
}

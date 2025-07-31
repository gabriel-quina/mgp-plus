<?php

namespace App\Models;
use App\Models\Abstract\Pessoa;

class Aluno extends Pessoa
{
    protected $table = 'alunos';

    protected $fillable = [
        'nome',
        'cpf',
        'email',
        'matricula',
        'ano_escolar',
    ];
}

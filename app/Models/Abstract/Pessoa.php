<?php

namespace App\Models\Abstract;

use Illuminate\Database\Eloquent\Model;

abstract class Pessoa extends Model
{
    protected $fillable = ['nome', 'cpf', 'email'];

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getCpf(): string
    {
        return $this->cpf;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}


<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Aluno;

class AlunoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Aluno::create([
            'nome' => 'João Silva',
            'cpf'  => '12345678900',
            'email' => 'joao.silva@example.com',
            'matricula' => '20250001',
            'ano_escolar' => '9º Ano',
        ]);
    }
}

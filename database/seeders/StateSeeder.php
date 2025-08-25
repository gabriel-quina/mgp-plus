<?php

namespace Database\Seeders;

use App\Models\State;
use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $rows = [
            ['name' => 'Acre',                'uf' => 'AC', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Alagoas',             'uf' => 'AL', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Amapá',               'uf' => 'AP', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Amazonas',            'uf' => 'AM', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Bahia',               'uf' => 'BA', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Ceará',               'uf' => 'CE', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Distrito Federal',    'uf' => 'DF', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Espírito Santo',      'uf' => 'ES', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Goiás',               'uf' => 'GO', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Maranhão',            'uf' => 'MA', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Mato Grosso',         'uf' => 'MT', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Mato Grosso do Sul',  'uf' => 'MS', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Minas Gerais',        'uf' => 'MG', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Pará',                'uf' => 'PA', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Paraíba',             'uf' => 'PB', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Paraná',              'uf' => 'PR', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Pernambuco',          'uf' => 'PE', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Piauí',               'uf' => 'PI', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Rio de Janeiro',      'uf' => 'RJ', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Rio Grande do Norte', 'uf' => 'RN', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Rio Grande do Sul',   'uf' => 'RS', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Rondônia',            'uf' => 'RO', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Roraima',             'uf' => 'RR', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Santa Catarina',      'uf' => 'SC', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'São Paulo',           'uf' => 'SP', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Sergipe',             'uf' => 'SE', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Tocantins',           'uf' => 'TO', 'created_at' => $now, 'updated_at' => $now],
        ];

        // evita duplicar ao rodar mais de uma vez; usa a 'uf' (única) como chave
        State::upsert($rows, ['uf'], ['name', 'updated_at']);
    }
}


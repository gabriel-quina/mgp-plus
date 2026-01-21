<?php

namespace Database\Seeders\Deployment;

use App\Models\GradeLevel;
use Illuminate\Database\Seeder;

class GradeLevelSeeder extends Seeder
{
    public function run(): void
    {
        $keep = [
            'Maternal I', 'Maternal II', 'Infantil I (4 anos)', 'Infantil II (5 anos)',
            '1º Ano (EF)', '2º Ano (EF)', '3º Ano (EF)', '4º Ano (EF)', '5º Ano (EF)',
            '6º Ano (EF)', '7º Ano (EF)', '8º Ano (EF)', '9º Ano (EF)',
        ];

        $levels = [
            ['sequence' => 1,  'name' => 'Maternal I',           'short_name' => 'EI-MI',  'is_active' => true],
            ['sequence' => 2,  'name' => 'Maternal II',          'short_name' => 'EI-MII', 'is_active' => true],
            ['sequence' => 3,  'name' => 'Infantil I (4 anos)',  'short_name' => 'EI-I',   'is_active' => true],
            ['sequence' => 4,  'name' => 'Infantil II (5 anos)', 'short_name' => 'EI-II',  'is_active' => true],

            ['sequence' => 5, 'name' => '1º Ano (EF)',          'short_name' => 'EF1',    'is_active' => true],
            ['sequence' => 6, 'name' => '2º Ano (EF)',          'short_name' => 'EF2',    'is_active' => true],
            ['sequence' => 7, 'name' => '3º Ano (EF)',          'short_name' => 'EF3',    'is_active' => true],
            ['sequence' => 8, 'name' => '4º Ano (EF)',          'short_name' => 'EF4',    'is_active' => true],
            ['sequence' => 9, 'name' => '5º Ano (EF)',          'short_name' => 'EF5',    'is_active' => true],

            ['sequence' => 10, 'name' => '6º Ano (EF)',          'short_name' => 'EF6',    'is_active' => true],
            ['sequence' => 11, 'name' => '7º Ano (EF)',          'short_name' => 'EF7',    'is_active' => true],
            ['sequence' => 12, 'name' => '8º Ano (EF)',          'short_name' => 'EF8',    'is_active' => true],
            ['sequence' => 13, 'name' => '9º Ano (EF)',          'short_name' => 'EF9',    'is_active' => true],
        ];

        foreach ($levels as $data) {
            GradeLevel::updateOrCreate(['name' => $data['name']], $data);
        }

        // Mantém o catálogo “canônico” ativo e desativa os demais
        GradeLevel::whereNotIn('name', $keep)->update(['is_active' => false]);
    }
}


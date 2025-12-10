<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\School;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        // Pelo menos 1 por cidade; em UMA cidade de MG (Juiz de Fora) teremos 3 escolas
        $rows = [
            // MG — Juiz de Fora (3 escolas)
            ['city' => 'Juiz de Fora', 'name' => 'EM Santa Helena',         'street' => null, 'number' => null, 'neighborhood' => 'Santa Helena', 'cep' => null],
            ['city' => 'Juiz de Fora', 'name' => 'EM Doutor João Penido',   'street' => null, 'number' => null, 'neighborhood' => 'Centro',       'cep' => null],
            ['city' => 'Juiz de Fora', 'name' => 'EM Mariano Procópio',     'street' => null, 'number' => null, 'neighborhood' => 'Mariano',      'cep' => null],

            // MG — Ubá (1)
            ['city' => 'Ubá',          'name' => 'EM Professora Maria Silva','street' => null, 'number' => null, 'neighborhood' => 'Centro',       'cep' => null],

            // MG — Barbacena (1)
            ['city' => 'Barbacena',    'name' => 'EM José Bonifácio',       'street' => null, 'number' => null, 'neighborhood' => 'São José',     'cep' => null],

            // RJ — Três Rios (1)
            ['city' => 'Três Rios',    'name' => 'EM Padre Anchieta',       'street' => null, 'number' => null, 'neighborhood' => 'Centro',       'cep' => null],

            // SP — São Paulo (1)
            ['city' => 'São Paulo',    'name' => 'EM Parque do Carmo',      'street' => null, 'number' => null, 'neighborhood' => 'Itaquera',     'cep' => null],
        ];

        foreach ($rows as $r) {
            $cityId = City::where('name', $r['city'])->value('id');
            if (!$cityId) continue;

            School::firstOrCreate(
                ['city_id' => $cityId, 'name' => $r['name']],
                [
                    'street'       => $r['street'],
                    'number'       => $r['number'],
                    'neighborhood' => $r['neighborhood'],
                    'complement'   => null,
                    'cep'          => $r['cep'],
                ]
            );
        }
    }
}


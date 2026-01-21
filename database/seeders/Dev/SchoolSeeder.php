<?php

namespace Database\Seeders\Dev;

use App\Models\City;
use App\Models\School;
use App\Models\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // MG — Juiz de Fora (3 escolas)
            ['uf' => 'MG', 'city' => 'Juiz de Fora', 'name' => 'EM Santa Helena',           'neighborhood' => 'Santa Helena'],
            ['uf' => 'MG', 'city' => 'Juiz de Fora', 'name' => 'EM Doutor João Penido',     'neighborhood' => 'Centro'],
            ['uf' => 'MG', 'city' => 'Juiz de Fora', 'name' => 'EM Mariano Procópio',       'neighborhood' => 'Mariano'],

            // MG — Ubá (1)
            ['uf' => 'MG', 'city' => 'Ubá',          'name' => 'EM Professora Maria Silva', 'neighborhood' => 'Centro'],

            // MG — Barbacena (1)
            ['uf' => 'MG', 'city' => 'Barbacena',    'name' => 'EM José Bonifácio',         'neighborhood' => 'São José'],

            // RJ — Três Rios (1)
            ['uf' => 'RJ', 'city' => 'Três Rios',    'name' => 'EM Padre Anchieta',         'neighborhood' => 'Centro'],

            // SP — São Paulo (1)
            ['uf' => 'SP', 'city' => 'São Paulo',    'name' => 'EM Parque do Carmo',        'neighborhood' => 'Itaquera'],
        ];

        $hasCityStateId = Schema::hasColumn('cities', 'state_id');

        foreach ($rows as $r) {
            $stateId = null;

            if ($hasCityStateId) {
                $stateId = State::where('uf', $r['uf'])->value('id');
                if (!$stateId) {
                    // sem estado, não dá pra criar cidade se state_id for obrigatório
                    continue;
                }
            }

            // Cria ou encontra a cidade
            if ($hasCityStateId) {
                $city = City::firstOrCreate(
                    ['state_id' => $stateId, 'name' => $r['city']],
                    ['name' => $r['city'], 'state_id' => $stateId]
                );
            } else {
                // caso raro: tabela cities sem state_id
                $city = City::firstOrCreate(['name' => $r['city']], ['name' => $r['city']]);
            }

            School::firstOrCreate(
                ['city_id' => $city->id, 'name' => $r['name']],
                [
                    'street'       => null,
                    'number'       => null,
                    'neighborhood' => $r['neighborhood'],
                    'complement'   => null,
                    'cep'          => null,
                ]
            );
        }
    }
}


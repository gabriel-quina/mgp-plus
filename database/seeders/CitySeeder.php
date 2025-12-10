<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\State;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        // Exatamente 5 cidades conforme pedido:
        // 3 em MG (região de Juiz de Fora), 1 no RJ e 1 em SP
        $byUf = [
            'MG' => ['Juiz de Fora', 'Ubá', 'Barbacena'],
            'RJ' => ['Três Rios'],
            'SP' => ['São Paulo'],
        ];

        foreach ($byUf as $uf => $cities) {
            $stateId = State::where('uf', $uf)->value('id');
            foreach ($cities as $name) {
                City::firstOrCreate(['state_id' => $stateId, 'name' => $name]);
            }
        }
    }
}


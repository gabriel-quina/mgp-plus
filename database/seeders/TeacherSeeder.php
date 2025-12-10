<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        if (!class_exists(\App\Models\Teacher::class)) return;

        $Teacher = \App\Models\Teacher::class;

        // Descobre quantas cidades temos para definir a QUANTIDADE de professores
        // (3 para a cidade com mais escolas + 1 para cada cidade restante)
        $byCity = \App\Models\School::select('city_id')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('city_id')
            ->orderByDesc('total')
            ->get();

        if ($byCity->isEmpty()) return;

        $numCities   = $byCity->unique('city_id')->count();
        $targetTotal = 3 + max(0, $numCities - 1); // 3 + 1 por cidade restante

        // Garante que haja pelo menos esse total de professores (sem city_id, sem is_active)
        $first = ['Ana','Bruna','Carla','Daniel','Eduardo','Fernanda','Gustavo','Helena','Isabela','João','Larissa','Marcos','Natália','Paula','Rafael','Sofia','Tiago','Victor','Yara','Zeca'];
        $last  = ['Almeida','Barbosa','Cardoso','Dias','Esteves','Ferraz','Gomes','Henrique','Ibrahim','Junqueira','Lima','Macedo','Nascimento','Oliveira','Pereira','Queiroz','Ribeiro','Silva','Teixeira','Vieira'];

        $existing = $Teacher::count();
        $toCreate = max(0, $targetTotal - $existing);

        for ($i = 0; $i < $toCreate; $i++) {
            $name = $first[($existing + $i) % count($first)]
                  . ' '
                  . $last[(($existing + $i) * 7) % count($last)];

            $Teacher::firstOrCreate(['name' => $name]);
        }
    }
}


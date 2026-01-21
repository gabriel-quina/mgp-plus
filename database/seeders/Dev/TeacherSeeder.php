<?php

namespace Database\Seeders\Dev;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        if (!class_exists(\App\Models\Teacher::class)) {
            return;
        }

        $Teacher = \App\Models\Teacher::class;

        $byCity = \App\Models\School::select('city_id')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('city_id')
            ->orderByDesc('total')
            ->get();

        if ($byCity->isEmpty()) return;

        $numCities   = $byCity->unique('city_id')->count();
        $targetTotal = 3 + max(0, $numCities - 1);

        $hasEmail    = Schema::hasColumn('teachers', 'email');
        $hasIsActive = Schema::hasColumn('teachers', 'is_active');

        $first = ['Ana','Bruna','Carla','Daniel','Eduardo','Fernanda','Gustavo','Helena','Isabela','João','Larissa','Marcos','Natália','Paula','Rafael','Sofia','Tiago','Victor','Yara','Zeca'];
        $last  = ['Almeida','Barbosa','Cardoso','Dias','Esteves','Ferraz','Gomes','Henrique','Ibrahim','Junqueira','Lima','Macedo','Nascimento','Oliveira','Pereira','Queiroz','Ribeiro','Silva','Teixeira','Vieira'];

        $existing = $Teacher::count();
        $toCreate = max(0, $targetTotal - $existing);

        for ($i = 0; $i < $toCreate; $i++) {
            $name = $first[($existing + $i) % count($first)]
                  . ' '
                  . $last[(($existing + $i) * 7) % count($last)];

            $attrs = ['name' => $name];

            if ($hasEmail) {
                $slug = Str::slug($name, '.');
                $attrs['email'] = $slug . '@example.test';
            }
            if ($hasIsActive) {
                $attrs['is_active'] = true;
            }

            // Chave de idempotência: se tiver email, usa email; senão usa name
            if ($hasEmail) {
                $Teacher::firstOrCreate(['email' => $attrs['email']], $attrs);
            } else {
                $Teacher::firstOrCreate(['name' => $name], $attrs);
            }
        }
    }
}


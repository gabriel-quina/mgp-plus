<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Catálogos territoriais
            StateSeeder::class,
            CitySeeder::class,

            // Catálogo escolar
            GradeLevelSeeder::class,
            WorkshopSeeder::class,
            SchoolSeeder::class,

            // Pessoas
            StudentSeeder::class,
            TeacherSeeder::class,

            // 1) cria episódios (matrículas) — define quais anos existirão em cada escola
            StudentEnrollmentSeeder::class,

            RbacSeeder::class,
            MasterUserSeeder::class,
        ]);
    }
}

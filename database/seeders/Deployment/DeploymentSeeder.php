<?php

namespace Database\Seeders\Deployment;

use Illuminate\Database\Seeder;

class DeploymentSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            StateSeeder::class,
            CitySeeder::class,
            GradeLevelSeeder::class,
            WorkshopSeeder::class,
//            RbacSeeder::class,
            MasterUserSeeder::class,
        ]);
    }
}


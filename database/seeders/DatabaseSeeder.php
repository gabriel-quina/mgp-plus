<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Deployment\DeploymentSeeder;
use Database\Seeders\Dev\DevSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // sempre roda
        $this->call(DeploymentSeeder::class);

        // só roda se você ligar a flag
        if (app()->environment(['local', 'testing']) && env('SEED_DEV_DATA', false)) {
            $this->call(DevSeeder::class);
        }
    }
}

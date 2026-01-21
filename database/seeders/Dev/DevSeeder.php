<?php

namespace Database\Seeders\Dev;

use Illuminate\Database\Seeder;

class DevSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SchoolSeeder::class,
            StudentSeeder::class,
            TeacherSeeder::class,
            StudentEnrollmentSeeder::class,
            ClassroomSeeder::class,
        ]);
    }
}


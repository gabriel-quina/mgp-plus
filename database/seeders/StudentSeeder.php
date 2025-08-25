<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\School;
use App\Models\State;
use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $schools = School::query()->inRandomOrder()->get();

        if ($schools->isEmpty()) {
            $state = State::query()->first() ?? State::factory()->create(['name' => 'São Paulo', 'uf' => 'SP']);
            $city = City::factory()->create(['state_id' => $state->id]);
            $schools = School::factory()->count(3)->create(['city_id' => $city->id]);
        }

        Student::factory()
            ->count(100)
            ->recycle($schools)
            ->create();
    }
}


<?php

namespace Database\Factories;

use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;

class SchoolFactory extends Factory
{
    public function definition(): array
    {
        return [
            'city_id' => City::factory(),
            'name'    => $this->faker->company().' Escola',
            // 'cep'  => $this->faker->optional()->numerify('########'), // só se existir na migration
        ];
    }
}


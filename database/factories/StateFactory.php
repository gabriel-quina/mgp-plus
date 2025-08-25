<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StateFactory extends Factory
{
    public function definition(): array
    {
        // UF fake de 2 letras
        return [
            'name' => $this->faker->state(),
            'uf'   => strtoupper($this->faker->lexify('??')),
        ];
    }
}


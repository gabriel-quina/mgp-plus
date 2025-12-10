<?php

namespace Database\Factories;

use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\School>
 */
class SchoolFactory extends Factory
{
    public function definition(): array
    {
        $faker = $this->faker; // pt_BR se estiver APP_FAKER_LOCALE=pt_BR

        return [
            'city_id'      => City::inRandomOrder()->value('id') ?? City::factory(),
            'name'         => 'Escola Municipal ' . $faker->lastName(),

            'street'       => $faker->streetName(),
            'number'       => (string) $faker->buildingNumber(),
            'neighborhood' => $faker->citySuffix(),
            'complement'   => $faker->optional(0.3)->secondaryAddress(),
            // gravamos só dígitos; o accessor formata 12345-678
            'cep'          => preg_replace('/\D+/', '', $faker->postcode()),
        ];
    }
}


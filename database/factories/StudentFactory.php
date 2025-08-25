<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        $email = $this->faker->boolean(70) ? $this->faker->unique()->safeEmail() : null;

        return [
            'school_id' => School::factory(),
            'name'      => $this->faker->name(),
            'cpf'       => $this->faker->unique()->numerify('###########'),
            'email'     => $email,
            'birthdate' => $this->faker->dateTimeBetween('-22 years', '-8 years')->format('Y-m-d'),
        ];
    }
}


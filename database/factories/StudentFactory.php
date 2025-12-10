<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        // monta nome "puro": primeiro nome + sobrenome
        $first = $this->faker->firstName();   // sem títulos
        $last  = $this->faker->lastName();

        $name = trim("$first $last");

        // redundante por segurança: remove qualquer prefixo indesejado que venha do faker
        // (Sr, Sra, Dr, Dra, Prof, etc — com ou sem ponto, maiúsc/minúsc)
        $name = preg_replace(
            '/\b(Sr|Sra|Sr\.|Sra\.|Dr|Dra|Dr\.|Dra\.|Prof|Profa|Prof\.|Profa\.)\b\s*/iu',
            '',
            $name
        );

        return [
            'name' => $name,
            // o school_id será definido no seeder com ->for($school)
            // não setamos campos que não existem na tabela
        ];
    }
}


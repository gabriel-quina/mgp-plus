<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        // Gera um conjunto enxuto de alunos para testar a malha:
        // ~ 120 alunos no total (ajuste se quiser)
        $targetTotal = 120;

        $existing = Student::count();
        $toCreate = max(0, $targetTotal - $existing);

        if ($toCreate > 0) {
            Student::factory()->count($toCreate)->create();
        }
    }
}

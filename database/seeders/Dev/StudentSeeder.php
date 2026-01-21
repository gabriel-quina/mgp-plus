<?php

namespace Database\Seeders\Dev;

use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $targetTotal = 120;

        $existing = Student::count();
        $toCreate = max(0, $targetTotal - $existing);

        if ($toCreate > 0) {
            Student::factory()->count($toCreate)->create();
        }
    }
}


<?php

namespace Database\Factories;

use App\Models\GradeLevel;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentEnrollmentFactory extends Factory
{
    protected $model = StudentEnrollment::class;

    public function definition(): array
    {
        $year = (int) date('Y');

        return [
            'student_id' => Student::query()->inRandomOrder()->value('id') ?? Student::factory(),
            'school_id' => School::query()->inRandomOrder()->value('id') ?? School::factory(),
            'grade_level_id' => GradeLevel::query()->inRandomOrder()->value('id') ?? GradeLevel::factory(),
            'academic_year' => $year,
            'shift' => $this->faker->randomElement([
                StudentEnrollment::SHIFT_MORNING,
                StudentEnrollment::SHIFT_AFTERNOON,
                StudentEnrollment::SHIFT_EVENING,
            ]),
            'status' => StudentEnrollment::STATUS_ACTIVE,
            'transfer_scope' => StudentEnrollment::SCOPE_FIRST,
            'origin_school_id' => null,
            'started_at' => now()->startOfYear(),
            'ended_at' => null,
        ];
    }

    /* ---------- Atalhos de estado úteis nos testes ---------- */

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => StudentEnrollment::STATUS_ACTIVE,
            'ended_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => StudentEnrollment::STATUS_COMPLETED,
            'ended_at' => now()->endOfYear(),
        ]);
    }

    public function transferred(): static
    {
        return $this->state(fn () => [
            'status' => StudentEnrollment::STATUS_TRANSFERRED,
            'ended_at' => now()->subMonths(1),
            'transfer_scope' => StudentEnrollment::SCOPE_INTERNAL,
        ]);
    }

    public function forYear(int $year): static
    {
        return $this->state(fn () => ['academic_year' => $year]);
    }

    public function forShift(string $shift): static
    {
        return $this->state(fn () => ['shift' => $shift]);
    }

    public function forSchool(int $schoolId): static
    {
        return $this->state(fn () => ['school_id' => $schoolId]);
    }

    public function forLevel(int $gradeLevelId): static
    {
        return $this->state(fn () => ['grade_level_id' => $gradeLevelId]);
    }

    public function withOrigin(?int $originSchoolId): static
    {
        return $this->state(fn () => ['origin_school_id' => $originSchoolId]);
    }
}

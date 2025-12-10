<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentGrade extends Model
{
    protected $fillable = [
        'assessment_id',
        'student_enrollment_id',
        'score_points',
        'score_concept',
        'notes',
    ];

    protected $casts = [
        'score_points' => 'decimal:1',
    ];

    public const CONCEPTS = [
        'ruim',
        'regular',
        'bom',
        'muito_bom',
        'excelente',
    ];

    public static function conceptToPoints(?string $concept): ?float
    {
        return match ($concept) {
            'ruim' => 1.0,
            'regular' => 2.5,
            'bom' => 3.5,
            'muito_bom' => 4.5,
            'excelente' => 5.0,
            default => null,
        };
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(StudentEnrollment::class, 'student_enrollment_id');
    }
}

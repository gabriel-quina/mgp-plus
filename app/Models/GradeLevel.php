<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'short_name', 'sequence', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sequence' => 'integer',
    ];

    /**
     * Episódios de matrícula neste ano/série.
     * Obs.: se você preferir manter o nome antigo por compatibilidade,
     * pode criar um alias studentYears() chamando esta relação.
     */
    public function studentEnrollments()
    {
        return $this->hasMany(StudentEnrollment::class, 'grade_level_id');
    }

    // Turmas agora armazenam grade_level_ids em JSON (não há pivot).
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentGrade extends Model
{
    protected $fillable = [
        'assessment_id',
        'student_enrollment_id',
        'score_points',   // usado quando a avaliação for por pontos
        'score_concept',  // usado quando a avaliação for por conceito
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

    /**
     * Apenas ordem (1..5) para estatísticas/ordenação.
     * Não é "conversão em pontos".
     */
    public const CONCEPT_RANK = [
        'ruim' => 1,
        'regular' => 2,
        'bom' => 3,
        'muito_bom' => 4,
        'excelente' => 5,
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class, 'student_enrollment_id');
    }

    /**
     * Normaliza o conceito (ex.: "Muito Bom" -> "muito_bom")
     */
    public function setScoreConceptAttribute($value): void
    {
        $v = trim((string) $value);

        if ($v === '') {
            $this->attributes['score_concept'] = null;
            return;
        }

        $v = mb_strtolower($v);
        $v = str_replace([' ', '-'], '_', $v);

        $this->attributes['score_concept'] = $v;
    }

    /**
     * Retorna o rank 1..5 do conceito (ou null se inválido/nulo).
     */
    public function conceptRank(): ?int
    {
        $c = $this->score_concept ? trim((string) $this->score_concept) : null;
        if (!$c) return null;

        return self::CONCEPT_RANK[$c] ?? null;
    }
}


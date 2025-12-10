<?php

namespace App\Services;

use App\Models\Assessment;

class AssessmentStatsService
{
    /**
     * Calcula estatísticas para UMA avaliação.
     *
     * Retorna:
     * - 'numeric' => stats quando scale_type = 'points' (ou null)
     * - 'concept' => stats quando scale_type = 'concept' (ou null)
     */
    public function forAssessment(Assessment $assessment): array
    {
        // Garante que as notas estão carregadas
        $assessment->loadMissing('grades');

        $grades = $assessment->grades;

        $numeric = null;
        $concept = null;

        if ($assessment->scale_type === 'points') {
            $valid = $grades->filter(fn ($g) => $g->score_points !== null);

            if ($valid->count() > 0) {
                $numeric = [
                    'count' => $valid->count(),
                    'avg' => round($valid->avg('score_points'), 2),
                    'min' => $valid->min('score_points'),
                    'max' => $valid->max('score_points'),
                    'max_points' => $assessment->max_points,
                ];
            }
        } else {
            if ($grades->count() > 0) {
                $concept = [
                    'total' => $grades->count(),
                    'distribution' => $grades
                        ->groupBy('score_concept')
                        ->map(fn ($group) => $group->count())
                        ->sortKeys(),
                ];
            }
        }

        return [
            'numeric' => $numeric,
            'concept' => $concept,
        ];
    }
}

<?php

namespace App\Http\Controllers\Classrooms;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\WorkshopAllocation;

class ClassroomParentController extends Controller
{
    /**
     * Exibe a TURMA PAI.
     * - Lista TODOS os episódios elegíveis (derivados da turma PAI).
     * - Mostra cards das SUBTURMAS (children) com contadores.
     * - Estatísticas de alocação por oficina e por child.
     *
     * Rota sugerida: GET /turmas/{classroom}  -> name: classrooms.show
     */
    /**
     * Exibe a TURMA PAI.
     * - Lista TODOS os episódios elegíveis (derivados da turma PAI).
     * - Estatísticas de alocação por oficina e por child.
     *
     * Rota sugerida: GET /turmas/{classroom}  -> name: classrooms.show
     */
    public function show(Classroom $classroom)
    {
        // Garantia: só PAI aqui (se for child, 404)
        abort_if($classroom->parent_classroom_id, 404);

        // Load básico para a view
        $classroom->loadMissing('school', 'gradeLevels', 'workshops');

        // 1) TODOS os episódios elegíveis (derivados do PAI)
        $allEnrollments = method_exists($classroom, 'eligibleEnrollments')
            ? $classroom->eligibleEnrollments()->with(['student', 'gradeLevel'])->get()
            : collect();

        // 2) Children + alocações (para estatísticas e status das oficinas)
        $children = $classroom->children()
            ->with('workshops') // cada child deve pertencer a UMA oficina
            ->get();

        $childIds = $children->pluck('id')->all();

        // Todas as alocações nas children do PAI
        $allocations = empty($childIds)
            ? collect()
            : WorkshopAllocation::whereIn('child_classroom_id', $childIds)
                ->get(['workshop_id', 'student_enrollment_id', 'child_classroom_id']);

        // IDs já alocados em QUALQUER oficina (para alerta por aluno)
        $allocatedAnyIds = $allocations->pluck('student_enrollment_id')->unique()->values()->all();

        // Mapa: workshop_id => qtd alunos alocados nessa oficina (distinct por episódio)
        $allocatedPerWorkshop = $allocations
            ->groupBy('workshop_id')
            ->map(fn ($rows) => $rows->pluck('student_enrollment_id')->unique()->count());

        // Mapa: child_id => qtd alunos (distinct)
        $allocCountByChild = $allocations
            ->groupBy('child_classroom_id')
            ->map(fn ($rows) => $rows->pluck('student_enrollment_id')->unique()->count());

        // Ordena TODOS por nome (a aba Alunos do PAI exibe todos os elegíveis)
        $enrollments = $allEnrollments->sortBy(
            fn ($e) => mb_strtolower(optional($e->student)->name ?? '')
        );

        // Estatísticas básicas para a view
        $stats = [
            'total_all' => $allEnrollments->count(),
            'allocated_any_ids' => $allocatedAnyIds,
            'allocated_per_workshop' => $allocatedPerWorkshop,
            'alloc_count_by_child' => $allocCountByChild,
        ];

        // 3) Preparar resumo por OFICINA (para o card de "Oficinas da turma")
        $totalAll = $stats['total_all'];

        // Quais oficinas possuem pelo menos uma SUBTURMA associada?
        // (cada child->workshops deve ter 0 ou 1 workshop)
        $workshopIdsWithChildren = $children
            ->flatMap(fn ($child) => $child->workshops->pluck('id'))
            ->unique()
            ->values();

        // Coleção de resumos por oficina
        $workshopSummaries = $classroom->workshops->map(function ($wk) use (
            $totalAll,
            $allocatedPerWorkshop,
            $workshopIdsWithChildren
        ) {
            // IMPORTANTE: ajuste o nome do campo de capacidade se for diferente.
            // Exemplo: 'max_capacity', 'capacity', 'limit', etc.
            $limit = data_get($wk->pivot, 'max_students'); // <-- TROQUE AQUI SE PRECISAR
            $hasLimit = ! is_null($limit) && $limit > 0;

            // Quantos alunos já estão alocados em subturmas dessa oficina (quando se aplica)
            $allocated = (int) $allocatedPerWorkshop->get($wk->id, 0);

            $status = 'ok';              // "ok" | "warning" | "danger"
            $notAllocated = null;        // número de alunos ainda não alocados (quando fizer sentido)
            $showSubclassesButton = false;

            if ($hasLimit && $limit < $totalAll) {
                // Caso em que a oficina USA subturmas:
                // - tem limite
                // - a turma é maior que a capacidade
                $showSubclassesButton = true;

                $hasChildrenForThisWorkshop = $workshopIdsWithChildren->contains($wk->id);

                if (! $hasChildrenForThisWorkshop) {
                    // Tem limite e turma excede, mas nenhuma subturma configurada para essa oficina
                    $status = 'danger';
                    $notAllocated = $totalAll; // na prática, ninguém foi distribuído ainda
                } else {
                    // Já existem subturmas; verifica se ainda há alunos da turma PAI sem alocação
                    $notAllocated = max(0, $totalAll - $allocated);

                    if ($notAllocated > 0) {
                        $status = 'warning';
                    } else {
                        $status = 'ok';
                    }
                }
            } else {
                // Sem limite ou limite >= total de alunos:
                // comportamento igual às oficinas "sem capacidade máxima"
                $status = 'ok';
                $showSubclassesButton = false;
                $notAllocated = null;
            }

            return (object) [
                'id' => $wk->id,
                'name' => $wk->name,
                'limit' => $limit,
                'has_limit' => $hasLimit,
                'status' => $status,
                'not_allocated' => $notAllocated,
                'show_subclasses_button' => $showSubclassesButton,
            ];
        });

        return view('classrooms.show-parent', [
            'classroom' => $classroom,
            'enrollments' => $enrollments,
            'children' => $children,
            'stats' => $stats,
            'workshopSummaries' => $workshopSummaries,
        ]);
    }
}

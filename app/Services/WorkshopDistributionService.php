<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\StudentEnrollment;
use App\Models\Workshop;
use App\Models\WorkshopAllocation;
use Illuminate\Support\Facades\DB;

class WorkshopDistributionService
{
    /**
     * Gera a prévia de distribuição:
     * - eligible: episódios (StudentEnrollment) elegíveis para a turma PAI
     * - capacity: capacidade do workshop (pivot max_students)
     * - buckets: arranjo round-robin dos elegíveis em N baldes (N = ceil(qtd/capacidade))
     */
    public function preview(Classroom $parent, Workshop $workshop, int $tolerance = 0): array
    {
        [$eligible, $max] = $this->eligibleAndCapacity($parent, $workshop);

        $tolerance = max(0, $tolerance); // nunca negativo

        // capacidade efetiva = limite + tolerância
        if ($max > 0) {
            $effectiveMax = $max + $tolerance;
            $n = (int) ceil($eligible->count() / $effectiveMax);
        } else {
            $n = 1;
        }

        $n = max($n, 1);

        // round-robin nos buckets
        $buckets = array_fill(0, $n, []);
        $i = 0;
        foreach ($eligible as $enrollment) {
            $buckets[$i % $n][] = $enrollment; // StudentEnrollment
            $i++;
        }

        return [
            'eligible' => $eligible,
            'capacity' => $max,
            'tolerance' => $tolerance,
            'effective_capacity' => $max > 0 ? $max + $tolerance : null,
            'buckets' => $buckets,
        ];
    }

    /**
     * Aplica a distribuição:
     * - garante subturmas (children) suficientes para a oficina
     * - limpa alocações automáticas anteriores (preserva locks)
     * - distribui elegíveis round-robin respeitando locks e uniques
     */
    public function apply(Classroom $parent, Workshop $workshop): void
    {
        // [$eligible] = Collection<StudentEnrollment>
        // [$max]      = limite por subturma (pivot max_students)
        [$eligible, $max] = $this->eligibleAndCapacity($parent, $workshop);

        // Sem limite ou sem alunos -> nada a fazer (oficina é da turma inteira)
        if ($max <= 0 || $eligible->isEmpty()) {
            return;
        }

        // Quantidade de subturmas necessária, respeitando o limite
        $n = (int) ceil($eligible->count() / $max);
        $n = max($n, 1);

        DB::transaction(function () use ($parent, $workshop, $eligible, $n) {
            // 1) Criar/reutilizar N subturmas para esta oficina
            $children = $this->ensureChildren($parent, $workshop, $n);

            // 2) Limpar alocações automáticas anteriores desta oficina (preservar locks)
            $this->clearAutoAllocations($children, $workshop);

            // 3) Distribuir elegíveis round-robin, respeitando locks / uniques
            $i = 0;
            $childCount = count($children);

            if ($childCount === 0) {
                return;
            }

            foreach ($eligible as $enrollment) {
                $target = $children[$i % $childCount];
                $this->assignStudentToChild($enrollment, $target, $workshop);
                $i++;
            }
        });
    }

    /**
     * Obtém elegíveis (episódios ativos) e a capacidade do workshop no pivot da TURMA PAI.
     */
    private function eligibleAndCapacity(Classroom $parent, Workshop $workshop): array
    {
        // garantir gradeLevels carregado para possível sync nas children
        $parent->loadMissing('gradeLevels', 'workshops');

        // DERIVADO via episódios (StudentEnrollment) — já com overrides aplicados pelo helper
        $eligible = $parent->eligibleEnrollments()->get();

        // capacidade definida no pivot classroom<->workshop (max_students)
        $pivot = $parent->workshops()
            ->where('workshops.id', $workshop->id)
            ->first()?->pivot;

        $max = (int) ($pivot->max_students ?? 0);

        return [$eligible, $max];
    }

    /**
     * Garante N subturmas (children) para a oficina informada.
     * Reutiliza existentes (ordenadas por id) e cria as que faltarem.
     *
     * IMPORTANTE: para não violar UNIQUE(school_id, academic_year, shift, grade_level_key),
     * a child recebe um grade_level_key "derivado" do PAI com sufixo por workshop/index.
     */
    private function ensureChildren(Classroom $parent, Workshop $workshop, int $n): array
    {
        // children já vinculadas a ESTE workshop
        $existing = $parent->children()
            ->whereHas('workshops', fn ($q) => $q->where('workshops.id', $workshop->id))
            ->orderBy('id')
            ->get();

        $children = [];
        for ($i = 1; $i <= $n; $i++) {
            /** @var \App\Models\Classroom $child */
            if (isset($existing[$i - 1])) {
                $child = $existing[$i - 1];
            } else {
                // sufixo para não colidir com UNIQUE do PAI
                $baseKey = $parent->grade_level_key ?? 'GL';
                $childKey = $baseKey.'|ws:'.$workshop->id.'#'.$i;

                $child = $parent->children()->create([
                    'school_id' => $parent->school_id,
                    'name' => $parent->name.' - '.$workshop->name.' #'.$i,
                    'shift' => $parent->shift,
                    'is_active' => true,
                    'academic_year' => $parent->academic_year,
                    'grade_level_key' => $childKey, // <-- chave diferenciada para evitar UNIQUE
                ]);

                // sincroniza os mesmos anos do PAI (útil para filtros/relatórios)
                if ($parent->relationLoaded('gradeLevels')) {
                    $child->gradeLevels()->sync($parent->gradeLevels->pluck('id')->all());
                }
            }

            // garante vínculo da child com o mesmo workshop
            $child->workshops()->syncWithoutDetaching([$workshop->id]);

            $children[] = $child;
        }

        return $children;
    }

    /**
     * Remove alocações NÃO travadas (is_locked=false) para as children desta oficina.
     * Mantém as travadas (overrides).
     */
    private function clearAutoAllocations(array $children, Workshop $workshop): void
    {
        if (empty($children)) {
            return;
        }

        $childIds = array_map(fn ($c) => $c->id, $children);

        WorkshopAllocation::whereIn('child_classroom_id', $childIds)
            ->where('workshop_id', $workshop->id)
            ->where('is_locked', false)
            ->delete();
    }

    /**
     * Aloca (ou move) um episódio para a child target, respeitando locks e uniques:
     * - unique(child_classroom_id, workshop_id, student_enrollment_id)
     * - unique(workshop_id, student_enrollment_id)  (um aluno em apenas UMA child por oficina)
     */
    private function assignStudentToChild(StudentEnrollment $enrollment, Classroom $child, Workshop $workshop): void
    {
        // Garantia: child tem o workshop vinculado
        $child->workshops()->syncWithoutDetaching([$workshop->id]);

        // Existe alguma alocação deste aluno (episódio) nesta oficina — em qualquer child?
        $existing = WorkshopAllocation::where('workshop_id', $workshop->id)
            ->where('student_enrollment_id', $enrollment->id)
            ->first();

        if ($existing) {
            // Se já está na child destino, não faz nada
            if ((int) $existing->child_classroom_id === (int) $child->id) {
                return;
            }

            // Se está travada em outra child, respeita lock (não move)
            if ($existing->is_locked) {
                return;
            }

            // Não está travada: move para a child destino
            $existing->update([
                'child_classroom_id' => $child->id,
            ]);

            return;
        }

        // Não havia alocação prévia nesta oficina: cria nova
        WorkshopAllocation::create([
            'child_classroom_id' => $child->id,
            'workshop_id' => $workshop->id,
            'student_enrollment_id' => $enrollment->id,
            'is_locked' => false,
            'note' => null,
        ]);
    }
}

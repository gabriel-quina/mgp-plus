<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\StudentEnrollment;
use App\Models\Workshop;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassroomSeeder extends Seeder
{
    public function run(): void
    {
        $academicYear = (int) date('Y');

        // Descobre quais combinações (escola, turno) possuem matrículas ativas no ano
        $groups = StudentEnrollment::query()
            ->select('school_id', 'shift')
            ->where('academic_year', $academicYear)
            ->where('status', StudentEnrollment::STATUS_ACTIVE)
            ->whereNull('ended_at')
            ->groupBy('school_id', 'shift')
            ->get();

        if ($groups->isEmpty()) {
            return;
        }

        // Escola âncora: a que possui MAIS matrículas ativas neste ano
        $anchorSchoolId = StudentEnrollment::query()
            ->select('school_id', DB::raw('COUNT(*) as total'))
            ->where('academic_year', $academicYear)
            ->where('status', StudentEnrollment::STATUS_ACTIVE)
            ->whereNull('ended_at')
            ->groupBy('school_id')
            ->orderByDesc('total')
            ->value('school_id');

        // Workshops padrão (use nomes existentes na sua tabela)
        $ing = Workshop::where('name', 'Ingles')->first();
        $art = Workshop::where('name', 'Artes')->first();
        $esp = Workshop::where('name', 'Esporte')->first();

        foreach ($groups as $g) {
            $schoolId = $g->school_id;
            $shift = $g->shift;

            // Quais anos (grade_levels) existem com matrícula nessa escola/turno/ano?
            $levelIds = StudentEnrollment::query()
                ->where('school_id', $schoolId)
                ->where('academic_year', $academicYear)
                ->where('shift', $shift)
                ->where('status', StudentEnrollment::STATUS_ACTIVE)
                ->whereNull('ended_at')
                ->distinct()
                ->pluck('grade_level_id')
                ->values();

            if ($levelIds->isEmpty()) {
                continue;
            }

            // Ordena os levels por sequence para nomes mais bonitos
            $levels = GradeLevel::whereIn('id', $levelIds)->orderBy('sequence')->get();

            // ======== ESCOLA ÂNCORA: cria pelo menos UMA TURMA MISTA (2 níveis) ========
            $didMixed = false;
            if ($schoolId === (int) $anchorSchoolId && $levels->count() >= 2) {
                $firstTwo = $levels->take(2)->values();
                $name = sprintf('%s - Turma Mista (%s + %s, %s)',
                    School::find($schoolId)?->name ?? 'Escola',
                    $firstTwo[0]->short_name ?? $firstTwo[0]->name,
                    $firstTwo[1]->short_name ?? $firstTwo[1]->name,
                    ucfirst($shift)
                );

                $classroom = Classroom::updateOrCreate(
                    [
                        'school_id' => $schoolId,
                        'name' => $name,
                        'shift' => $shift,
                        'academic_year' => $academicYear,
                        'parent_classroom_id' => null,
                    ],
                    [
                        'is_active' => true,
                        'grade_level_key' => implode('+', $firstTwo->pluck('id')->all()),
                    ]
                );

                $classroom->gradeLevels()->sync($firstTwo->pluck('id')->all());

                // Workshops “padrão” (capacidade menor para Inglês pra forçar subturmas nos testes)
                if ($ing) {
                    $classroom->workshops()->syncWithoutDetaching([$ing->id => ['max_students' => 10]]);
                }
                if ($art) {
                    $classroom->workshops()->syncWithoutDetaching([$art->id => ['max_students' => 20]]);
                }
                if ($esp) {
                    $classroom->workshops()->syncWithoutDetaching([$esp->id => ['max_students' => 20]]);
                }

                $didMixed = true;

                // Remove os dois primeiros níveis da lista para criação de turmas simples abaixo
                $levelIds = $levelIds->diff($firstTwo->pluck('id'))->values();
            }

            // ======== Para TODOS os níveis restantes, cria TURMAS SIMPLES (1 nível) ========
            foreach ($levelIds as $idx => $levelId) {
                $gl = $levels->firstWhere('id', $levelId);

                $name = sprintf('%s - Turma %s (%s, %s)',
                    School::find($schoolId)?->name ?? 'Escola',
                    $this->letter($idx + 1, $didMixed), // B, C, D... se já criou mista
                    $gl?->short_name ?? $gl?->name ?? 'Série',
                    ucfirst($shift)
                );

                $classroom = Classroom::updateOrCreate(
                    [
                        'school_id' => $schoolId,
                        'name' => $name,
                        'shift' => $shift,
                        'academic_year' => $academicYear,
                        'parent_classroom_id' => null,
                    ],
                    [
                        'is_active' => true,
                        'grade_level_key' => (string) $levelId,
                    ]
                );

                $classroom->gradeLevels()->sync([$levelId]);

                // Workshops padrão
                if ($ing) {
                    $classroom->workshops()->syncWithoutDetaching([$ing->id => ['max_students' => 20]]);
                }
                if ($art) {
                    $classroom->workshops()->syncWithoutDetaching([$art->id => ['max_students' => 20]]);
                }
                if ($esp) {
                    $classroom->workshops()->syncWithoutDetaching([$esp->id => ['max_students' => 20]]);
                }
            }
        }
    }

    private function letter(int $n, bool $mixedAlready): string
    {
        // Se já criou a mista (A), a próxima turma simples vira B; senão A, B, C...
        $offset = $mixedAlready ? 1 : 0;

        return chr(65 + $n + $offset - 1);
    }
}

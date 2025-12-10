<?php

namespace Database\Seeders;

use App\Models\GradeLevel;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class StudentEnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $year = (int) date('Y');

        $schools = School::orderBy('id')->get();
        $levels = GradeLevel::orderBy('sequence')->get();
        $students = Student::orderBy('id')->get();

        if ($schools->isEmpty() || $levels->count() < 2 || $students->isEmpty()) {
            return;
        }

        // Definimos a ESCOLA ÂNCORA: a primeira (poderia ser a maior; para determinismo, usamos a primeira).
        $anchorSchool = $schools->first();

        // Dois anos para a escola âncora (garantia da regra "pelo menos dois anos escolares")
        $anchorLevels = $levels->take(2)->values(); // [GL1, GL2]
        $shiftAnchor = StudentEnrollment::SHIFT_MORNING;

        // Shifts para outras escolas (apenas para variar)
        $otherShifts = [
            StudentEnrollment::SHIFT_AFTERNOON,
            StudentEnrollment::SHIFT_EVENING,
            StudentEnrollment::SHIFT_MORNING,
        ];

        // Quantidade alvo por turma (aproximado, suficiente para testar distribuição/subturmas)
        $total = $students->count();
        $anchorTotal = min(60, max(24, (int) floor($total * 0.5))); // 24..60
        $perAnchorLevel = max(12, (int) floor($anchorTotal / 2));   // 12+ em cada ano na âncora

        // Particiona os alunos: metade na escola âncora (divididos entre GL1 e GL2), resto nas demais escolas
        $anchorSlice = $students->slice(0, $perAnchorLevel * 2)->values();
        $otherSlice = $students->slice($perAnchorLevel * 2)->values();

        // 1) Insere episódios na ESCOLA ÂNCORA divididos entre 2 anos (GL1/GL2), no mesmo shift
        $this->seedForSchoolDistributingLevels(
            students: $anchorSlice,
            schoolId: $anchorSchool->id,
            levelIds: $anchorLevels->pluck('id'),
            year: $year,
            shift: $shiftAnchor
        );

        // 2) Para as demais escolas, escolhemos 1 ano por escola e distribuímos os alunos restantes
        if ($otherSlice->isNotEmpty()) {
            $rotLevel = 2; // começa do 3º nível para variar
            $rotShift = 0;
            $idx = 0;

            foreach ($schools->where('id', '!=', $anchorSchool->id) as $school) {
                if ($idx >= $otherSlice->count()) {
                    break;
                }

                // escolhe 1 nível para esta escola (variação simples)
                $levelId = $levels[$rotLevel % $levels->count()]->id;
                $shift = $otherShifts[$rotShift % count($otherShifts)];

                // tamanho alvo por escola “não âncora”
                $target = min(28, max(14, (int) floor(($otherSlice->count()) / max(1, $schools->count() - 1))));

                $bucket = $otherSlice->slice($idx, $target)->values();
                $idx += $target;

                $this->seedForSchoolFixedLevel(
                    students: $bucket,
                    schoolId: $school->id,
                    levelId: $levelId,
                    year: $year,
                    shift: $shift
                );

                $rotLevel++;
                $rotShift++;
            }

            // sobras (se existirem) ficam na escola âncora, alternando entre os 2 anos
            if ($idx < $otherSlice->count()) {
                $leftover = $otherSlice->slice($idx)->values();
                $this->seedForSchoolDistributingLevels(
                    students: $leftover,
                    schoolId: $anchorSchool->id,
                    levelIds: $anchorLevels->pluck('id'),
                    year: $year,
                    shift: $shiftAnchor
                );
            }
        }
    }

    /**
     * Cria episódios alternando entre múltiplos anos (ex.: turma mista futuro).
     */
    private function seedForSchoolDistributingLevels(Collection $students, int $schoolId, Collection $levelIds, int $year, string $shift): void
    {
        if ($students->isEmpty() || $levelIds->isEmpty()) {
            return;
        }

        $nLevels = $levelIds->count();
        foreach ($students as $i => $student) {
            $levelId = $levelIds[$i % $nLevels];

            StudentEnrollment::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'academic_year' => $year,
                ],
                [
                    'student_id' => $student->id,
                    'school_id' => $schoolId,
                    'grade_level_id' => $levelId,
                    'academic_year' => $year,
                    'shift' => $shift,
                    'status' => StudentEnrollment::STATUS_ACTIVE,
                    'transfer_scope' => StudentEnrollment::SCOPE_FIRST,
                    'origin_school_id' => null,
                    'started_at' => now()->startOfYear(),
                    'ended_at' => null,
                ]
            );
        }
    }

    /**
     * Cria episódios fixando um único ano para a escola.
     */
    private function seedForSchoolFixedLevel(Collection $students, int $schoolId, int $levelId, int $year, string $shift): void
    {
        if ($students->isEmpty()) {
            return;
        }

        foreach ($students as $student) {
            StudentEnrollment::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'academic_year' => $year,
                ],
                [
                    'student_id' => $student->id,
                    'school_id' => $schoolId,
                    'grade_level_id' => $levelId,
                    'academic_year' => $year,
                    'shift' => $shift,
                    'status' => StudentEnrollment::STATUS_ACTIVE,
                    'transfer_scope' => StudentEnrollment::SCOPE_FIRST,
                    'origin_school_id' => null,
                    'started_at' => now()->startOfYear(),
                    'ended_at' => null,
                ]
            );
        }
    }
}

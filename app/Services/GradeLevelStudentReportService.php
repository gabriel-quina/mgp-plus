<?php

// app/Services/GradeLevelStudentReportService.php

namespace App\Services;

use App\Models\AssessmentGrade;
use App\Models\GradeLevel;
use App\Models\LessonAttendance;
use App\Models\School;
use App\Models\StudentEnrollment; // ajuste se seu model tiver outro nome
use Carbon\Carbon;
use Illuminate\Support\Collection;

class GradeLevelStudentReportService
{
    /**
     * Monta relatório de alunos de um ano escolar dentro de uma escola,
     * com média de notas (só avaliações em pontos) e frequência.
     */
    public function forSchoolAndGrade(
        School $school,
        GradeLevel $gradeLevel,
        ?Carbon $start = null,
        ?Carbon $end = null
    ): Collection {
        $currentAcademicYear = (int) now()->year;

        // 1) Matrículas dessa escola + ano escolar
        $enrollments = StudentEnrollment::with('student')
            ->where('school_id', $school->id)
            ->where('grade_level_id', $gradeLevel->id)
            ->where('academic_year', $currentAcademicYear)
            ->whereIn('status', [
                StudentEnrollment::STATUS_ENROLLED,
                StudentEnrollment::STATUS_ACTIVE,
            ])
            ->whereNull('ended_at')
            ->orderBy('student_id')
            ->orderByDesc('started_at')
            ->orderByDesc('id')
            ->get()
            ->unique('student_id')
            ->values();

        if ($enrollments->isEmpty()) {
            return collect();
        }

        $enrollmentIds = $enrollments->pluck('id');

        // 2) NOTAS – só avaliações scale_type = 'points'
        $gradesByEnrollment = AssessmentGrade::with('assessment')
            ->whereIn('student_enrollment_id', $enrollmentIds)
            ->whereHas('assessment', function ($q) use ($school, $start, $end) {
                $q->where('scale_type', 'points') // mesma lógica do AssessmentStatsService
                    ->whereHas('classroom', function ($qq) use ($school) {
                        $qq->where('school_id', $school->id);
                    });

                if ($start) {
                    $q->whereDate('due_at', '>=', $start);
                }
                if ($end) {
                    $q->whereDate('due_at', '<=', $end);
                }
            })
            ->get()
            ->groupBy('student_enrollment_id');

        // 3) FREQUÊNCIA
        // Assumindo que você grava um LessonAttendance por aluno/aula,
        // com campo booleano "present".
        $attendancesByEnrollment = LessonAttendance::with('lesson.classroom')
            ->whereIn('student_enrollment_id', $enrollmentIds)
            ->whereHas('lesson.classroom', function ($q) use ($school) {
                $q->where('school_id', $school->id);
            })
            ->when($start, function ($q) use ($start) {
                $q->whereDate('lesson_date' /* ou taught_at */, '>=', $start);
            })
            ->when($end, function ($q) use ($end) {
                $q->whereDate('lesson_date' /* ou taught_at */, '<=', $end);
            })
            ->get()
            ->groupBy('student_enrollment_id');

        $rows = collect();

        foreach ($enrollments as $enrollment) {
            $student = $enrollment->student;

            // --- Média de notas (pontos) ---
            $grades = $gradesByEnrollment->get($enrollment->id, collect())
                ->filter(fn ($g) => $g->score_points !== null);

            $avgPoints = $grades->isNotEmpty()
                ? round($grades->avg('score_points'), 2)
                : null;

            // --- Frequência ---
            $att = $attendancesByEnrollment->get($enrollment->id, collect());

            // Se você tiver um registro por aula + aluno, total de aulas = total de registros
            $totalLessons = $att->count();
            $presentCount = $att->where('present', true)->count();

            $freqPct = $totalLessons > 0
                ? round(($presentCount / $totalLessons) * 100, 1)
                : null;

            $rows->push([
                'enrollment' => $enrollment,
                'student' => $student,
                'avg_points' => $avgPoints,
                'freq_pct' => $freqPct,
            ]);
        }

        return $rows->sortBy(fn ($row) => $row['student']->name ?? '');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'shift',
        'workshop_id',
        'grade_level_ids',
        'grades_signature',
        'group_number',
        'capacity_hint',
        'status',
    ];

    protected $casts = [
        'academic_year_id' => 'integer',
        'grade_level_ids' => 'array',
        'group_number' => 'integer',
        'capacity_hint' => 'integer',
    ];

    /** Escola da turma */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /** Oficina associada diretamente à turma operacional */
    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }

    /** Alocações com vigência */
    public function memberships()
    {
        return $this->hasMany(ClassroomMembership::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers / Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Retorna a lista de matrículas (StudentEnrollment) vigentes na data/hora informada.
     */
    public function rosterAt(\Carbon\CarbonInterface $at)
    {
        return StudentEnrollment::query()
            ->whereHas('memberships', function ($q) use ($at) {
                $q->where('classroom_id', $this->id)
                    ->where('starts_at', '<=', $at)
                    ->where(function ($q) use ($at) {
                        $q->whereNull('ends_at')->orWhere('ends_at', '>', $at);
                    });
            })
            ->with(['student', 'gradeLevel'])
            ->get();
    }

    public function hasAcademicData(): bool
    {
        return $this->lessons()->exists() || $this->assessments()->exists();
    }

    public function getGradeLevelNamesAttribute(): string
    {
        $ids = $this->grade_level_ids ?? [];
        if (empty($ids)) {
            return '—';
        }

        return GradeLevel::query()
            ->whereIn('id', $ids)
            ->orderBy('sequence')
            ->orderBy('id')
            ->get(['id', 'short_name', 'name'])
            ->map(fn (GradeLevel $grade) => $grade->short_name ?: $grade->name)
            ->implode(', ');
    }

    public static function normalizeGradeLevelIds(array $gradeLevelIds): array
    {
        $ids = collect($gradeLevelIds)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return [];
        }

        return GradeLevel::query()
            ->whereIn('id', $ids->all())
            ->orderBy('sequence')
            ->orderBy('id')
            ->pluck('id')
            ->all();
    }

    public static function buildGradesSignature(array $gradeLevelIds): string
    {
        return implode(',', self::normalizeGradeLevelIds($gradeLevelIds));
    }

    public function getNameAttribute(): string
    {
        $workshopName = $this->workshop?->name ?? 'Turma';
        $grades = $this->grade_level_names ?? '';
        $group = $this->group_number ? '#'.$this->group_number : '';
        $year = $this->academic_year_id ?? '';

        return trim(implode(' ', array_filter([$workshopName, $grades, $year, $group])));
    }
}

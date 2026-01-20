<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'academic_year',          // int (2025, 2026...)
        'shift',                  // manhã/tarde/noite...
        'school_workshop_id',     // contrato escola↔oficina
        'grades_signature',       // "1,2" (ids ordenados)
        'group_number',           // 1..N (auto)
        'capacity_hint',
        'status',
        'locked_at',
    ];

    protected $casts = [
        'academic_year' => 'integer',
        'group_number' => 'integer',
        'capacity_hint' => 'integer',
        'locked_at' => 'datetime',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function schoolWorkshop(): BelongsTo
    {
        return $this->belongsTo(SchoolWorkshop::class, 'school_workshop_id');
    }

    public function gradeLevels(): BelongsToMany
    {
        return $this->belongsToMany(GradeLevel::class, 'classroom_grade_level')
            ->withTimestamps();
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(ClassroomMembership::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    /**
     * Conveniência: $classroom->workshop (via contrato).
     * Eager load recomendado: schoolWorkshop.workshop
     */
    public function getWorkshopAttribute(): ?Workshop
    {
        if ($this->relationLoaded('schoolWorkshop') && $this->schoolWorkshop) {
            return $this->schoolWorkshop->relationLoaded('workshop')
                ? $this->schoolWorkshop->workshop
                : $this->schoolWorkshop->workshop()->first();
        }

        $sw = $this->schoolWorkshop()->with('workshop')->first();
        return $sw?->workshop;
    }

    /**
     * Nomes das séries/anos (ex.: "1º, 2º").
     * Para evitar N+1, eager load gradeLevels.
     */
    public function getGradeLevelNamesAttribute(): string
    {
        $levels = $this->relationLoaded('gradeLevels')
            ? $this->gradeLevels
            : $this->gradeLevels()->orderBy('sequence')->orderBy('id')->get();

        if ($levels->isEmpty()) return '—';

        return $levels
            ->map(fn (GradeLevel $g) => $g->short_name ?: $g->name)
            ->implode(', ');
    }

    /**
     * Nome exibível (oficina + séries + ano + grupo se > 1)
     */
    public function getNameAttribute(): string
    {
        $workshopName = $this->workshop?->name ?? 'Turma';
        $grades = $this->grade_level_names ?? '';
        $year = (string)($this->academic_year ?? '');
        $group = ($this->group_number ?? 1) > 1 ? '#'.$this->group_number : '';

        return trim(implode(' ', array_filter([$workshopName, $grades, $year, $group])));
    }

    /**
     * Lista de matrículas vigentes em $at (via memberships).
     */
    public function rosterAt(CarbonInterface|string|null $at = null)
    {
        $at = $at instanceof CarbonInterface ? $at : Carbon::parse($at ?? now());

        return $this->memberships()
            ->activeAt($at)
            ->with(['enrollment.student', 'enrollment.gradeLevel'])
            ->get()
            ->map(fn (ClassroomMembership $m) => $m->enrollment)
            ->filter()
            ->unique('id')
            ->values();
    }

    public function hasAcademicData(): bool
    {
        return $this->lessons()->exists() || $this->assessments()->exists();
    }

    public function lockIfHasAcademicData(): bool
    {
        if ($this->locked_at !== null) return true;
        if (! $this->hasAcademicData()) return false;

        $this->forceFill(['locked_at' => now()])->save();
        return true;
    }

    /**
     * Normaliza ids de grade level: int, únicos, ordenados.
     */
    public static function normalizeGradeLevelIds(array $gradeLevelIds): array
    {
        $ids = collect($gradeLevelIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) return [];

        return GradeLevel::query()
            ->whereIn('id', $ids->all())
            ->orderBy('sequence')
            ->orderBy('id')
            ->pluck('id')
            ->all();
    }

    /**
     * Assinatura "1,2,5" (ids ordenados) para identificar conjunto de séries.
     */
    public static function buildGradesSignature(array $gradeLevelIds): string
    {
        $ids = self::normalizeGradeLevelIds($gradeLevelIds);
        return implode(',', $ids);
    }
}


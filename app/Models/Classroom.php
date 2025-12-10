<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'parent_classroom_id',
        'name',
        'shift',
        'is_active',
        'academic_year',
        'grade_level_key',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'academic_year' => 'integer',
    ];

    /** Escola da turma */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /** Turma ↔ Anos (pivot: classroom_grade_level) */
    public function gradeLevels()
    {
        return $this->belongsToMany(GradeLevel::class, 'classroom_grade_level')
            ->withTimestamps();
    }

    /** Turma ↔ Oficinas (pivot: classroom_workshop, com max_students) */
    public function workshops()
    {
        return $this->belongsToMany(Workshop::class, 'classroom_workshop')
            ->withPivot('max_students')
            ->withTimestamps();
    }

    /**
     * Acesso conveniente: $classroom->workshop
     * Retorna o primeiro workshop associado (útil para SUBTURMA vinculada a 1 oficina).
     * OBS: não é relação Eloquent; é um accessor. Para evitar N+1, faça eager load de 'workshops'.
     */
    public function getWorkshopAttribute()
    {
        if ($this->relationLoaded('workshops')) {
            return $this->workshops->first();
        }

        return $this->workshops()->first();
    }

    /** Turma pai / subturmas */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_classroom_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_classroom_id');
    }

    /** Alocações em subturmas por oficina */
    public function workshopAllocations()
    {
        return $this->hasMany(WorkshopAllocation::class, 'child_classroom_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers / Scopes
    |--------------------------------------------------------------------------
    */

    public function isParent(): bool
    {
        return $this->parent_classroom_id === null;
    }

    public function scopeOnlyParents($q)
    {
        return $q->whereNull('parent_classroom_id');
    }

    public function scopeOnlyChildren($q)
    {
        return $q->whereNotNull('parent_classroom_id');
    }

    /** Conjunto DERIVADO de alunos (PAI) com base em episódios ativos (StudentEnrollment) */
    public function eligibleEnrollments()
    {
        $gradeLevelIds = $this->gradeLevels()->pluck('grade_levels.id')->all();

        // Base: episódios ativos no ano/escola/turno, filtrando série quando houver multi-ano
        $base = StudentEnrollment::query()
            ->with(['student', 'gradeLevel'])
            ->where('academic_year', $this->academic_year)
            ->where('school_id', $this->school_id)
            ->where('shift', $this->shift)
            ->where('status', StudentEnrollment::STATUS_ACTIVE)
            ->whereNull('ended_at')
            ->when(! empty($gradeLevelIds), fn ($q) => $q->whereIn('grade_level_id', $gradeLevelIds));

        // Overrides (A↔B): OUT = quem sai desta turma; IN = quem entra nesta turma
        $outIds = ClassroomOverride::where('from_classroom_id', $this->id)
            ->where('is_active', true)
            ->pluck('student_enrollment_id')
            ->all();

        $inIds = ClassroomOverride::where('to_classroom_id', $this->id)
            ->where('is_active', true)
            ->pluck('student_enrollment_id')
            ->all();

        // (base − OUT) ∪ IN
        $base->when(! empty($outIds), fn ($q) => $q->whereNotIn('id', $outIds));

        return $base->when(! empty($inIds), fn ($q) => $q->orWhereIn('id', $inIds));
    }
}

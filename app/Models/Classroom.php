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
        'school_workshop_id',
        'workshop_group_set_id',
        'group_number',
        'name',
        'shift',
        'is_active',
        'academic_year',
        'grade_level_key',
        'status',
        'locked_at',
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

    /** Oficina associada diretamente ao grupo (novo modelo explícito) */
    public function schoolWorkshop()
    {
        return $this->belongsTo(SchoolWorkshop::class);
    }

    /** Conjunto de grupos de oficina (novo modelo explícito) */
    public function groupSet()
    {
        return $this->belongsTo(WorkshopGroupSet::class, 'workshop_group_set_id');
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
     * Retorna a oficina vinculada ao contrato da escola.
     * OBS: não é relação Eloquent; é um accessor. Para evitar N+1, faça eager load de 'schoolWorkshop.workshop'.
     */
    public function getWorkshopAttribute()
    {
        if ($this->relationLoaded('schoolWorkshop') && $this->schoolWorkshop) {
            return $this->schoolWorkshop->workshop;
        }

        $schoolWorkshop = $this->schoolWorkshop()->with('workshop')->first();

        return $schoolWorkshop?->workshop;
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

    /**
     * Verifica se há dados acadêmicos vinculados à turma (aulas ou avaliações).
     */
    public function hasAcademicData(): bool
    {
        return Lesson::query()->where('classroom_id', $this->id)->exists()
            || Assessment::query()->where('classroom_id', $this->id)->exists();
    }

    /**
     * Define locked_at quando existem dados acadêmicos.
     * Retorna true se a turma foi (ou já estava) bloqueada.
     */
    public function lockIfHasAcademicData(): bool
    {
        if ($this->locked_at !== null) {
            return true;
        }

        if (! $this->hasAcademicData()) {
            return false;
        }

        $this->forceFill([
            'locked_at' => now(),
        ])->save();

        return true;
    }
}

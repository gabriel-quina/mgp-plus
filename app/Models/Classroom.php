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

    public function getNameAttribute(): string
    {
        $workshopName = $this->workshop?->name ?? 'Turma';
        $grades = $this->grades_signature ?? '';
        $group = $this->group_number ? '#'.$this->group_number : '';
        $year = $this->academic_year_id ?? '';

        return trim(implode(' ', array_filter([$workshopName, $grades, $year, $group])));
    }
}

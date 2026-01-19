<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * School: adiciona scopes para lidar com "históricas" no UX.
 */
class School extends Model
{
    use HasFactory;

    protected $table = 'schools';

    protected $fillable = [
        'city_id',
        'name',
        'is_historical',
        'street',
        'number',
        'neighborhood',
        'complement',
        'cep',
    ];

    protected $casts = [
        'is_historical' => 'boolean',
    ];

    /* ============ Relações ============ */

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function workshops()
    {
        return $this->belongsToMany(Workshop::class)->withTimestamps();
    }

    public function roleAssignments()
    {
        return $this->morphMany(\App\Models\RoleAssignment::class, 'scope');
    }

    /**
     * Matriculas.
     */
    public function enrollments()
    {
        // Episódios de matrícula dessa escola
        return $this->hasMany(\App\Models\StudentEnrollment::class);
    }

    /**
     * Turmas da escola (operacionais).
     */
    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
        // se preferir evitar o use:
        // return $this->hasMany(\App\Models\Classroom::class);
    }

    /* ============ Scopes ============ */

    /** Usar em combos/listas normais p/ esconder históricas. */
    public function scopeNonHistorical($q)
    {
        return $q->where('is_historical', false);
    }

    /** Usar no endpoint de busca (typeahead), para incluir históricas também. */
    public function scopeIncludeHistorical($q)
    {
        return $q; // só para semântica; não filtra nada
    }

    /** Só históricas (quando precisar). */
    public function scopeOnlyHistorical($q)
    {
        return $q->where('is_historical', true);
    }
}

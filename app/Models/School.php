<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class School extends Model
{
    use HasFactory;

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

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Contratos escola↔oficina (histórico).
     */
    public function schoolWorkshops(): HasMany
    {
        return $this->hasMany(SchoolWorkshop::class);
    }

    /**
     * Conveniência (opcional): lista de workshops através dos contratos.
     * (Isso NÃO substitui os contratos; é só atalho.)
     */
    public function workshops(): HasManyThrough
    {
        return $this->hasManyThrough(
            Workshop::class,
            SchoolWorkshop::class,
            'school_id',   // FK em school_workshops
            'id',          // PK em workshops
            'id',          // PK em schools
            'workshop_id'  // FK em school_workshops -> workshops
        );
    }

    public function roleAssignments(): MorphMany
    {
        return $this->morphMany(RoleAssignment::class, 'scope');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }

    public function scopeNonHistorical($q)
    {
        return $q->where('is_historical', false);
    }

    public function scopeIncludeHistorical($q)
    {
        return $q;
    }

    public function scopeOnlyHistorical($q)
    {
        return $q->where('is_historical', true);
    }
}


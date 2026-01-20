<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GradeLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_name',
        'sequence',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sequence' => 'integer',
    ];

    public function studentEnrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class, 'grade_level_id');
    }

    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'classroom_grade_level')
            ->withTimestamps();
    }

    // Scopes úteis (opcional)
    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function scopeOrdered($q)
    {
        return $q->orderBy('sequence')->orderBy('id');
    }

    // Normalização opcional
    public function setShortNameAttribute($value): void
    {
        $v = trim((string) $value);
        $this->attributes['short_name'] = $v !== '' ? $v : null;
    }

    // Alias opcional para compatibilidade
    // public function studentYears(): HasMany
    // {
    //     return $this->studentEnrollments();
    // }
}


<?php

namespace App\Models;

use App\Models\Abstract\Person;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Person
{
    use HasFactory;

    protected $table = 'teachers';

    protected $fillable = [
        'name',
        'cpf',
        'email',
        'birthdate',
        'is_active',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'is_active' => 'boolean',
    ];

    /** Vínculos/empregos (our_clt/our_pj/our_temporary/municipal) */
    public function engagements(): HasMany
    {
        return $this->hasMany(TeacherEngagement::class);
    }

    /** Cidades onde pode atuar */
    public function cityAccesses(): HasMany
    {
        return $this->hasMany(TeacherCityAccess::class);
    }

    /**
     * Habilitação/alocação em escolas (por ano/turno) — seu modelo diz que é por escola.
     * (Mesmo não sendo "na turma", ainda é uma entidade válida)
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(TeachingAssignment::class);
    }

    /** Aulas lançadas para este professor */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }
}


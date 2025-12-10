<?php

namespace App\Models;

use App\Models\Abstract\Person;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Students: dados pessoais do aluno.
 * Toda informação de "escola atual" vem do episódio ativo em StudentEnrollment.
 */
class Student extends Person
{
    protected $table = 'students';

    // Liste tudo aqui (não use array_merge em propriedades)
    protected $fillable = [
        'name',
        'cpf',
        'email',
        'birthdate',
        'race_color',
        'has_disability',
        'disability_types',
        'disability_details',
        'allergies',
        'emergency_contact_name',
        'emergency_contact_phone',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'has_disability' => 'boolean',
        'disability_types' => 'array',
    ];

    /* ===================== Relações ===================== */

    /** Histórico completo de episódios de matrícula. */
    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    /** Episódio ativo (0 ou 1). */
    public function currentEnrollment(): HasOne
    {
        return $this->hasOne(StudentEnrollment::class)->active();
    }

    /* ===================== Helpers ===================== */

    /**
     * Atalho para a escola atual via episódio ativo.
     * Uso no Blade: optional($student->current_school)->name
     */
    public function getCurrentSchoolAttribute()
    {
        return $this->currentEnrollment?->school;
    }
}

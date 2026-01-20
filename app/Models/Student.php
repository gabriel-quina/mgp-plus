<?php

namespace App\Models;

use App\Models\Abstract\Person;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Person
{
    protected $table = 'students';

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

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function currentEnrollment(): HasOne
    {
        // fallback seguro: se tiver mais de um "ongoing", pega o mais recente
        return $this->hasOne(StudentEnrollment::class)
            ->ongoing()
            ->latestOfMany('created_at');
    }

    public function getCurrentSchoolAttribute()
    {
        return $this->currentEnrollment?->school;
    }
}


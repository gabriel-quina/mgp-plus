<?php

namespace App\Models;

use App\Models\Abstract\Person;

class Student extends Person
{
    protected $fillable = [
        'school_id',
        'name',
        'cpf',
        'email',
        'birthdate',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}


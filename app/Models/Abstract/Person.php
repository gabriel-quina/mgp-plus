<?php

namespace App\Models\Abstract;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

abstract class Person extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'cpf',
        'email',
        'birthdate',
    ];

    protected $casts = [
        'birthdate' => 'date',
    ];
}


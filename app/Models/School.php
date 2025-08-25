<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_id',
        'name',
        'cep',
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}


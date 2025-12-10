<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherCityAccess extends Model
{
    use HasFactory;

    protected $table = 'teacher_city_access';

    protected $fillable = [
        'teacher_id',
        'city_id',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}


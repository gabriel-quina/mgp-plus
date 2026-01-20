<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherCityAccess extends Model
{
    use HasFactory;

    protected $table = 'teacher_city_access';

    protected $fillable = [
        'teacher_id',
        'city_id',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = [
        'classroom_id',
        'lesson_at',
        'topic',
        'notes',
        'is_locked',
    ];

    protected $casts = [
        'lesson_at' => 'datetime',
        'is_locked' => 'bool',
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function attendances()
    {
        return $this->hasMany(LessonAttendance::class);
    }
}

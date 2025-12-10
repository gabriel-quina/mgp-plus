<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = [
        'classroom_id',
        'workshop_id',
        'taught_at',
        'starts_at',
        'ends_at',
        'topic',
        'notes',
        'is_locked',
    ];

    protected $casts = [
        'taught_at' => 'date',
        'starts_at' => 'datetime:H:i',
        'ends_at' => 'datetime:H:i',
        'is_locked' => 'bool',
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }

    public function attendances()
    {
        return $this->hasMany(LessonAttendance::class);
    }
}

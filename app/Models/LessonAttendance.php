<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonAttendance extends Model
{
    protected $fillable = [
        'lesson_id',
        'student_enrollment_id',
        'present',
        'note',
    ];

    protected $casts = [
        'present' => 'bool',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(StudentEnrollment::class, 'student_enrollment_id');
    }
}

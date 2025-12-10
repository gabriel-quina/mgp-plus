<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassroomOverride extends Model
{
    protected $fillable = [
        'student_enrollment_id', // <- agora por episódio
        'from_classroom_id',
        'to_classroom_id',
        'is_active',
        'reason',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function studentEnrollment()
    {
        return $this->belongsTo(StudentEnrollment::class);
    }

    public function fromClassroom()
    {
        return $this->belongsTo(Classroom::class, 'from_classroom_id');
    }

    public function toClassroom()
    {
        return $this->belongsTo(Classroom::class, 'to_classroom_id');
    }
}

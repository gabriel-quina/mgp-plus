<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkshopAllocation extends Model
{
    protected $fillable = [
        'child_classroom_id',
        'workshop_id',
        'student_enrollment_id', // <- agora por episódio
        'is_locked',
        'note',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
    ];

    public function childClassroom()
    {
        return $this->belongsTo(Classroom::class, 'child_classroom_id');
    }

    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }

    public function studentEnrollment()
    {
        return $this->belongsTo(StudentEnrollment::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassroomMembership extends Model
{
    protected $fillable = [
        'student_enrollment_id',
        'classroom_id',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(StudentEnrollment::class, 'student_enrollment_id');
    }

    public function scopeActiveAt($query, \Carbon\CarbonInterface $at)
    {
        return $query->where('starts_at', '<=', $at)
            ->where(function ($q) use ($at) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', $at);
            });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassroomMembership extends Model
{
    protected static function booted(): void
    {
        static::creating(function (ClassroomMembership $membership) {
            if (! $membership->starts_at) {
                throw new \InvalidArgumentException('starts_at is required for classroom memberships.');
            }

            $classroom = $membership->classroom()->first();
            if (! $classroom) {
                return;
            }

            ClassroomMembership::query()
                ->where('student_enrollment_id', $membership->student_enrollment_id)
                ->activeAt($membership->starts_at)
                ->whereHas('classroom', function ($query) use ($classroom) {
                    $query->where('workshop_id', $classroom->workshop_id)
                        ->where('school_id', $classroom->school_id)
                        ->where('academic_year_id', $classroom->academic_year_id);
                })
                ->update(['ends_at' => $membership->starts_at]);
        });
    }

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

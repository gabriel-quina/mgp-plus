<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeachingAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'school_id',
        'engagement_id',
        'academic_year',
        'shift',
        'hours_per_week',
        'notes',
    ];

    protected $casts = [
        'academic_year'  => 'integer',
        'hours_per_week' => 'integer',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class)->withDefault();
    }

    public function engagement()
    {
        return $this->belongsTo(TeacherEngagement::class)->withDefault();
    }
}


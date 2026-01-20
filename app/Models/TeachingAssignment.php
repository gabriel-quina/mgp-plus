<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeachingAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'school_id',
        'engagement_id',
        'academic_year',
        'hours_per_week',
        'notes',
    ];

    protected $casts = [
        'academic_year'  => 'integer',
        'hours_per_week' => 'integer',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function engagement(): BelongsTo
    {
        return $this->belongsTo(TeacherEngagement::class, 'engagement_id');
    }

    public function scopeForSchool($q, int $schoolId)
    {
        return $q->where('school_id', $schoolId);
    }

    public function scopeForYear($q, int $year)
    {
        return $q->where('academic_year', $year);
    }
}


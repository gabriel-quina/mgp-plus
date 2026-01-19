<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    protected $fillable = [
        'classroom_id',
        'title',
        'description',
        'assessment_at',
        'scale_type',
        'max_points',
    ];

    protected $casts = [
        'assessment_at' => 'datetime',
        'max_points' => 'decimal:1',
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function grades()
    {
        return $this->hasMany(AssessmentGrade::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    protected $fillable = [
        'classroom_id',
        'workshop_id',
        'title',
        'description',
        'due_at',
        'scale_type',
        'max_points',
    ];

    protected $casts = [
        'due_at' => 'date',
        'max_points' => 'decimal:1',
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }

    public function grades()
    {
        return $this->hasMany(AssessmentGrade::class);
    }
}

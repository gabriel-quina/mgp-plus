<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    protected $fillable = [
        'classroom_id',
        'title',
        'description',
        'due_at',       // data aplicada
        'scale_type',   // ex: 'points' | 'concept' (defina como você usa)
        'max_points',
    ];

    protected $casts = [
        'due_at' => 'date',
        'max_points' => 'decimal:1',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(AssessmentGrade::class);
    }
}


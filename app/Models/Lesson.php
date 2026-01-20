<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    protected $fillable = [
        'classroom_id',
        'teacher_id',
        'taught_at',     // só a data
        'topic',
        'notes',
        'is_locked',
    ];

    protected $casts = [
        'taught_at' => 'date',
        'is_locked' => 'boolean',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(LessonAttendance::class);
    }

    /**
     * Conveniência: oficina via turma (sem workshop_id aqui).
     */
    public function getWorkshopAttribute(): ?Workshop
    {
        return $this->classroom?->workshop;
    }
}


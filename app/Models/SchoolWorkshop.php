<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolWorkshop extends Model
{
    public const STATUS_ACTIVE   = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_EXPIRED  = 'expired';

    protected $fillable = [
        'school_id',
        'workshop_id',
        'starts_at',
        'ends_at',
        'status',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    /**
     * Contrato ativo em uma data (ends_at exclusivo).
     */
    public function scopeActiveAt($q, $at = null)
    {
        $at = $at ? now()->parse($at)->toDateString() : now()->toDateString();

        return $q->whereDate('starts_at', '<=', $at)
            ->where(function ($q) use ($at) {
                $q->whereNull('ends_at')
                  ->orWhereDate('ends_at', '>', $at);
            })
            ->where('status', self::STATUS_ACTIVE);
    }
}


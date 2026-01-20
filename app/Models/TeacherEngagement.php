<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherEngagement extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'engagement_type',
        'hours_per_week',
        'status',
        'start_date',
        'end_date',
        'city_id',
        'notes',
    ];

    protected $casts = [
        'hours_per_week' => 'integer',
        'start_date'     => 'date',
        'end_date'       => 'date',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Vínculo ativo numa data (end_date inclusivo).
     * Se você preferir end_date exclusivo, troque >= por > e trate datas.
     */
    public function scopeActiveAt($q, $at = null)
    {
        $at = $at ? now()->parse($at)->toDateString() : now()->toDateString();

        return $q->whereDate('start_date', '<=', $at)
            ->where(function ($q) use ($at) {
                $q->whereNull('end_date')
                  ->orWhereDate('end_date', '>=', $at);
            });
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->engagement_type) {
            'our_clt'       => 'CLT (nossa empresa)',
            'our_pj'        => 'PJ (nossa empresa)',
            'our_temporary' => 'Temporário (nossa empresa)',
            'municipal'     => 'Municipal (prefeitura)',
            default         => ucfirst((string) $this->engagement_type),
        };
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    // Helpers simples para exibição
    public function getTypeLabelAttribute(): string
    {
        return match ($this->engagement_type) {
            'our_clt'       => 'CLT (nossa empresa)',
            'our_pj'        => 'PJ (nossa empresa)',
            'our_temporary' => 'Temporário (nossa empresa)',
            'municipal'     => 'Municipal (prefeitura)',
            default         => ucfirst($this->engagement_type),
        };
    }
}


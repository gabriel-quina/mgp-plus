<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class SchoolWorkshop extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $table = 'school_workshop';

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

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }

    public function scopeActive($query, ?Carbon $date = null)
    {
        $checkDate = ($date ?? now())->toDateString();

        return $query
            ->where('status', self::STATUS_ACTIVE)
            ->where(function ($q) use ($checkDate) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $checkDate);
            })
            ->where(function ($q) use ($checkDate) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $checkDate);
            });
    }

    public function isActiveOn(?Carbon $date = null): bool
    {
        $checkDate = ($date ?? now())->toDateString();

        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->toDateString() > $checkDate) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->toDateString() < $checkDate) {
            return false;
        }

        return true;
    }
}

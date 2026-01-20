<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassroomMembership extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_enrollment_id',
        'classroom_id',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (ClassroomMembership $m) {
            if (! $m->starts_at) {
                throw new \InvalidArgumentException('starts_at is required for classroom memberships.');
            }

            // Fecha qualquer membership que esteja ativa no início desta (ends_at exclusivo)
            ClassroomMembership::query()
                ->where('student_enrollment_id', $m->student_enrollment_id)
                ->activeAt($m->starts_at)
                ->update(['ends_at' => $m->starts_at]);

            // Evita sobreposição com uma membership futura já agendada:
            // se existir uma próxima membership com starts_at > este starts_at,
            // e esta nova não tem ends_at, então fecha em next.starts_at.
            $next = ClassroomMembership::query()
                ->where('student_enrollment_id', $m->student_enrollment_id)
                ->where('starts_at', '>', $m->starts_at)
                ->orderBy('starts_at')
                ->first();

            if ($next) {
                if ($m->ends_at === null) {
                    $m->ends_at = $next->starts_at;
                } elseif ($m->ends_at > $next->starts_at) {
                    throw new \InvalidArgumentException('Membership interval overlaps a future membership.');
                }
            }
        });

        static::saving(function (ClassroomMembership $m) {
            if ($m->starts_at && $m->ends_at && $m->ends_at <= $m->starts_at) {
                throw new \InvalidArgumentException('ends_at must be greater than starts_at (ends_at is exclusive).');
            }
        });
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class, 'student_enrollment_id');
    }

    /**
     * Ativo em $at:
     * starts_at <= $at AND (ends_at IS NULL OR ends_at > $at)
     */
    public function scopeActiveAt(Builder $q, CarbonInterface|string|null $at = null): Builder
    {
        $at = $at instanceof CarbonInterface ? $at : Carbon::parse($at ?? now());

        return $q->where('starts_at', '<=', $at)
            ->where(function ($q) use ($at) {
                $q->whereNull('ends_at')
                  ->orWhere('ends_at', '>', $at);
            });
    }
}


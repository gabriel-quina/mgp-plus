<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentEnrollment extends Model
{
    use HasFactory;

    protected $table = 'student_enrollments';

    /**
     * STATUS (workflow correto)
     * - pre_enrolled: pré-matrícula (gerada para o ano seguinte)
     * - enrolled: matrícula efetivada (antes do início das aulas)
     * - active: cursando (após início do curso / comparecimento)
     */
    public const STATUS_PRE_ENROLLED = 'pre_enrolled';
    public const STATUS_ENROLLED     = 'enrolled';

    public const STATUS_ACTIVE      = 'active';
    public const STATUS_COMPLETED   = 'completed';
    public const STATUS_FAILED      = 'failed';
    public const STATUS_TRANSFERRED = 'transferred';
    public const STATUS_DROPPED     = 'dropped';
    public const STATUS_SUSPENDED   = 'suspended';

    public const SCOPE_FIRST    = 'first';
    public const SCOPE_INTERNAL = 'internal';
    public const SCOPE_EXTERNAL = 'external';

    public const SHIFT_MORNING   = 'morning';
    public const SHIFT_AFTERNOON = 'afternoon';
    public const SHIFT_EVENING   = 'evening';

    protected $fillable = [
        'student_id',
        'school_id',
        'grade_level_id',
        'academic_year',
        'shift',
        'status',
        'transfer_scope',
        'origin_school_id',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'academic_year' => 'integer',
        'started_at'    => 'date',
        'ended_at'      => 'date',
    ];

    /* ================= Relações ================= */

    public function student()      { return $this->belongsTo(Student::class); }
    public function school()       { return $this->belongsTo(School::class); }
    public function originSchool() { return $this->belongsTo(School::class, 'origin_school_id'); }
    public function gradeLevel()   { return $this->belongsTo(GradeLevel::class, 'grade_level_id'); }
    public function memberships()  { return $this->hasMany(ClassroomMembership::class); }

    public function currentMembership()
    {
        return $this->hasOne(ClassroomMembership::class)->whereNull('ends_at')->latest('starts_at');
    }

    /* ================= Fonte única de verdade (domínio) ================= */

    public static function allowedStatuses(): array
    {
        return [
            self::STATUS_PRE_ENROLLED,
            self::STATUS_ENROLLED,
            self::STATUS_ACTIVE,
            self::STATUS_COMPLETED,
            self::STATUS_FAILED,
            self::STATUS_TRANSFERRED,
            self::STATUS_DROPPED,
            self::STATUS_SUSPENDED,
        ];
    }

    public static function ongoingStatuses(): array
    {
        return [
            self::STATUS_PRE_ENROLLED,
            self::STATUS_ENROLLED,
            self::STATUS_ACTIVE,
        ];
    }

    public static function shiftValues(): array
    {
        return [self::SHIFT_MORNING, self::SHIFT_AFTERNOON, self::SHIFT_EVENING];
    }

    public static function transferScopes(): array
    {
        return [self::SCOPE_FIRST, self::SCOPE_INTERNAL, self::SCOPE_EXTERNAL];
    }

    /* ================= Scopes ================= */

    /**
     * “Cursando” (você pediu: cursando só após iniciar/comparecer)
     */
    public function scopeActive($q)
    {
        return $q->where('status', self::STATUS_ACTIVE)
                 ->whereNull('ended_at');
    }

    /**
     * “Em andamento” (pré + matriculado + cursando)
     */
    public function scopeOngoing($q)
    {
        return $q->whereIn('status', self::ongoingStatuses())
                 ->whereNull('ended_at');
    }

    public function scopeForYear($q, int $year) { return $q->where('academic_year', $year); }
    public function scopeForSchool($q, int $id) { return $q->where('school_id', $id); }

    /* ================= Helpers ================= */

    public function getIsActiveAttribute(): bool
    {
        return $this->status === self::STATUS_ACTIVE && $this->ended_at === null;
    }

    public function getIsPreEnrolledAttribute(): bool
    {
        return $this->status === self::STATUS_PRE_ENROLLED && $this->ended_at === null;
    }

    public function getIsEnrolledAttribute(): bool
    {
        return $this->status === self::STATUS_ENROLLED && $this->ended_at === null;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PRE_ENROLLED => 'Pré-matrícula',
            self::STATUS_ENROLLED     => 'Matriculado',
            self::STATUS_ACTIVE       => 'Cursando',
            self::STATUS_COMPLETED    => 'Concluída',
            self::STATUS_FAILED       => 'Reprovado',
            self::STATUS_TRANSFERRED  => 'Transferido',
            self::STATUS_DROPPED      => 'Evasão/Cancelada',
            self::STATUS_SUSPENDED    => 'Trancada',
            default                   => ucfirst((string) $this->status),
        };
    }

    public function getTransferScopeLabelAttribute(): string
    {
        return match ($this->transfer_scope) {
            self::SCOPE_FIRST    => 'Primeira matrícula',
            self::SCOPE_INTERNAL => 'Transferência interna',
            self::SCOPE_EXTERNAL => 'Transferência externa',
            default              => ucfirst((string) $this->transfer_scope),
        };
    }

    public function getShiftLabelAttribute(): string
    {
        return match ($this->shift) {
            self::SHIFT_MORNING   => 'Manhã',
            self::SHIFT_AFTERNOON => 'Tarde',
            self::SHIFT_EVENING   => 'Noite',
            default               => ucfirst((string) $this->shift),
        };
    }
}

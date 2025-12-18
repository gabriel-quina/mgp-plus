<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentEnrollment extends Model
{
    use HasFactory;

    protected $table = 'student_enrollments';

    /**
     * STATUS
     * - pre_enrolled: pré-matrícula (gerada ao final do ano / renovação)
     * - enrolled: matrícula efetivada (antes do início das aulas)
     * - active: cursando (após início real / comparecimento)
     * - demais: status terminais/administrativos
     */
    public const STATUS_PRE_ENROLLED = 'pre_enrolled';

    public const STATUS_ENROLLED = 'enrolled';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_TRANSFERRED = 'transferred';

    public const STATUS_DROPPED = 'dropped';

    public const STATUS_SUSPENDED = 'suspended';

    /** TRANSFER SCOPE */
    public const SCOPE_FIRST = 'first';

    public const SCOPE_INTERNAL = 'internal';

    public const SCOPE_EXTERNAL = 'external';

    /** SHIFT */
    public const SHIFT_MORNING = 'morning';

    public const SHIFT_AFTERNOON = 'afternoon';

    public const SHIFT_EVENING = 'evening';

    protected $fillable = [
        'student_id',
        'school_id',          // destino
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
        'started_at' => 'date',
        'ended_at' => 'date',
    ];

    /* ================= Relações ================= */

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function school() // destino
    {
        return $this->belongsTo(School::class);
    }

    public function originSchool()
    {
        return $this->belongsTo(School::class, 'origin_school_id');
    }

    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class, 'grade_level_id');
    }

    /* ================= Scopes ================= */

    /**
     * "Cursando" de verdade.
     * (Você pediu: cursando só após iniciar e o aluno comparecer.)
     */
    public function scopeActive($q)
    {
        return $q->where('status', self::STATUS_ACTIVE)
            ->whereNull('ended_at');
    }

    /**
     * Em andamento "administrativo" (pré/matriculado/cursando).
     * Útil para listagens do ano atual que consideram pré-matrículas.
     */
    public function scopeOngoing($q)
    {
        return $q->whereIn('status', [
            self::STATUS_PRE_ENROLLED,
            self::STATUS_ENROLLED,
            self::STATUS_ACTIVE,
        ])
            ->whereNull('ended_at');
    }

    public function scopeForYear($q, int $year)
    {
        return $q->where('academic_year', $year);
    }

    public function scopeForSchool($q, int $id)
    {
        return $q->where('school_id', $id);
    }

    /* ================= Helpers / Accessors ================= */

    public function getIsActiveAttribute(): bool
    {
        // Mantém compatibilidade: "is_active" = cursando
        return $this->status === self::STATUS_ACTIVE && $this->ended_at === null;
    }

    public function getIsStudyingAttribute(): bool
    {
        // alias mais semântico para cursando
        return $this->getIsActiveAttribute();
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
            self::STATUS_ENROLLED => 'Matriculado',
            self::STATUS_ACTIVE => 'Cursando',
            self::STATUS_COMPLETED => 'Concluída',
            self::STATUS_FAILED => 'Reprovado',
            self::STATUS_TRANSFERRED => 'Transferido',
            self::STATUS_DROPPED => 'Evasão/Cancelada',
            self::STATUS_SUSPENDED => 'Trancada',
            default => ucfirst((string) $this->status),
        };
    }

    public function getTransferScopeLabelAttribute(): string
    {
        return match ($this->transfer_scope) {
            self::SCOPE_FIRST => 'Primeira matrícula',
            self::SCOPE_INTERNAL => 'Transferência interna',
            self::SCOPE_EXTERNAL => 'Transferência externa',
            default => ucfirst((string) $this->transfer_scope),
        };
    }

    public function getShiftLabelAttribute(): string
    {
        return match ($this->shift) {
            self::SHIFT_MORNING => 'Manhã',
            self::SHIFT_AFTERNOON => 'Tarde',
            self::SHIFT_EVENING => 'Noite',
            default => ucfirst((string) $this->shift),
        };
    }

    /* ================= Utilitários ================= */

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
}

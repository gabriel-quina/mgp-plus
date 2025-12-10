<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentEnrollment extends Model
{
    use HasFactory;

    protected $table = 'student_enrollments';

    // Enums como constantes
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
        'school_id',          // destino
        'grade_level_id',     // <- corrigido
        'academic_year',
        'shift',
        'status',
        'transfer_scope',     // first|internal|external
        'origin_school_id',   // origem (pode ser histórica)
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'academic_year' => 'integer',
        'started_at'    => 'date',
        'ended_at'      => 'date',
    ];

    /* Relações */
    public function student()      { return $this->belongsTo(Student::class); }
    public function school()       { return $this->belongsTo(School::class); }                 // destino
    public function originSchool() { return $this->belongsTo(School::class, 'origin_school_id'); }
    public function gradeLevel()   { return $this->belongsTo(GradeLevel::class, 'grade_level_id'); } // <- corrigido

    /* Scopes */
    public function scopeActive($q)
    {
        return $q->where('status', self::STATUS_ACTIVE)
                 ->whereNull('ended_at');
    }
    public function scopeForYear($q, int $year)   { return $q->where('academic_year', $year); }
    public function scopeForSchool($q, int $id)   { return $q->where('school_id', $id); }

    /* Helpers */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === self::STATUS_ACTIVE && $this->ended_at === null;
    }
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE      => 'Cursando',
            self::STATUS_COMPLETED   => 'Concluída',
            self::STATUS_FAILED      => 'Reprovado',
            self::STATUS_TRANSFERRED => 'Transferido',
            self::STATUS_DROPPED     => 'Evasão/Cancelada',
            self::STATUS_SUSPENDED   => 'Trancada',
            default                  => ucfirst((string)$this->status),
        };
    }
    public function getTransferScopeLabelAttribute(): string
    {
        return match ($this->transfer_scope) {
            self::SCOPE_FIRST    => 'Primeira matrícula',
            self::SCOPE_INTERNAL => 'Transferência interna',
            self::SCOPE_EXTERNAL => 'Transferência externa',
            default              => ucfirst((string)$this->transfer_scope),
        };
    }
    public function getShiftLabelAttribute(): string
    {
        return match ($this->shift) {
            self::SHIFT_MORNING   => 'Manhã',
            self::SHIFT_AFTERNOON => 'Tarde',
            self::SHIFT_EVENING   => 'Noite',
            default               => ucfirst((string)$this->shift),
        };
    }
}


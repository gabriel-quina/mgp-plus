<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'cpf',
        'password',
        'is_master',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_master' => 'boolean',
        'must_change_password' => 'boolean',
    ];

    public function setCpfAttribute($value): void
    {
        $this->attributes['cpf'] = preg_replace('/\D+/', '', (string) $value) ?: null;
    }

    /* =========================
     | Relations
     *=========================*/

    public function userScope(): HasOne
    {
        return $this->hasOne(UserScope::class, 'user_id', 'id');
    }

    public function companyRoleAssignments(): HasMany
    {
        return $this->hasMany(CompanyRoleAssignment::class, 'user_id', 'id');
    }

    public function schoolRoleAssignments(): HasMany
    {
        return $this->hasMany(SchoolRoleAssignment::class, 'user_id', 'id');
    }

    public function companySchoolAccessGrants(): HasMany
    {
        return $this->hasMany(CompanySchoolAccessGrant::class, 'user_id', 'id');
    }

    /* =========================
     | Scope helpers
     *=========================*/

    public function scopeType(): ?string
    {
        // master é transversal, mas você ainda grava 'company' em user_scopes por conveniência
        return $this->userScope?->scope;
    }

    public function isCompany(): bool
    {
        return $this->scopeType() === 'company';
    }

    public function isSchool(): bool
    {
        return $this->scopeType() === 'school';
    }

    /* =========================
     | Role helpers
     *=========================*/

    public function hasCompanyRole(string $roleName): bool
    {
        return $this->companyRoleAssignments()
            ->whereHas('role', fn ($q) => $q->where('name', $roleName))
            ->exists();
    }

    public function hasSchoolRole(string $roleName, int $schoolId): bool
    {
        return $this->schoolRoleAssignments()
            ->where('school_id', $schoolId)
            ->whereHas('role', fn ($q) => $q->where('name', $roleName))
            ->exists();
    }

    /* =========================
     | Permission helper
     *=========================*/

    /**
     * Company: verifica permissão em qualquer role company do usuário
     * School: verifica permissão em roles school do usuário NAQUELA escola
     *
     * @param string $permissionName ex: 'dashboard.view'
     * @param int|null $schoolId obrigatório quando usuário é school
     */
    public function canByRole(string $permissionName, ?int $schoolId = null): bool
    {
        if ($this->is_master) {
            return true;
        }

        if ($this->isCompany()) {
            return $this->companyRoleAssignments()
                ->whereHas('role.permissions', fn ($q) => $q->where('name', $permissionName))
                ->exists();
        }

        if ($this->isSchool()) {
            if (! $schoolId) {
                return false; // em school, permission sempre depende de uma escola
            }

            return $this->schoolRoleAssignments()
                ->where('school_id', $schoolId)
                ->whereHas('role.permissions', fn ($q) => $q->where('name', $permissionName))
                ->exists();
        }

        return false;
    }

    /* =========================
     | Accessible schools
     *=========================*/

    public function accessibleSchools(): Collection
    {
        if ($this->is_master) {
            return School::orderBy('name')->get();
        }

        if ($this->isCompany()) {
            $schoolIds = $this->companySchoolAccessGrants()
                ->pluck('school_id')
                ->unique()
                ->values();

            if ($schoolIds->isEmpty()) {
                return collect();
            }

            return School::query()
                ->whereIn('id', $schoolIds)
                ->orderBy('name')
                ->get();
        }

        if ($this->isSchool()) {
            $schoolIds = $this->schoolRoleAssignments()
                ->pluck('school_id')
                ->unique()
                ->values();

            if ($schoolIds->isEmpty()) {
                return collect();
            }

            return School::query()
                ->whereIn('id', $schoolIds)
                ->orderBy('name')
                ->get();
        }

        return collect();
    }

    /**
     * Retorna a "actingSchool" default:
     * - se sessão tiver acting_school_id, usa ela
     * - senão, se só tiver 1 escola acessível, usa essa
     */
    public function actingSchool(): ?School
    {
        $schoolId = session('acting_school_id');

        if ($schoolId) {
            return School::find($schoolId);
        }

        $schools = $this->accessibleSchools();

        if ($schools->count() === 1) {
            return $schools->first();
        }

        return null;
    }
}


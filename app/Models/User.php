<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        // 'role', // legado: se existir no banco, ok manter, mas não use para permissão
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

    /* =========================
     | RBAC in-memory cache
     *=========================*/
    protected bool $rbacIndexed = false;

    /** @var array<string, array<string, bool>> roleName => [scopeKey => true] */
    protected array $rbacRoleIndex = [];

    /** @var array<string, array<string, bool>> permissionName => [scopeKey => true] */
    protected array $rbacPermissionIndex = [];

    public function setCpfAttribute($value): void
    {
        $this->attributes['cpf'] = preg_replace('/\D+/', '', (string) $value) ?: null;
    }

    /* =========================
     | Relations
     *=========================*/
    public function roleAssignments(): HasMany
    {
        return $this->hasMany(RoleAssignment::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_assignments')
            ->withPivot(['scope_type', 'scope_id'])
            ->withTimestamps();
    }

    /**
     * Use em listagens para evitar N+1:
     * User::withRbac()->get()
     */
    public function scopeWithRbac($q)
    {
        return $q->with(['roleAssignments.role.permissions']);
    }

    /* =========================
     | RBAC cache helpers
     *=========================*/
    protected function rbacScopeKey($scope): string
    {
        if (! $scope) {
            return 'global|global';
        }

        return $scope->getMorphClass() . '|' . $scope->getKey();
    }

    protected function buildRbacIndex(): void
    {
        if ($this->rbacIndexed) {
            return;
        }

        // carrega uma vez (se não tiver sido eager loaded)
        $this->loadMissing(['roleAssignments.role.permissions']);

        foreach ($this->roleAssignments as $ra) {
            $roleName = $ra->role?->name;
            if (! $roleName) {
                continue;
            }

            $scopeKey = ($ra->scope_type ?? 'global') . '|' . ($ra->scope_id ?? 'global');

            $this->rbacRoleIndex[$roleName][$scopeKey] = true;

            $perms = $ra->role?->permissions ?? collect();
            foreach ($perms as $perm) {
                $permName = $perm->name ?? null;
                if (! $permName) {
                    continue;
                }
                $this->rbacPermissionIndex[$permName][$scopeKey] = true;
            }
        }

        $this->rbacIndexed = true;
    }

    /* =========================
     | RBAC helpers (no N+1)
     *=========================*/
    public function hasRole(string $roleName, $scope = null): bool
    {
        $this->buildRbacIndex();

        $scopeKey = $this->rbacScopeKey($scope);

        return isset($this->rbacRoleIndex[$roleName][$scopeKey]);
    }

    public function assignRole(string $roleName, $scope = null): void
    {
        $role = Role::firstOrCreate(['name' => $roleName]);

        RoleAssignment::firstOrCreate([
            'user_id' => $this->id,
            'role_id' => $role->id,
            'scope_type' => $scope ? $scope->getMorphClass() : null,
            'scope_id' => $scope ? $scope->getKey() : null,
        ]);

        // invalida cache local (para refletir imediatamente no mesmo request)
        $this->rbacIndexed = false;
        $this->rbacRoleIndex = [];
        $this->rbacPermissionIndex = [];
    }

    public function canByRole(string $permissionName, $scope = null): bool
    {
        if ($this->is_master) {
            return true;
        }

        $this->buildRbacIndex();

        $scopeKey = $this->rbacScopeKey($scope);

        return isset($this->rbacPermissionIndex[$permissionName][$scopeKey]);
    }

    /* =========================
     | Accessible schools
     *=========================*/
    public function accessibleSchools(): Collection
    {
        // Master/empresa transversal
        if ($this->is_master
            || $this->hasRole('company_coordinator')
            || $this->hasRole('company_consultant')
        ) {
            return School::orderBy('name')->get();
        }

        // 1) Acesso direto por role escopada na escola
        $directSchoolIds = RoleAssignment::query()
            ->where('user_id', $this->id)
            ->where('scope_type', School::class)
            ->pluck('scope_id')
            ->unique()
            ->values();

        // 2) Acesso por cidade (secretaria municipal)
        $cityIds = RoleAssignment::query()
            ->where('user_id', $this->id)
            ->where('scope_type', City::class)
            ->whereHas('role', function ($q) {
                $q->whereIn('name', [
                    'city_education_secretary',
                    'city_coordinator',
                ]);
            })
            ->pluck('scope_id')
            ->unique()
            ->values();

        // 3) Acesso por estado (secretaria estadual)
        $stateIds = RoleAssignment::query()
            ->where('user_id', $this->id)
            ->where('scope_type', State::class)
            ->whereHas('role', function ($q) {
                $q->whereIn('name', [
                    'state_education_secretary',
                ]);
            })
            ->pluck('scope_id')
            ->unique()
            ->values();

        // sem escopo, sem acesso
        if ($directSchoolIds->isEmpty() && $cityIds->isEmpty() && $stateIds->isEmpty()) {
            return collect();
        }

        return School::query()
            ->where(function ($q) use ($directSchoolIds, $cityIds, $stateIds) {
                if ($directSchoolIds->isNotEmpty()) {
                    $q->whereIn('id', $directSchoolIds);
                }

                if ($cityIds->isNotEmpty()) {
                    $q->orWhere(function ($qq) use ($cityIds) {
                        $qq->whereIn('city_id', $cityIds)
                            ->where('administrative_dependency', 'municipal');
                    });
                }

                if ($stateIds->isNotEmpty()) {
                    $q->orWhere(function ($qq) use ($stateIds) {
                        $qq->whereHas('city', function ($cq) use ($stateIds) {
                            $cq->whereIn('state_id', $stateIds);
                        })
                            ->where('administrative_dependency', 'state');
                    });
                }
            })
            ->orderBy('name')
            ->get();
    }
}


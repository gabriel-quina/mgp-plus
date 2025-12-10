<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'cpf', 'password', 'is_master', 'role', 'must_change_password',
    ];

    protected $hidden = [
        'password', 'remember_token',
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
     | RBAC relations
     *=========================*/
    public function roleAssignments()
    {
        return $this->hasMany(RoleAssignment::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_assignments')
            ->withPivot(['scope_type', 'scope_id'])
            ->withTimestamps();
    }

    public function accessibleSchools()
    {
        // Master/empresa transversal
        if ($this->is_master
            || $this->hasRole('company_coordinator')
            || $this->hasRole('company_consultant')
            || ! empty($this->role)) {
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

        return School::query()
            ->when($directSchoolIds->isNotEmpty(), function ($q) use ($directSchoolIds) {
                $q->whereIn('id', $directSchoolIds);
            })
            ->when($cityIds->isNotEmpty(), function ($q) use ($cityIds) {
                $q->orWhere(function ($qq) use ($cityIds) {
                    $qq->whereIn('city_id', $cityIds)
                        ->where('administrative_dependency', 'municipal');
                });
            })
            ->when($stateIds->isNotEmpty(), function ($q) use ($stateIds) {
                $q->orWhere(function ($qq) use ($stateIds) {
                    $qq->whereHas('city', function ($cq) use ($stateIds) {
                        $cq->whereIn('state_id', $stateIds);
                    })
                        ->where('administrative_dependency', 'state');
                });
            })
            ->orderBy('name')
            ->get();
    }

    /* =========================
     | RBAC helpers
     *=========================*/
    public function hasRole(string $roleName, $scope = null): bool
    {
        $q = $this->roleAssignments()
            ->whereHas('role', fn ($r) => $r->where('name', $roleName));

        if ($scope) {
            $q->where('scope_type', get_class($scope))
                ->where('scope_id', $scope->id);
        } else {
            $q->whereNull('scope_type')->whereNull('scope_id');
        }

        return $q->exists();
    }

    public function assignRole(string $roleName, $scope = null): void
    {
        $role = Role::firstOrCreate(['name' => $roleName]);

        $attrs = [
            'user_id' => $this->id,
            'role_id' => $role->id,
            'scope_type' => $scope ? get_class($scope) : null,
            'scope_id' => $scope ? $scope->id : null,
        ];

        RoleAssignment::firstOrCreate($attrs);
    }

    public function canByRole(string $permissionName, $scope = null): bool
    {
        if ($this->is_master) {
            return true;
        }

        $q = $this->roleAssignments()
            ->whereHas('role.permissions', fn ($p) => $p->where('name', $permissionName));

        if ($scope) {
            $q->where('scope_type', get_class($scope))
                ->where('scope_id', $scope->id);
        } else {
            $q->whereNull('scope_type')->whereNull('scope_id');
        }

        return $q->exists();
    }
}

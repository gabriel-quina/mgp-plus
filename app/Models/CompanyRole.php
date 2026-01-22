<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyRole extends Model
{
    protected $table = 'company_roles';

    protected $fillable = [
        'name',
        'label',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(CompanyRoleAssignment::class, 'company_role_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_role_assignments', 'company_role_id', 'user_id')
            ->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_company_role', 'company_role_id', 'permission_id')
            ->withTimestamps();
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = ['name', 'label'];

    public function companyRoles(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\CompanyRole::class,
            'permission_company_role',
            'permission_id',
            'company_role_id'
        )->withTimestamps();
    }

    public function schoolRoles(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\SchoolRole::class,
            'permission_school_role',
            'permission_id',
            'school_role_id'
        )->withTimestamps();
    }
}


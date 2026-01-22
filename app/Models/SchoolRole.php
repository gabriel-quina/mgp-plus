<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolRole extends Model
{
    protected $table = 'school_roles';

    protected $fillable = [
        'name',
        'label',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(SchoolRoleAssignment::class, 'school_role_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_school_role', 'school_role_id', 'permission_id')
            ->withTimestamps();
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolRoleAssignment extends Model
{
    protected $table = 'school_role_assignments';

    protected $fillable = [
        'user_id',
        'school_role_id',
        'school_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(SchoolRole::class, 'school_role_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}


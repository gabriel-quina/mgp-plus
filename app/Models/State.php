<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class State extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'uf',
    ];

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function roleAssignments(): MorphMany
    {
        return $this->morphMany(RoleAssignment::class, 'scope');
    }

    public function setUfAttribute($value): void
    {
        $this->attributes['uf'] = mb_strtoupper(trim((string) $value));
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = trim((string) $value);
    }
}


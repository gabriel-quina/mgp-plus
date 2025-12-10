<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'uf',
    ];

    public function cities()
    {
        return $this->hasMany(City::class);
    }

    public function roleAssignments()
    {
        return $this->morphMany(RoleAssignment::class, 'scope');
    }
}

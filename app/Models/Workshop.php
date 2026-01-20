<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workshop extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function schoolWorkshops(): HasMany
    {
        return $this->hasMany(SchoolWorkshop::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}


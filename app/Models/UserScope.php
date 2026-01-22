<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserScope extends Model
{
    protected $table = 'user_scopes';

    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'scope', // 'company' | 'school'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}


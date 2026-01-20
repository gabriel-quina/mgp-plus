<?php

namespace App\Models\Abstract;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

abstract class Person extends Model
{
    use HasFactory;

    public const BASE_FILLABLE = [
        'name', 'cpf', 'email', 'birthdate',
    ];

    protected $fillable = self::BASE_FILLABLE;

    protected $casts = [
        'birthdate' => 'date',
    ];

    protected $appends = [
        'display_name',
        'cpf_formatted',
    ];

    public function getDisplayNameAttribute(): string
    {
        return (string)($this->attributes['name'] ?? '');
    }

    public function getCpfFormattedAttribute(): ?string
    {
        $raw = (string)($this->attributes['cpf'] ?? '');
        $digits = preg_replace('/\D+/', '', $raw);
        if (strlen($digits) !== 11) return $raw ?: null;
        return substr($digits,0,3).'.'.substr($digits,3,3).'.'.substr($digits,6,3).'-'.substr($digits,9,2);
    }

    public function setCpfAttribute($value): void
    {
        $this->attributes['cpf'] = preg_replace('/\D+/', '', (string)$value) ?: null;
    }

    public function setEmailAttribute($value): void
    {
        $email = trim((string)$value);
        $this->attributes['email'] = $email !== '' ? mb_strtolower($email) : null;
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = trim((string)$value);
    }

    public function setBirthdateAttribute($value): void
    {
        $this->attributes['birthdate'] = $value ?: null;
    }

    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string)$term);
        if ($term === '') return $query;
        $digits = preg_replace('/\D+/', '', $term);

        return $query->where(function($q) use ($term, $digits){
            $q->where('name','like',"%{$term}%")
              ->orWhere('email','like',"%{$term}%");

            if ($digits !== '') {
                $q->orWhere('cpf','like',"%{$digits}%");
            }
        });
    }

    public function scopeAlphabetical($query)
    {
        return $query->orderBy('name');
    }
}


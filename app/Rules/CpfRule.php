<?php

namespace App\Rules;

use App\Support\Cpf;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CpfRule implements ValidationRule
{
    public function __construct(
        private bool $allowNull = false
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->allowNull && (is_null($value) || trim((string) $value) === '')) {
            return;
        }

        if (! Cpf::isValid((string) $value)) {
            $fail('CPF inválido.');
        }
    }
}

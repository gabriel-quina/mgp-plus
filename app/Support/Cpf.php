<?php

namespace App\Support;

final class Cpf
{
    public static function normalize(?string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        return $digits !== '' ? $digits : null;
    }

    public static function isValid(?string $value): bool
    {
        $cpf = self::normalize($value);

        if (! $cpf || strlen($cpf) !== 11) {
            return false;
        }

        // rejeita sequências iguais (111..., 000..., etc.)
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        $digits = array_map('intval', str_split($cpf));

        // DV1
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += $digits[$i] * (10 - $i);
        }
        $rem = $sum % 11;
        $dv1 = ($rem < 2) ? 0 : 11 - $rem;

        if ($digits[9] !== $dv1) {
            return false;
        }

        // DV2
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += $digits[$i] * (11 - $i);
        }
        $rem = $sum % 11;
        $dv2 = ($rem < 2) ? 0 : 11 - $rem;

        return $digits[10] === $dv2;
    }
}

<?php

namespace App\Support;

class PhoneNumber
{
    public static function normalize(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value) ?? '';

        if (str_starts_with($digits, '0040')) {
            $digits = '0'.substr($digits, 4);
        } elseif (str_starts_with($digits, '40') && strlen($digits) === 11) {
            $digits = '0'.substr($digits, 2);
        } elseif (strlen($digits) === 9 && str_starts_with($digits, '7')) {
            $digits = '0'.$digits;
        }

        if (preg_match('/^07\d{8}$/', $digits) !== 1) {
            return null;
        }

        return $digits;
    }
}

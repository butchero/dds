<?php

namespace App\Support;

class ContactInquiry
{
    /**
     * @return array<string, string>
     */
    public static function departments(): array
    {
        return [
            'vanzari' => __('Vânzări'),
            'suport' => __('Suport tehnic'),
        ];
    }
}

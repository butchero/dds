<?php

namespace App\Support;

use App\Models\SiteSetting;

class ContactChallenge
{
    private const string SESSION_KEY = 'contact_challenge';

    public static function issue(): void
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';

        for ($index = 0; $index < 5; $index++) {
            $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        session([self::SESSION_KEY => $code]);
    }

    public static function matches(string $input): bool
    {
        $code = session(self::SESSION_KEY);

        if (! is_string($code) || $code === '') {
            return false;
        }

        return hash_equals($code, strtoupper(trim($input)));
    }

    public static function svg(): ?string
    {
        $code = session(self::SESSION_KEY);

        if (! is_string($code) || $code === '') {
            return null;
        }

        $glyphs = '';

        foreach (str_split($code) as $index => $character) {
            $x = 14 + ($index * 28);
            $y = ($index % 2) === 0 ? 32 : 36;
            $glyphs .= '<text x="'.$x.'" y="'.$y.'" font-family="monospace" font-size="22" fill="#333333">'.htmlspecialchars($character, ENT_XML1).'</text>';
        }

        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<svg xmlns="http://www.w3.org/2000/svg" width="160" height="48" viewBox="0 0 160 48" role="img">'
            .'<rect width="160" height="48" fill="#f7f7f7"/>'
            .'<line x1="0" y1="24" x2="160" y2="20" stroke="'.htmlspecialchars((string) SiteSetting::get('brand_primary'), ENT_XML1).'" stroke-width="1" opacity="0.35"/>'
            .$glyphs
            .'</svg>';
    }
}

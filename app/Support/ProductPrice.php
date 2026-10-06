<?php

namespace App\Support;

use App\Models\Product;
use App\Models\SiteSetting;

class ProductPrice
{
    private function __construct(
        public string $net,
        public string $gross,
        public string $vatRate,
    ) {}

    public static function from(Product $product): ?self
    {
        if ($product->price === null || $product->price === '') {
            return null;
        }

        $rate = $product->vat_rate !== null && $product->vat_rate !== ''
            ? (string) $product->vat_rate
            : (string) SiteSetting::get('vat_rate');

        return self::quote((string) $product->price, $rate, SiteSetting::get('prices_include_vat'));
    }

    public static function quote(string $entered, string $rate, bool $pricesIncludeVat): self
    {
        $factor = bcadd('1', bcdiv($rate, '100', 6), 6);

        if ($pricesIncludeVat) {
            $gross = self::roundMoney($entered);
            $net = bccomp($factor, '0', 6) === 0
                ? $gross
                : self::roundMoney(bcdiv($entered, $factor, 6));
        } else {
            $net = self::roundMoney($entered);
            $gross = self::roundMoney(bcmul($entered, $factor, 6));
        }

        return new self(
            net: self::format($net),
            gross: self::format($gross),
            vatRate: self::formatRate($rate),
        );
    }

    private static function roundMoney(string $amount): string
    {
        if (! str_contains($amount, '.')) {
            $amount .= '.00';
        }

        if (bccomp($amount, '0', 6) < 0) {
            return bcsub($amount, '0.005', 2);
        }

        return bcadd($amount, '0.005', 2);
    }

    private static function format(string $amount): string
    {
        $negative = bccomp($amount, '0', 2) < 0;
        $absolute = ltrim($amount, '-');
        [$whole, $fraction] = array_pad(explode('.', $absolute, 2), 2, '00');
        $fraction = substr(str_pad($fraction, 2, '0'), 0, 2);
        $wholeWithSeparator = preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', $whole) ?: '0';

        return ($negative ? '-' : '').$wholeWithSeparator.','.$fraction.' lei';
    }

    private static function formatRate(string $rate): string
    {
        $trimmed = rtrim(rtrim(self::roundMoney($rate), '0'), '.');

        if ($trimmed === '' || $trimmed === '-') {
            $trimmed = '0';
        }

        return str_replace('.', ',', $trimmed).'%';
    }
}

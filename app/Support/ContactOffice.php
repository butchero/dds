<?php

namespace App\Support;

use App\Models\SiteSetting;

class ContactOffice
{
    /**
     * @return array{
     *     name: string,
     *     street: string,
     *     city: string,
     *     email: string,
     *     phone: array{display: string, tel: string|null}|null,
     *     fax: string|null,
     *     mobiles: list<array{display: string, tel: string|null}>,
     *     mapEmbed: string|null,
     *     mapLink: string|null,
     *     schema: array<string, mixed>
     * }
     */
    public static function present(): array
    {
        $name = trim((string) SiteSetting::get('contact_company'));
        $street = trim((string) SiteSetting::get('contact_address'));
        $city = trim((string) SiteSetting::get('contact_city'));
        $email = trim((string) SiteSetting::get('contact_email'));
        $phone = self::phone(SiteSetting::get('contact_phone'));
        $fax = self::text(SiteSetting::get('contact_fax'));
        $mobiles = array_values(array_filter([
            self::phone(SiteSetting::get('contact_mobile')),
            self::phone(SiteSetting::get('contact_mobile_alt')),
        ]));
        $latitude = self::coordinate('contact_latitude', 90);
        $longitude = self::coordinate('contact_longitude', 180);
        $query = $latitude !== null && $longitude !== null ? $latitude.','.$longitude : null;
        $telephones = array_values(array_filter([
            $phone['tel'] ?? null,
            ...array_map(fn (array $mobile): ?string => $mobile['tel'], $mobiles),
        ]));

        return [
            'name' => $name,
            'street' => $street,
            'city' => $city,
            'email' => $email,
            'phone' => $phone,
            'fax' => $fax,
            'mobiles' => $mobiles,
            'mapEmbed' => $query === null ? null : 'https://maps.google.com/maps?hl=ro&z=17&output=embed&q='.$query,
            'mapLink' => $query === null ? null : 'https://www.google.com/maps/search/?api=1&query='.$query,
            'schema' => [
                '@context' => 'https://schema.org',
                '@type' => 'LocalBusiness',
                'name' => $name,
                'email' => $email,
                'telephone' => $telephones,
                'faxNumber' => $fax,
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => $street,
                    'addressLocality' => $city,
                    'addressCountry' => 'RO',
                ],
                'geo' => $query === null ? null : [
                    '@type' => 'GeoCoordinates',
                    'latitude' => (float) $latitude,
                    'longitude' => (float) $longitude,
                ],
            ],
        ];
    }

    public static function storedCoordinate(mixed $value): string
    {
        if (is_string($value)) {
            $value = trim(str_replace(',', '.', $value));
        }

        if (is_int($value)) {
            return (string) $value;
        }

        if (is_float($value)) {
            return rtrim(rtrim(sprintf('%.8F', $value), '0'), '.');
        }

        return is_numeric($value) ? trim((string) $value) : '';
    }

    /**
     * @return array{display: string, tel: string|null}|null
     */
    private static function phone(mixed $value): ?array
    {
        $display = self::text($value);

        if ($display === null) {
            return null;
        }

        return [
            'display' => $display,
            'tel' => self::dial($display),
        ];
    }

    private static function text(mixed $value): ?string
    {
        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private static function dial(string $display): ?string
    {
        $digits = preg_replace('/\D+/', '', $display) ?? '';

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            return '+'.substr($digits, 2);
        }

        if (str_starts_with($digits, '0')) {
            return '+40'.substr($digits, 1);
        }

        return '+'.$digits;
    }

    private static function coordinate(string $name, float $limit): ?string
    {
        $value = trim((string) SiteSetting::get($name));

        if (! is_numeric($value)) {
            return null;
        }

        $number = (float) $value;

        if ($number < -$limit || $number > $limit) {
            return null;
        }

        return $value;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Once;
use InvalidArgumentException;

class SiteSetting extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'name';

    protected $keyType = 'string';

    protected $fillable = [
        'name', 'value', 'description',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Once::flush());
        static::deleted(fn () => Once::flush());
    }

    /**
     * @return array<string, array{default: string, cast: 'string'|'decimal'|'boolean', description: string}>
     */
    public static function definitions(): array
    {
        return [
            'menu_position' => [
                'default' => 'top',
                'cast' => 'string',
                'description' => 'Poziția meniului principal pe desktop.',
            ],
            'vat_rate' => [
                'default' => '21',
                'cast' => 'decimal',
                'description' => 'Cota TVA folosită când produsul nu are o cotă proprie.',
            ],
            'prices_include_vat' => [
                'default' => '1',
                'cast' => 'boolean',
                'description' => 'Dacă prețul introdus include deja TVA.',
            ],
            'currency_suffix' => [
                'default' => 'lei',
                'cast' => 'string',
                'description' => 'Sufixul afișat după sumele de preț în catalog.',
            ],
            'sms_sender_prefix' => [
                'default' => 'DDS',
                'cast' => 'string',
                'description' => 'Prefixul din mesajele SMS trimise clienților.',
            ],
            'brand_primary' => [
                'default' => '#fe5013',
                'cast' => 'string',
                'description' => 'Culoarea principală a brandului (hex), folosită în admin.',
            ],
            'founded_year' => [
                'default' => '1995',
                'cast' => 'string',
                'description' => 'Anul înființării firmei, afișat pe prima pagină.',
            ],
            'company_email' => [
                'default' => 'vali.ciuca@gmail.com',
                'cast' => 'string',
                'description' => 'Adresa la care ajung programările și mesajele din formularul de contact.',
            ],
            'contact_company' => [
                'default' => 'SC DDS SERVICES GROUP SRL',
                'cast' => 'string',
                'description' => 'Numele firmei afișat pe pagina de contact.',
            ],
            'contact_address' => [
                'default' => 'Bd. Mamaia nr. 70, bl. Bi 1, parter',
                'cast' => 'string',
                'description' => 'Adresa afișată pe pagina de contact.',
            ],
            'contact_city' => [
                'default' => 'Constanța',
                'cast' => 'string',
                'description' => 'Localitatea afișată pe pagina de contact.',
            ],
            'contact_email' => [
                'default' => 'office@dds-constanta.ro',
                'cast' => 'string',
                'description' => 'E-mailul afișat pe pagina de contact.',
            ],
            'contact_phone' => [
                'default' => '0241-619619',
                'cast' => 'string',
                'description' => 'Telefonul afișat pe pagina de contact.',
            ],
            'contact_fax' => [
                'default' => '0241-619699',
                'cast' => 'string',
                'description' => 'Faxul afișat pe pagina de contact.',
            ],
            'contact_mobile' => [
                'default' => '0722-462289',
                'cast' => 'string',
                'description' => 'Primul mobil afișat pe pagina de contact.',
            ],
            'contact_mobile_alt' => [
                'default' => '0722-462279',
                'cast' => 'string',
                'description' => 'Al doilea mobil afișat pe pagina de contact.',
            ],
            'contact_latitude' => [
                'default' => '44.1878643',
                'cast' => 'string',
                'description' => 'Latitudinea sediului, folosită de harta de pe pagina de contact.',
            ],
            'contact_longitude' => [
                'default' => '28.6507139',
                'cast' => 'string',
                'description' => 'Longitudinea sediului, folosită de harta de pe pagina de contact.',
            ],
            'home_heading' => [
                'default' => 'Centrale termice Constanta - aparate aer conditionat Constanta',
                'cast' => 'string',
                'description' => 'Titlul afișat ca titlu principal pe prima pagină.',
            ],
            'title_suffix' => [
                'default' => 'DDS Services Group',
                'cast' => 'string',
                'description' => 'Numele brandului: sufixul din titlul paginii, admin și mesaje.',
            ],
            'copyright_start_year' => [
                'default' => '2008',
                'cast' => 'string',
                'description' => 'Anul de la care începe copyrightul din subsol.',
            ],
            'meta_description' => [
                'default' => 'DDS Services Group din Constanța comercializează și montează centrale termice și aparate de aer condiționat, cu service și verificări ISCIR.',
                'cast' => 'string',
                'description' => 'Descrierea folosită când pagina nu are un text propriu.',
            ],
            'share_image' => [
                'default' => '',
                'cast' => 'string',
                'description' => 'Imaginea folosită la share când pagina nu are o poză proprie.',
            ],
        ];
    }

    public static function ensureDefaults(): void
    {
        $present = static::query()->pluck('name')->all();

        static::insertMissing(array_values(array_diff(array_keys(static::definitions()), $present)));
    }

    public static function get(string $name): mixed
    {
        $definition = static::definition($name);
        $stored = static::storedValues()[$name] ?? $definition['default'];

        return static::castStored($definition['cast'], $stored);
    }

    public static function set(string $name, mixed $value): void
    {
        $definition = static::definition($name);
        $stored = static::serialize($definition['cast'], $value);
        $setting = static::query()->find($name);

        if ($setting === null) {
            static::query()->create([
                'name' => $name,
                'value' => $stored,
                'description' => $definition['description'],
            ]);

            return;
        }

        $setting->value = $stored;
        $setting->save();
    }

    /**
     * @return array<string, string|null>
     */
    private static function storedValues(): array
    {
        return once(function (): array {
            $rows = static::query()->pluck('value', 'name')->all();
            $missing = array_values(array_diff(array_keys(static::definitions()), array_keys($rows)));

            if ($missing === []) {
                return $rows;
            }

            static::insertMissing($missing);

            return static::query()->pluck('value', 'name')->all();
        });
    }

    /**
     * @param  array<int, string>  $names
     */
    private static function insertMissing(array $names): void
    {
        if ($names === []) {
            return;
        }

        $now = now();
        $rows = [];

        foreach ($names as $name) {
            $definition = static::definitions()[$name];
            $rows[] = [
                'name' => $name,
                'value' => $definition['default'],
                'description' => $definition['description'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        static::query()->insert($rows);
    }

    /**
     * @return array{default: string, cast: 'string'|'decimal'|'boolean', description: string}
     */
    private static function definition(string $name): array
    {
        return static::definitions()[$name] ?? throw new InvalidArgumentException("Unknown site setting [{$name}].");
    }

    /**
     * @param  'string'|'decimal'|'boolean'  $cast
     */
    private static function castStored(string $cast, ?string $stored): mixed
    {
        return match ($cast) {
            'boolean' => $stored === '1',
            'decimal' => static::decimalString($stored, '0'),
            default => (string) $stored,
        };
    }

    /**
     * @param  'string'|'decimal'|'boolean'  $cast
     */
    private static function serialize(string $cast, mixed $value): string
    {
        return match ($cast) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0',
            'decimal' => static::decimalString($value, '0'),
            default => (string) $value,
        };
    }

    private static function decimalString(mixed $value, string $fallback): string
    {
        if ($value === null || $value === '') {
            return $fallback;
        }

        $string = trim((string) $value);

        if (! is_numeric($string)) {
            return $fallback;
        }

        if (str_contains($string, '.')) {
            $string = rtrim(rtrim($string, '0'), '.');
        }

        return $string === '' || $string === '-' ? '0' : $string;
    }
}

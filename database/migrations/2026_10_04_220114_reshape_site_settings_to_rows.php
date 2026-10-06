<?php

use App\Models\SiteSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $existing = DB::table('site_settings')->orderBy('id')->first();
        $definitions = SiteSetting::definitions();

        Schema::drop('site_settings');

        Schema::create('site_settings', function (Blueprint $table) {
            $table->string('name')->primary();
            $table->text('value')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        $now = now();
        $values = [
            'menu_position' => $existing?->menu_position ?? $definitions['menu_position']['default'],
            'vat_rate' => $this->decimal($existing?->vat_rate, $definitions['vat_rate']['default']),
            'prices_include_vat' => $this->boolean($existing?->prices_include_vat, $definitions['prices_include_vat']['default']),
        ];

        DB::table('site_settings')->insert(array_map(
            fn (string $name) => [
                'name' => $name,
                'value' => $values[$name],
                'description' => $definitions[$name]['description'],
                'created_at' => $now,
                'updated_at' => $now,
            ],
            array_keys($values),
        ));
    }

    public function down(): void
    {
        $rows = DB::table('site_settings')->pluck('value', 'name');

        Schema::drop('site_settings');

        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('menu_position')->default('top');
            $table->decimal('vat_rate', 5, 2)->default(21);
            $table->boolean('prices_include_vat')->default(true);
            $table->timestamps();
        });

        $now = now();

        DB::table('site_settings')->insert([
            'menu_position' => $rows['menu_position'] ?? 'top',
            'vat_rate' => $rows['vat_rate'] ?? 21,
            'prices_include_vat' => ($rows['prices_include_vat'] ?? '1') === '1',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function decimal(mixed $value, string $default): string
    {
        if ($value === null || $value === '') {
            return $default;
        }

        $string = trim((string) $value);

        if (! is_numeric($string)) {
            return $default;
        }

        if (str_contains($string, '.')) {
            $string = rtrim(rtrim($string, '0'), '.');
        }

        return $string === '' ? '0' : $string;
    }

    private function boolean(mixed $value, string $default): string
    {
        if ($value === null || $value === '') {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
    }
};

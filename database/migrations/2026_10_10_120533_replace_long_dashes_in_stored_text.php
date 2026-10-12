<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [
            'products' => ['name', 'excerpt', 'description'],
            'product_categories' => ['name', 'description'],
            'news' => ['title', 'excerpt', 'blocks'],
            'services' => ['name', 'blocks'],
            'service_categories' => ['name'],
            'site_settings' => ['value', 'description'],
            'manufacturers' => ['name'],
            'banners' => ['title'],
            'menu_items' => ['label'],
            'attributes' => ['name', 'unit'],
            'attribute_options' => ['label'],
            'product_attribute_values' => ['value_text'],
            'appointments' => ['note'],
            'users' => ['name'],
        ];

        foreach ($columns as $table => $fields) {
            foreach ($fields as $field) {
                DB::table($table)->update([
                    $field => DB::raw("REPLACE(REPLACE(`{$field}`, UNHEX('E28094'), '-'), UNHEX('E28093'), '-')"),
                ]);
            }
        }
    }

    public function down(): void {}
};

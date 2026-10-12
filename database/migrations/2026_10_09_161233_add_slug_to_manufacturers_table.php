<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manufacturers', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        $used = [];

        foreach (DB::table('manufacturers')->orderBy('id')->get() as $manufacturer) {
            $base = Str::slug((string) $manufacturer->name);
            $base = $base !== '' ? $base : 'producator';
            $slug = $base;
            $suffix = 2;

            while (isset($used[$slug])) {
                $slug = $base.'-'.$suffix;
                $suffix++;
            }

            $used[$slug] = true;

            DB::table('manufacturers')->where('id', $manufacturer->id)->update([
                'slug' => $slug,
            ]);
        }

        Schema::table('manufacturers', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('manufacturers', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};

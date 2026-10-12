<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manufacturers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('logo')->nullable();
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('manufacturer_id')->nullable()->after('image')->constrained()->nullOnDelete();
        });

        $names = DB::table('products')
            ->whereNotNull('manufacturer')
            ->where('manufacturer', '!=', '')
            ->distinct()
            ->orderBy('manufacturer')
            ->pluck('manufacturer');

        foreach ($names as $name) {
            $id = DB::table('manufacturers')->insertGetId([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('products')->where('manufacturer', $name)->update([
                'manufacturer_id' => $id,
            ]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('manufacturer');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('manufacturer')->nullable()->after('image');
        });

        $names = DB::table('manufacturers')->pluck('name', 'id');

        foreach ($names as $id => $name) {
            DB::table('products')->where('manufacturer_id', $id)->update([
                'manufacturer' => $name,
            ]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('manufacturer_id');
        });

        Schema::dropIfExists('manufacturers');
    }
};

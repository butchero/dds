<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->decimal('vat_rate', 5, 2)->default(21)->after('menu_position');
            $table->boolean('prices_include_vat')->default(true)->after('vat_rate');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->nullable()->after('manufacturer');
            $table->unsignedInteger('quantity')->nullable()->after('price');
            $table->decimal('vat_rate', 5, 2)->nullable()->after('quantity');
        });

        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type');
            $table->string('unit')->nullable();
            $table->boolean('is_filterable')->default(false);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('attribute_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attribute_id');
            $table->string('label');
            $table->string('slug');
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->foreign('attribute_id')->references('id')->on('attributes')->cascadeOnDelete();
            $table->unique(['attribute_id', 'slug']);
        });

        Schema::create('attribute_product_category', function (Blueprint $table) {
            $table->unsignedBigInteger('attribute_id');
            $table->foreignId('product_category_id')->constrained()->cascadeOnDelete();

            $table->foreign('attribute_id')->references('id')->on('attributes')->cascadeOnDelete();
            $table->primary(['attribute_id', 'product_category_id']);
        });

        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->text('value_text')->nullable();
            $table->decimal('value_number', 12, 2)->nullable();
            $table->boolean('value_boolean')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->unique(['product_id', 'attribute_id']);
        });

        Schema::create('product_attribute_options', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id');
            $table->foreignId('attribute_option_id')->constrained()->cascadeOnDelete();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->primary(['product_id', 'attribute_option_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attribute_options');
        Schema::dropIfExists('product_attribute_values');
        Schema::dropIfExists('attribute_product_category');
        Schema::dropIfExists('attribute_options');
        Schema::dropIfExists('attributes');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['price', 'quantity', 'vat_rate']);
        });

        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['vat_rate', 'prices_include_vat']);
        });
    }
};

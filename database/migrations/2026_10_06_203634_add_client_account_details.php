<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
            $table->unique('phone');
        });

        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider');
            $table->string('provider_id');
            $table->timestamps();

            $table->unique(['provider', 'provider_id']);
        });

        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->string('county');
            $table->string('city');
            $table->string('street');
            $table->string('street_number');
            $table->string('postal_code')->nullable();
            $table->string('details')->nullable();
            $table->timestamps();
        });

        Schema::create('billing_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('company_name')->nullable();
            $table->string('cui')->nullable();
            $table->string('registration_number')->nullable();
            $table->boolean('is_vat_payer')->default(false);
            $table->string('county');
            $table->string('city');
            $table->string('street');
            $table->string('street_number');
            $table->string('postal_code')->nullable();
            $table->string('details')->nullable();
            $table->string('bank')->nullable();
            $table->string('iban')->nullable();
            $table->timestamps();
        });

        Schema::table('equipment', function (Blueprint $table) {
            $table->foreignId('address_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->boolean('notify_email')->default(false)->after('interval_months');
            $table->boolean('notify_sms')->default(false)->after('notify_email');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('address_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->foreignId('billing_profile_id')->nullable()->after('address_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('billing_profile_id');
            $table->dropConstrainedForeignId('address_id');
        });

        Schema::table('equipment', function (Blueprint $table) {
            $table->dropConstrainedForeignId('address_id');
            $table->dropColumn(['notify_email', 'notify_sms']);
        });

        Schema::dropIfExists('billing_profiles');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('social_accounts');

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['phone']);
            $table->string('email')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
        });
    }
};

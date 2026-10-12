<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('menu_items')->where('url', '/servicii/contact')->update(['url' => '/contact']);
    }

    public function down(): void
    {
        DB::table('menu_items')->where('url', '/contact')->update(['url' => '/servicii/contact']);
    }
};

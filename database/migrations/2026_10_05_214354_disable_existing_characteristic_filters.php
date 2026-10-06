<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('attributes')->update(['is_filterable' => false]);
    }

    public function down(): void
    {
        //
    }
};

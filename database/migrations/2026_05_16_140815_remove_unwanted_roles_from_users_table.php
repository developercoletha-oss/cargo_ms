<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereIn('role', ['manager', 'staff', 'user'])
            ->update(['role' => 'customer']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data cleanup migration is intentionally irreversible.
    }
};

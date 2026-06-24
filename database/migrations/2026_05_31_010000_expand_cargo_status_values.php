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
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE cargo MODIFY status ENUM('pending', 'approved', 'disapproved', 'in_transit', 'arrived', 'delivered') NOT NULL DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE cargo MODIFY status ENUM('pending', 'approved', 'disapproved') NOT NULL DEFAULT 'pending'");
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE cargo MODIFY status ENUM('pending', 'approved', 'disapproved', 'in_transit', 'arrived_regional_hub', 'arrived', 'delivered') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::table('cargo')
            ->where('status', 'arrived_regional_hub')
            ->update(['status' => 'in_transit']);

        DB::statement("ALTER TABLE cargo MODIFY status ENUM('pending', 'approved', 'disapproved', 'in_transit', 'arrived', 'delivered') NOT NULL DEFAULT 'pending'");
    }
};

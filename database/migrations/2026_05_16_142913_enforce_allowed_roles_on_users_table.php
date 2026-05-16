<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $allowedRoles = ['admin', 'manager', 'customer', 'transporter', 'store_keeper'];

        DB::table('users')
            ->whereNotIn('role', $allowedRoles)
            ->update(['role' => 'customer']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE users
                MODIFY role ENUM('admin', 'manager', 'customer', 'transporter', 'store_keeper')
                NOT NULL DEFAULT 'customer'
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE users
                MODIFY role VARCHAR(255)
                NOT NULL DEFAULT 'customer'
            ");
        }
    }
};

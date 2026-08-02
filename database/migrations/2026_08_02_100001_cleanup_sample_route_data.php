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
        // Delete all location updates with source = 'sample_route'
        DB::table('cargo_location_updates')->where('source', 'sample_route')->delete();

        // Clear cargo.current_location_city where it contains 'Sample route point'
        DB::table('cargo')
            ->where('current_location_city', 'like', '%Sample route point%')
            ->update(['current_location_city' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};

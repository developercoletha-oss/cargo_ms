<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cargo', function (Blueprint $table) {
            $table->string('current_location_city')->nullable()->after('destination_address');
            $table->decimal('current_location_lat', 10, 7)->nullable()->after('current_location_city');
            $table->decimal('current_location_lng', 10, 7)->nullable()->after('current_location_lat');
            $table->timestamp('current_location_updated_at')->nullable()->after('current_location_lng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cargo', function (Blueprint $table) {
            $table->dropColumn([
                'current_location_city',
                'current_location_lat',
                'current_location_lng',
                'current_location_updated_at',
            ]);
        });
    }
};

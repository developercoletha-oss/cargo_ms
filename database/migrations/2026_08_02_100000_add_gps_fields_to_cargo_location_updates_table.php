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
        Schema::table('cargo_location_updates', function (Blueprint $table) {
            $table->decimal('speed', 6, 2)->nullable()->after('longitude');
            $table->decimal('heading', 5, 2)->nullable()->after('speed');
            $table->string('geocoded_name', 255)->nullable()->after('location_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cargo_location_updates', function (Blueprint $table) {
            $table->dropColumn(['speed', 'heading', 'geocoded_name']);
        });
    }
};

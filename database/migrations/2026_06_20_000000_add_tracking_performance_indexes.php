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
            $table->index(['customer_id', 'status', 'created_at'], 'cargo_customer_status_created_idx');
            $table->index(['transport_staff_id', 'status', 'created_at'], 'cargo_transport_status_created_idx');
            $table->index(['status', 'current_location_updated_at'], 'cargo_status_location_updated_idx');
            $table->index(['origin_city', 'status'], 'cargo_origin_status_idx');
            $table->index(['destination_city', 'status'], 'cargo_destination_status_idx');
            $table->index(['current_location_lat', 'current_location_lng'], 'cargo_current_coordinates_idx');
        });

        Schema::table('cargo_details', function (Blueprint $table) {
            $table->index('cargo_type', 'cargo_details_type_idx');
        });

        Schema::table('cargo_location_updates', function (Blueprint $table) {
            $table->index(['source', 'recorded_at'], 'cargo_location_source_recorded_idx');
        });

        Schema::table('transport_staff', function (Blueprint $table) {
            $table->index(['is_active', 'user_id'], 'transport_staff_active_user_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['role', 'is_active'], 'users_role_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_active_idx');
        });

        Schema::table('transport_staff', function (Blueprint $table) {
            $table->dropIndex('transport_staff_active_user_idx');
        });

        Schema::table('cargo_location_updates', function (Blueprint $table) {
            $table->dropIndex('cargo_location_source_recorded_idx');
        });

        Schema::table('cargo_details', function (Blueprint $table) {
            $table->dropIndex('cargo_details_type_idx');
        });

        Schema::table('cargo', function (Blueprint $table) {
            $table->dropIndex('cargo_customer_status_created_idx');
            $table->dropIndex('cargo_transport_status_created_idx');
            $table->dropIndex('cargo_status_location_updated_idx');
            $table->dropIndex('cargo_origin_status_idx');
            $table->dropIndex('cargo_destination_status_idx');
            $table->dropIndex('cargo_current_coordinates_idx');
        });
    }
};

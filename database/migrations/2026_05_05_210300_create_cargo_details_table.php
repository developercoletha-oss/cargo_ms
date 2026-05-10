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
        Schema::create('cargo_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cargo_id')->constrained('cargo')->cascadeOnDelete();
            $table->string('description');
            $table->string('cargo_type')->nullable();
            $table->decimal('weight_kg', 10, 2)->default(0);
            $table->decimal('volume_cbm', 10, 2)->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('package_count')->default(1);
            $table->decimal('estimated_value', 12, 2)->nullable();
            $table->boolean('is_fragile')->default(false);
            $table->boolean('is_hazardous')->default(false);
            $table->text('special_instructions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cargo_details');
    }
};

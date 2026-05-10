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
        Schema::create('cargo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('transport_staff_id')->nullable()->constrained('transport_staff')->nullOnDelete();
            $table->string('origin_country', 2);
            $table->string('origin_city');
            $table->string('origin_address')->nullable();
            $table->string('destination_country', 2);
            $table->string('destination_city');
            $table->string('destination_address')->nullable();
            $table->date('pickup_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->enum('status', ['pending', 'approved', 'disapproved'])->default('pending');
            $table->text('approval_note')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('disapproved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('disapproved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cargo');
    }
};

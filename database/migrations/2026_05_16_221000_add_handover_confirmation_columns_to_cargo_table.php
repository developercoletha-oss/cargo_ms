<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cargo', function (Blueprint $table) {
            $table->foreignId('handover_confirmed_by')->nullable()->after('signed_at')->constrained('users')->nullOnDelete();
            $table->timestamp('handover_confirmed_at')->nullable()->after('handover_confirmed_by');
        });
    }

    public function down(): void
    {
        Schema::table('cargo', function (Blueprint $table) {
            $table->dropConstrainedForeignId('handover_confirmed_by');
            $table->dropColumn('handover_confirmed_at');
        });
    }
};

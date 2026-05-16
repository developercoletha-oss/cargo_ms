<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cargo', function (Blueprint $table) {
            $table->foreignId('signed_by_transporter')->nullable()->after('disapproved_at')->constrained('users')->nullOnDelete();
            $table->timestamp('signed_at')->nullable()->after('signed_by_transporter');
        });
    }

    public function down(): void
    {
        Schema::table('cargo', function (Blueprint $table) {
            $table->dropConstrainedForeignId('signed_by_transporter');
            $table->dropColumn('signed_at');
        });
    }
};

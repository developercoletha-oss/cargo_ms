<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cargo', function (Blueprint $table) {
            $table->string('tracking_number')->nullable()->unique()->after('id');
        });

        DB::table('cargo')
            ->whereNull('tracking_number')
            ->get()
            ->each(function (object $cargo) {
                DB::table('cargo')
                    ->where('id', $cargo->id)
                    ->update([
                        'tracking_number' => $this->generateTrackingNumber(),
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cargo', function (Blueprint $table) {
            $table->dropUnique(['tracking_number']);
            $table->dropColumn('tracking_number');
        });
    }

    private function generateTrackingNumber(): string
    {
        do {
            $trackingNumber = 'CFTMS-' . now()->format('Y') . '-' . strtoupper(Str::random(8));
        } while (DB::table('cargo')->where('tracking_number', $trackingNumber)->exists());

        return $trackingNumber;
    }
};

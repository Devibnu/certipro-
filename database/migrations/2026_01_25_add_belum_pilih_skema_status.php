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
        // No schema changes needed - just adding new ENUM value to existing status column
        // Status 'BELUM_PILIH_SKEMA' is already supported by the existing VARCHAR column
        
        // Update existing NULL skema_sertifikasi_id records to BELUM_PILIH_SKEMA status
        DB::table('pendaftaran_sertifikasi')
            ->whereNull('skema_sertifikasi_id')
            ->where('status', '!=', 'BELUM_PILIH_SKEMA')
            ->update(['status' => 'BELUM_PILIH_SKEMA']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert BELUM_PILIH_SKEMA back to draft
        DB::table('pendaftaran_sertifikasi')
            ->where('status', 'BELUM_PILIH_SKEMA')
            ->update(['status' => 'draft']);
    }
};

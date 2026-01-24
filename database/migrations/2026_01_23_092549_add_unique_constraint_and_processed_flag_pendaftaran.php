<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * TUJUAN: Prevent duplicate pendaftaran sertifikasi dari 1 pra-pendaftaran
     * 
     * DATABASE CONSTRAINTS:
     * 1. UNIQUE constraint pada pra_pendaftaran_id
     * 2. Flag is_processed untuk tracking
     * 3. Indexes untuk performa query
     */
    public function up(): void
    {
        // 1. Add UNIQUE constraint ke pendaftaran_sertifikasi
        Schema::table('pendaftaran_sertifikasi', function (Blueprint $table) {
            // CRITICAL: Enforce 1 pra_pendaftaran = 1 pendaftaran
            $table->unique('pra_pendaftaran_id', 'unique_pra_pendaftaran_id');
            
            // Performance indexes
            $table->index('status');
            $table->index(['pra_pendaftaran_id', 'status'], 'idx_pra_status');
        });

        // 2. Add processed tracking ke pra_pendaftaran
        Schema::table('pra_pendaftaran', function (Blueprint $table) {
            $table->boolean('is_processed')->default(false)->after('status');
            $table->timestamp('processed_at')->nullable()->after('is_processed');
            
            // Performance index
            $table->index('is_processed');
            $table->index(['status', 'is_processed'], 'idx_status_processed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pendaftaran_sertifikasi', function (Blueprint $table) {
            $table->dropUnique('unique_pra_pendaftaran_id');
            $table->dropIndex(['status']);
            $table->dropIndex('idx_pra_status');
        });

        Schema::table('pra_pendaftaran', function (Blueprint $table) {
            $table->dropColumn(['is_processed', 'processed_at']);
            $table->dropIndex(['is_processed']);
            $table->dropIndex('idx_status_processed');
        });
    }
};

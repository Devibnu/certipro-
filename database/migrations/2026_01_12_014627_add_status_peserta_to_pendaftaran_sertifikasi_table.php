<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan kolom status_peserta untuk transparansi status
     * kepada peserta sesuai standar BNSP & ISO 17024
     */
    public function up(): void
    {
        Schema::table('pendaftaran_sertifikasi', function (Blueprint $table) {
            // Status peserta (public-facing status)
            $table->string('status_peserta', 50)->default('DALAM_PROSES')->after('status');
            
            // Pesan status untuk peserta
            $table->text('pesan_status_peserta')->nullable()->after('status_peserta');
            
            // Timestamp terakhir status peserta diperbarui
            $table->timestamp('status_peserta_updated_at')->nullable()->after('pesan_status_peserta');
            
            // Index untuk pencarian publik
            $table->index(['nomor_pendaftaran', 'email'], 'idx_public_status_search');
            $table->index('status_peserta', 'idx_status_peserta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pendaftaran_sertifikasi', function (Blueprint $table) {
            $table->dropIndex('idx_public_status_search');
            $table->dropIndex('idx_status_peserta');
            $table->dropColumn(['status_peserta', 'pesan_status_peserta', 'status_peserta_updated_at']);
        });
    }
};

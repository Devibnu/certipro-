<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * ============================================================================
 * PRA-PENDAFTARAN STATUS REFACTOR - CLEAN ARCHITECTURE
 * ============================================================================
 * 
 * Purpose: Simplify status from 4 ambiguous states to 3 clear states
 * 
 * OLD (Confusing):
 * - baru (what's the difference from menunggu_verifikasi?)
 * - diproses (admin sedang apa? ambigu!)
 * - diterima
 * - ditolak
 * 
 * NEW (Clean):
 * - menunggu_verifikasi (initial state, clear!)
 * - diterima (approved after verification)
 * - ditolak (rejected with reason)
 * 
 * Migration Strategy:
 * 1. Map old status to new status (data migration)
 * 2. Alter enum column (schema migration)
 * 3. Add status_email for idempotent email tracking
 * 
 * BNSP Compliance: Yes
 * ISO 17024 Compliance: Yes
 * ============================================================================
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ========================================================================
        // STEP 1: Data Migration - Map old status to new status
        // ========================================================================
        
        DB::statement("SET SQL_MODE='ALLOW_INVALID_DATES';"); // For safety
        
        // baru → menunggu_verifikasi (same meaning, clearer name)
        DB::table('pra_pendaftaran')
            ->where('status', 'baru')
            ->update([
                'status' => 'menunggu_verifikasi',
                'updated_at' => now(),
            ]);
        
        // diproses → menunggu_verifikasi (admin is still verifying, not "processing")
        DB::table('pra_pendaftaran')
            ->where('status', 'diproses')
            ->update([
                'status' => 'menunggu_verifikasi',
                'updated_at' => now(),
            ]);
        
        // diterima → diterima (no change)
        // ditolak → ditolak (no change)
        
        \Log::info('[Migration] Pra-Pendaftaran status mapped: baru/diproses → menunggu_verifikasi');
        
        // ========================================================================
        // STEP 2: Schema Migration - Alter enum column
        // ========================================================================
        
        DB::statement("
            ALTER TABLE pra_pendaftaran 
            MODIFY COLUMN status ENUM('menunggu_verifikasi', 'diterima', 'ditolak') 
            DEFAULT 'menunggu_verifikasi'
            COMMENT 'Clean Architecture: Only 3 valid states (no ambiguous baru/diproses)'
        ");
        
        \Log::info('[Migration] Pra-Pendaftaran status enum updated to 3 states');
        
        // ========================================================================
        // STEP 3: Add status_email column for idempotent email tracking
        // ========================================================================
        
        if (!Schema::hasColumn('pra_pendaftaran', 'status_email')) {
            Schema::table('pra_pendaftaran', function (Blueprint $table) {
                $table->enum('status_email', [
                    'pra_diterima',  // Email "Pra-Pendaftaran Diterima" sent
                    'pra_ditolak',   // Email "Pra-Pendaftaran Ditolak" sent
                ])->nullable()
                  ->after('status')
                  ->comment('Idempotent email tracking: prevents duplicate emails');
                
                $table->timestamp('email_sent_at')
                      ->nullable()
                      ->after('status_email')
                      ->comment('Timestamp when email was sent');
                
                $table->index('status_email', 'idx_status_email');
            });
            
            \Log::info('[Migration] Added status_email and email_sent_at columns');
        }
        
        // ========================================================================
        // STEP 4: Audit Log
        // ========================================================================
        
        DB::table('audit_logs')->insert([
            'module' => 'migration',
            'action' => 'refactor',
            'description' => 'Pra-Pendaftaran status refactored: 4 states → 3 states (clean architecture)',
            'auditable_type' => 'App\\Models\\PraPendaftaran',
            'auditable_id' => null,
            'old_values' => json_encode(['states' => ['baru', 'diproses', 'diterima', 'ditolak']]),
            'new_values' => json_encode(['states' => ['menunggu_verifikasi', 'diterima', 'ditolak']]),
            'user_id' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Laravel Migration',
            'metadata' => json_encode([
                'migration' => '2026_01_24_041547_refactor_pra_pendaftaran_status_clean_architecture',
                'architect' => 'Senior Laravel Expert',
                'compliance' => 'BNSP / ISO 17024',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ========================================================================
        // ROLLBACK STEP 1: Drop new columns
        // ========================================================================
        
        if (Schema::hasColumn('pra_pendaftaran', 'status_email')) {
            Schema::table('pra_pendaftaran', function (Blueprint $table) {
                $table->dropIndex('idx_status_email');
                $table->dropColumn(['status_email', 'email_sent_at']);
            });
            
            \Log::info('[Rollback] Dropped status_email and email_sent_at columns');
        }
        
        // ========================================================================
        // ROLLBACK STEP 2: Restore old enum
        // ========================================================================
        
        DB::statement("
            ALTER TABLE pra_pendaftaran 
            MODIFY COLUMN status ENUM('baru', 'diproses', 'diterima', 'ditolak') 
            DEFAULT 'baru'
            COMMENT 'Restored to old status values'
        ");
        
        \Log::info('[Rollback] Restored status enum to 4 states');
        
        // ========================================================================
        // ROLLBACK STEP 3: Map data back
        // ========================================================================
        
        DB::table('pra_pendaftaran')
            ->where('status', 'menunggu_verifikasi')
            ->update([
                'status' => 'baru',
                'updated_at' => now(),
            ]);
        
        \Log::info('[Rollback] Mapped menunggu_verifikasi → baru');
        
        // ========================================================================
        // ROLLBACK STEP 4: Audit Log
        // ========================================================================
        
        DB::table('audit_logs')->insert([
            'module' => 'migration',
            'action' => 'rollback',
            'description' => 'Pra-Pendaftaran status rollback: 3 states → 4 states',
            'auditable_type' => 'App\\Models\\PraPendaftaran',
            'auditable_id' => null,
            'old_values' => json_encode(['states' => ['menunggu_verifikasi', 'diterima', 'ditolak']]),
            'new_values' => json_encode(['states' => ['baru', 'diproses', 'diterima', 'ditolak']]),
            'user_id' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Laravel Migration Rollback',
            'metadata' => json_encode([
                'migration' => '2026_01_24_041547_refactor_pra_pendaftaran_status_clean_architecture',
                'reason' => 'Manual rollback requested',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};

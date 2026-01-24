<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add lock mechanism fields to enforce state machine immutability:
     * - asesmen: Lock asesmen data after "Simpan Asesmen"
     * - keputusan_sertifikasi: Lock keputusan data immediately on create
     * - pendaftaran_sertifikasi: Lock status changes after FINAL state
     * 
     * Also add email tracking fields for idempotent email delivery
     */
    public function up(): void
    {
        // ===============================================
        // 1. ASESMEN TABLE: Lock asesmen data
        // ===============================================
        Schema::table('asesmen', function (Blueprint $table) {
            // Lock mechanism
            $table->boolean('is_locked')->default(false)->after('status');
            $table->timestamp('locked_at')->nullable()->after('is_locked');
            $table->unsignedBigInteger('locked_by')->nullable()->after('locked_at');
            
            // Foreign key for locked_by (asesor who locked)
            $table->foreign('locked_by')->references('id')->on('users')->onDelete('set null');
            
            // Index for quick lock checks
            $table->index('is_locked');
        });
        
        // ===============================================
        // 2. KEPUTUSAN_SERTIFIKASI TABLE: Add lock timestamp
        // ===============================================
        Schema::table('keputusan_sertifikasi', function (Blueprint $table) {
            // Note: is_locked already exists, just add timestamp & locked_by
            $table->timestamp('locked_at')->nullable()->after('is_locked');
            $table->unsignedBigInteger('locked_by')->nullable()->after('locked_at');
            
            // Foreign key for locked_by (komite teknis who locked)
            $table->foreign('locked_by')->references('id')->on('users')->onDelete('set null');
            
            // Index for quick lock checks
            $table->index('is_locked');
        });
        
        // ===============================================
        // 3. PENDAFTARAN_SERTIFIKASI TABLE: Status lock + Email tracking
        // ===============================================
        Schema::table('pendaftaran_sertifikasi', function (Blueprint $table) {
            // Status lock mechanism (prevent FINAL state changes)
            $table->boolean('is_status_locked')->default(false)->after('status');
            $table->timestamp('status_locked_at')->nullable()->after('is_status_locked');
            
            // Email tracking for idempotent delivery
            $table->boolean('email_status_asesmen_selesai')->default(false)->after('status_locked_at');
            $table->timestamp('email_asesmen_sent_at')->nullable()->after('email_status_asesmen_selesai');
            
            $table->boolean('email_status_keputusan_sent')->default(false)->after('email_asesmen_sent_at');
            $table->timestamp('email_keputusan_sent_at')->nullable()->after('email_status_keputusan_sent');
            
            // Indexes for quick checks
            $table->index('is_status_locked');
            $table->index('email_status_asesmen_selesai');
            $table->index('email_status_keputusan_sent');
        });
        
        // ===============================================
        // 4. UPDATE EXISTING DATA (if any asesmen exists)
        // ===============================================
        // Any existing asesmen with status 'selesai' should be locked
        DB::table('asesmen')
            ->where('status', 'selesai')
            ->update([
                'is_locked' => true,
                'locked_at' => now(),
                'locked_by' => DB::raw('asesor_id'), // Lock by the asesor who created it
            ]);
        
        // Any existing keputusan should be locked
        DB::table('keputusan_sertifikasi')
            ->where('is_locked', true)
            ->update([
                'locked_at' => now(),
                'locked_by' => DB::raw('ditetapkan_oleh'), // Lock by the komite who created it
            ]);
        
        // Any existing pendaftaran with FINAL status should be locked
        DB::table('pendaftaran_sertifikasi')
            ->whereIn('status', ['kompeten_final', 'belum_kompeten_final'])
            ->update([
                'is_status_locked' => true,
                'status_locked_at' => DB::raw('updated_at'),
            ]);
        
        // ===============================================
        // 5. AUDIT LOG ENTRY
        // ===============================================
        DB::table('audit_logs')->insert([
            'audit_module' => 'system',
            'entity_id' => null,
            'entity_type' => 'migration',
            'action' => 'migrate',
            'user_id' => 1, // System
            'description' => 'Add lock mechanism to asesmen, keputusan_sertifikasi, and pendaftaran_sertifikasi tables (State Machine implementation)',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Laravel Migrator',
            'additional_data' => json_encode([
                'migration' => '2026_01_24_043007_add_lock_mechanism_to_asesmen_and_keputusan_tables',
                'fields_added' => [
                    'asesmen' => ['is_locked', 'locked_at', 'locked_by'],
                    'keputusan_sertifikasi' => ['locked_at', 'locked_by'],
                    'pendaftaran_sertifikasi' => ['is_status_locked', 'status_locked_at', 'email_status_asesmen_selesai', 'email_asesmen_sent_at', 'email_status_keputusan_sent', 'email_keputusan_sent_at'],
                ],
                'compliance' => 'ISO 17024, BNSP',
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
        Schema::table('pendaftaran_sertifikasi', function (Blueprint $table) {
            $table->dropIndex(['is_status_locked']);
            $table->dropIndex(['email_status_asesmen_selesai']);
            $table->dropIndex(['email_status_keputusan_sent']);
            
            $table->dropColumn([
                'is_status_locked',
                'status_locked_at',
                'email_status_asesmen_selesai',
                'email_asesmen_sent_at',
                'email_status_keputusan_sent',
                'email_keputusan_sent_at',
            ]);
        });
        
        Schema::table('keputusan_sertifikasi', function (Blueprint $table) {
            $table->dropForeign(['locked_by']);
            $table->dropIndex(['is_locked']);
            
            $table->dropColumn([
                'locked_at',
                'locked_by',
            ]);
        });
        
        Schema::table('asesmen', function (Blueprint $table) {
            $table->dropForeign(['locked_by']);
            $table->dropIndex(['is_locked']);
            
            $table->dropColumn([
                'is_locked',
                'locked_at',
                'locked_by',
            ]);
        });
    }
};

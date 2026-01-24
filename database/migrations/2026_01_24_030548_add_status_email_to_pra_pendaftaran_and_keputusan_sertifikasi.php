<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================================
 * Migration: Add status_email Field for Idempotent Email Sending
 * ============================================================================
 * 
 * Purpose:
 * - Prevent duplicate email sending for the same status change
 * - Track email delivery status per record
 * 
 * Tables Modified:
 * - pra_pendaftaran (add status_email)
 * - keputusan_sertifikasi (add status_email)
 * 
 * Values:
 * - NULL = belum pernah kirim email
 * - 'pending' = sedang dalam queue
 * - 'sent' = berhasil terkirim
 * - 'failed' = gagal setelah max retry
 * 
 * Compliance: BNSP, ISO 17024, EVENT_DRIVEN_EMAIL_ARCHITECTURE.md
 * ============================================================================
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add status_email to pra_pendaftaran table
        Schema::table('pra_pendaftaran', function (Blueprint $table) {
            $table->enum('status_email', ['pending', 'sent', 'failed'])
                ->nullable()
                ->after('status')
                ->comment('Email delivery status for idempotent check');
            
            $table->timestamp('email_sent_at')
                ->nullable()
                ->after('status_email')
                ->comment('Timestamp when email was successfully sent');
        });

        // Add status_email to keputusan_sertifikasi table
        Schema::table('keputusan_sertifikasi', function (Blueprint $table) {
            $table->enum('status_email', ['pending', 'sent', 'failed'])
                ->nullable()
                ->after('keputusan')
                ->comment('Email delivery status for idempotent check');
            
            $table->timestamp('email_sent_at')
                ->nullable()
                ->after('status_email')
                ->comment('Timestamp when email was successfully sent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pra_pendaftaran', function (Blueprint $table) {
            $table->dropColumn(['status_email', 'email_sent_at']);
        });

        Schema::table('keputusan_sertifikasi', function (Blueprint $table) {
            $table->dropColumn(['status_email', 'email_sent_at']);
        });
    }
};

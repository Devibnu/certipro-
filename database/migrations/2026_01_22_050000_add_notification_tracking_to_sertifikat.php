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
        Schema::table('sertifikat', function (Blueprint $table) {
            // Email notification tracking
            $table->timestamp('email_sent_at')->nullable()->after('file_pdf');
            $table->timestamp('email_failed_at')->nullable()->after('email_sent_at');
            $table->text('email_error')->nullable()->after('email_failed_at');
            
            // WhatsApp notification tracking
            $table->timestamp('whatsapp_sent_at')->nullable()->after('email_error');
            $table->timestamp('whatsapp_failed_at')->nullable()->after('whatsapp_sent_at');
            $table->text('whatsapp_error')->nullable()->after('whatsapp_failed_at');
            
            // Indexes for monitoring queries
            $table->index('email_sent_at');
            $table->index('whatsapp_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sertifikat', function (Blueprint $table) {
            $table->dropIndex(['email_sent_at']);
            $table->dropIndex(['whatsapp_sent_at']);
            
            $table->dropColumn([
                'email_sent_at',
                'email_failed_at',
                'email_error',
                'whatsapp_sent_at',
                'whatsapp_failed_at',
                'whatsapp_error',
            ]);
        });
    }
};

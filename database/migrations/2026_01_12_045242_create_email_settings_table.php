<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabel dedicated untuk konfigurasi email sistem.
     * Single-row table (id=1) untuk menyimpan setting aktif.
     * Password SMTP dienkripsi menggunakan Laravel encryption.
     * Semua perubahan dicatat ke audit_logs untuk kepatuhan BNSP/ISO 17024.
     */
    public function up(): void
    {
        Schema::create('email_settings', function (Blueprint $table) {
            $table->id();
            
            // SMTP Configuration
            $table->string('mail_driver', 20)->default('smtp')->comment('smtp, sendmail, mailgun, ses, log');
            $table->string('mail_host', 255)->nullable()->comment('SMTP Host: smtp.gmail.com');
            $table->unsignedSmallInteger('mail_port')->default(587)->comment('Port: 587 (TLS), 465 (SSL)');
            $table->string('mail_encryption', 10)->default('tls')->comment('tls, ssl, null');
            $table->string('mail_username', 255)->nullable()->comment('SMTP Username/Email');
            $table->text('mail_password')->nullable()->comment('Encrypted SMTP Password');
            
            // Sender Identity
            $table->string('mail_from_name', 255)->default('CertiPro LSP')->comment('Nama pengirim email');
            $table->string('mail_from_address', 255)->default('noreply@certipro.id')->comment('Alamat email pengirim');
            
            // Tracking
            $table->boolean('is_active')->default(true)->comment('Apakah setting aktif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        
        // Insert default row (id=1)
        \DB::table('email_settings')->insert([
            'id' => 1,
            'mail_driver' => 'smtp',
            'mail_host' => null,
            'mail_port' => 587,
            'mail_encryption' => 'tls',
            'mail_username' => null,
            'mail_password' => null,
            'mail_from_name' => 'CertiPro LSP',
            'mail_from_address' => 'noreply@certipro.id',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_settings');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabel untuk menyimpan logo LSP yang diupload admin.
     * Logo digunakan di Admin Panel, Landing Page, Sertifikat PDF, dan Audit Evidence.
     * BNSP/ISO 17024 Compliant - dengan audit trail.
     */
    public function up(): void
    {
        Schema::create('logo_admin', function (Blueprint $table) {
            $table->id();
            $table->string('gambar')->comment('Path file logo di storage');
            $table->string('nama_perusahaan')->nullable()->comment('Nama LSP');
            $table->string('tagline')->nullable()->comment('Tagline/slogan LSP');
            $table->boolean('status')->default(true)->comment('Status aktif logo');
            $table->boolean('is_active')->default(true)->comment('Alias status untuk kompatibilitas');
            $table->timestamps();
            
            // Index untuk query aktif
            $table->index('status');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logo_admin');
    }
};

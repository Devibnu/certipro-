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
        Schema::create('sertifikat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->constrained('pendaftaran_sertifikasi')->onDelete('cascade');
            $table->string('nomor_sertifikat')->unique();
            $table->string('nama_peserta');
            $table->string('skema_sertifikasi');
            $table->date('tanggal_terbit');
            $table->date('tanggal_berlaku_sampai');
            $table->string('qr_code')->nullable();
            $table->string('file_pdf')->nullable();
            $table->foreignId('diterbitkan_oleh')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Indexes
            $table->index('pendaftaran_id');
            $table->index('tanggal_terbit');
            $table->index('diterbitkan_oleh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sertifikat');
    }
};

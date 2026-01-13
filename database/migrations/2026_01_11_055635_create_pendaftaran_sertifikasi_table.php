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
        Schema::create('pendaftaran_sertifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('skema_sertifikasi_id')->constrained('skema_sertifikasi')->onDelete('cascade');
            $table->string('nomor_pendaftaran', 50)->unique();
            $table->date('tanggal_daftar');
            $table->string('status', 20)->default('draft');
            $table->text('catatan_admin')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['skema_sertifikasi_id', 'status']);
            $table->index('tanggal_daftar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_sertifikasi');
    }
};

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
        Schema::create('asesmen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->constrained('pendaftaran_sertifikasi')->onDelete('cascade');
            $table->foreignId('asesor_id')->constrained('users')->onDelete('cascade');
            $table->date('tanggal_asesmen');
            $table->enum('metode_asesmen', ['observasi', 'portofolio', 'wawancara']);
            $table->text('catatan_asesor')->nullable();
            $table->enum('status', ['proses', 'selesai'])->default('proses');
            $table->timestamps();

            $table->index(['pendaftaran_id', 'status']);
            $table->index('asesor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asesmen');
    }
};

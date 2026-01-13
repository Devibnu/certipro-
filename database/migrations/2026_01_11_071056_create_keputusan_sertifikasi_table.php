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
        Schema::create('keputusan_sertifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->constrained('pendaftaran_sertifikasi')->onDelete('cascade');
            $table->foreignId('asesmen_id')->constrained('asesmen')->onDelete('cascade');
            $table->enum('keputusan', ['kompeten', 'belum_kompeten']);
            $table->text('catatan_komite')->nullable();
            $table->foreignId('ditetapkan_oleh')->constrained('users')->onDelete('cascade');
            $table->date('tanggal_keputusan');
            $table->boolean('is_locked')->default(false);
            $table->timestamps();

            // Indexes
            $table->index(['pendaftaran_id', 'is_locked']);
            $table->index('asesmen_id');
            $table->index('ditetapkan_oleh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keputusan_sertifikasi');
    }
};

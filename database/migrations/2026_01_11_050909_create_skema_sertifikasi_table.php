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
        Schema::create('skema_sertifikasi', function (Blueprint $table) {
            $table->id();
            $table->string('kode_skema')->unique();
            $table->string('nama_skema');
            $table->text('deskripsi')->nullable();
            $table->enum('jenis', ['nasional', 'internasional', 'internal'])->default('nasional');
            $table->integer('masa_berlaku')->nullable()->comment('dalam tahun');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skema_sertifikasi');
    }
};

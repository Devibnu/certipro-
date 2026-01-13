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
        Schema::create('pra_pendaftaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap');
            $table->string('email');
            $table->string('no_hp');
            $table->enum('tipe_peserta', ['umum', 'kampus'])->default('umum');
            $table->string('nik')->nullable();
            $table->string('nim')->nullable();
            $table->string('institusi')->nullable();
            $table->string('upload_identitas')->nullable();
            $table->enum('status', ['baru', 'diproses', 'diterima', 'ditolak'])->default('baru');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pra_pendaftaran');
    }
};

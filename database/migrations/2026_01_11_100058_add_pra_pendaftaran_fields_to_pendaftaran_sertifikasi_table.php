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
        Schema::table('pendaftaran_sertifikasi', function (Blueprint $table) {
            // Tambah kolom pra_pendaftaran_id
            $table->foreignId('pra_pendaftaran_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('pra_pendaftaran')
                  ->onDelete('set null');

            // Tambah kolom data dari pra-pendaftaran
            $table->string('nama_lengkap', 255)->nullable()->after('pra_pendaftaran_id');
            $table->string('email', 255)->nullable()->after('nama_lengkap');
            $table->string('no_hp', 20)->nullable()->after('email');
            $table->string('tipe_peserta', 20)->nullable()->after('no_hp');
            $table->string('nik', 16)->nullable()->after('tipe_peserta');
            $table->string('nim', 50)->nullable()->after('nik');
            $table->string('institusi', 255)->nullable()->after('nim');

            // Ubah user_id dan skema_sertifikasi_id menjadi nullable
            $table->foreignId('user_id')->nullable()->change();
            $table->foreignId('skema_sertifikasi_id')->nullable()->change();

            // Index untuk lookup cepat
            $table->index('pra_pendaftaran_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pendaftaran_sertifikasi', function (Blueprint $table) {
            $table->dropForeign(['pra_pendaftaran_id']);
            $table->dropColumn([
                'pra_pendaftaran_id',
                'nama_lengkap',
                'email',
                'no_hp',
                'tipe_peserta',
                'nik',
                'nim',
                'institusi',
            ]);
        });
    }
};

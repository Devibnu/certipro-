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
        Schema::create('kuk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_kompetensi_id')->constrained('unit_kompetensi')->onDelete('cascade');
            $table->string('kode_kuk', 50);
            $table->text('pernyataan_unjuk_kerja');
            $table->integer('urutan')->default(1);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index(['unit_kompetensi_id', 'urutan']);
            $table->index(['unit_kompetensi_id', 'aktif']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kuk');
    }
};

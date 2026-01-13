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
        Schema::create('bagian_halaman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('halaman_id')->constrained('halaman')->onDelete('cascade');
            $table->enum('tipe', ['hero', 'teks', 'daftar', 'faq']);
            $table->string('judul');
            $table->longText('isi')->nullable();
            $table->integer('urutan')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bagian_halaman');
    }
};

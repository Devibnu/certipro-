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
        Schema::create('asesmen_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesmen_id')->constrained('asesmen')->onDelete('cascade');
            $table->foreignId('unit_kompetensi_id')->constrained('unit_kompetensi')->onDelete('cascade');
            $table->foreignId('kuk_id')->constrained('kuk')->onDelete('cascade');
            $table->enum('hasil', ['kompeten', 'belum_kompeten']);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['asesmen_id', 'unit_kompetensi_id']);
            $table->index('kuk_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asesmen_detail');
    }
};

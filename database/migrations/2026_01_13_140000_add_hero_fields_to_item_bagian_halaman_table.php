<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan field untuk mendukung Hero/Slider di ItemBagianHalaman
     */
    public function up(): void
    {
        Schema::table('item_bagian_halaman', function (Blueprint $table) {
            $table->string('subjudul')->nullable()->after('judul');
            $table->string('gambar')->nullable()->after('deskripsi');
            $table->string('tombol_text', 100)->nullable()->after('gambar');
            $table->string('tombol_link')->nullable()->after('tombol_text');
            $table->string('tombol_text_2', 100)->nullable()->after('tombol_link');
            $table->string('tombol_link_2')->nullable()->after('tombol_text_2');
            $table->boolean('aktif')->default(true)->after('urutan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_bagian_halaman', function (Blueprint $table) {
            $table->dropColumn([
                'subjudul',
                'gambar',
                'tombol_text',
                'tombol_link',
                'tombol_text_2',
                'tombol_link_2',
                'aktif',
            ]);
        });
    }
};

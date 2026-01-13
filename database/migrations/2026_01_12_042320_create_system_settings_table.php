<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabel untuk menyimpan konfigurasi sistem secara dinamis.
     * Digunakan untuk Email Settings, dll tanpa perlu edit .env
     */
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50)->index()->comment('Grup setting: mail, app, etc');
            $table->string('key', 100)->comment('Key setting: mail_host, mail_port, etc');
            $table->text('value')->nullable()->comment('Nilai setting');
            $table->boolean('is_encrypted')->default(false)->comment('Apakah value terenkripsi');
            $table->string('type', 20)->default('string')->comment('Tipe data: string, integer, boolean, json');
            $table->text('description')->nullable()->comment('Deskripsi setting untuk audit');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            // Unique constraint untuk group + key
            $table->unique(['group', 'key'], 'system_settings_group_key_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};

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
        Schema::create('in_app_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type')->index(); // pendaftaran_baru, asesmen_selesai, sertifikat_diterbitkan
            $table->string('title');
            $table->text('message')->nullable();
            $table->string('icon')->nullable(); // fas fa-certificate, fas fa-clipboard-check, etc
            $table->string('icon_color')->default('primary'); // success, warning, info, danger
            $table->string('route_name')->nullable(); // adminui.pra-pendaftaran.show
            $table->json('route_params')->nullable(); // {id: 123}
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();
            
            // Index untuk query performa
            $table->index(['user_id', 'read_at', 'created_at']);
            $table->index(['user_id', 'type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('in_app_notifications');
    }
};

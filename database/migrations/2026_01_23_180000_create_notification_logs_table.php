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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('channel')->index(); // email, whatsapp
            $table->string('event_type')->index(); // pra_pendaftaran_diterima, keputusan_kompeten, etc
            $table->string('recipient')->index(); // email or phone
            $table->json('payload'); // data yang dikirim
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            
            // Unique key untuk idempotent (prevent duplicate)
            $table->string('unique_key')->unique();
            $table->index(['event_type', 'status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabel audit_logs untuk mencatat semua aktivitas penting dalam sistem.
     * Digunakan untuk memenuhi standar audit BNSP.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            
            // User yang melakukan aksi
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name')->nullable(); // Simpan nama untuk histori jika user dihapus
            $table->string('user_role')->nullable(); // Role saat aksi dilakukan
            
            // Detail aksi
            $table->string('action'); // create, update, delete, approve, reject, etc.
            $table->string('module'); // pra_pendaftaran, pendaftaran, asesmen, keputusan, sertifikat
            $table->string('description'); // Deskripsi human-readable
            
            // Data yang diproses
            $table->string('model_type')->nullable(); // App\Models\PendaftaranSertifikasi
            $table->unsignedBigInteger('model_id')->nullable(); // ID record
            $table->string('reference_number')->nullable(); // Nomor pendaftaran/sertifikat untuk tracking mudah
            
            // Perubahan data (untuk audit detail)
            $table->json('old_values')->nullable(); // Data sebelum perubahan
            $table->json('new_values')->nullable(); // Data setelah perubahan
            $table->json('metadata')->nullable(); // Data tambahan (browser, etc.)
            
            // Informasi request
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('method', 10)->nullable(); // GET, POST, PUT, DELETE
            
            // Timestamp
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes untuk pencarian cepat
            $table->index(['user_id', 'created_at']);
            $table->index(['module', 'action', 'created_at']);
            $table->index(['model_type', 'model_id']);
            $table->index('reference_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};

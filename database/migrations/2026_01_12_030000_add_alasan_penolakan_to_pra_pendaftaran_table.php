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
        Schema::table('pra_pendaftaran', function (Blueprint $table) {
            $table->text('alasan_penolakan')->nullable()->after('status');
            $table->timestamp('status_updated_at')->nullable()->after('alasan_penolakan');
            $table->unsignedBigInteger('status_updated_by')->nullable()->after('status_updated_at');
            
            $table->foreign('status_updated_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pra_pendaftaran', function (Blueprint $table) {
            $table->dropForeign(['status_updated_by']);
            $table->dropColumn(['alasan_penolakan', 'status_updated_at', 'status_updated_by']);
        });
    }
};

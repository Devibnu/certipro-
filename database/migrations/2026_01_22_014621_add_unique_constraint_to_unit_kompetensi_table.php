<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * IMPORTANT: This migration ensures data integrity for Unit Kompetensi
     * - Removes duplicate kode_unit entries (keeps oldest record)
     * - Adds unique constraint to prevent future duplicates
     * - Normalizes kode_unit to uppercase
     */
    public function up(): void
    {
        // Step 1: Normalize existing kode_unit to uppercase and trim
        DB::statement("UPDATE unit_kompetensi SET kode_unit = UPPER(TRIM(kode_unit))");

        // Step 2: Delete duplicates, keeping the one with lowest ID (first created)
        // This query finds and deletes duplicates safely
        DB::statement("
            DELETE uk1 FROM unit_kompetensi uk1
            INNER JOIN unit_kompetensi uk2
            WHERE uk1.id > uk2.id
            AND uk1.kode_unit = uk2.kode_unit
            AND uk1.skema_sertifikasi_id = uk2.skema_sertifikasi_id
        ");

        // Step 3: Add unique constraint on kode_unit + skema_sertifikasi_id
        // This allows same kode_unit in different skema, but not duplicate within same skema
        Schema::table('unit_kompetensi', function (Blueprint $table) {
            $table->unique(['kode_unit', 'skema_sertifikasi_id'], 'uk_kode_unit_skema_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unit_kompetensi', function (Blueprint $table) {
            $table->dropUnique('uk_kode_unit_skema_unique');
        });
    }
};

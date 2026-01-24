<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================================
 * Migration: Add Sampling Fields to Asesmen Table
 * ============================================================================
 * 
 * Implements Quality Control (Sampling Audit) mechanism for ISO 17024 compliance.
 * 
 * Purpose:
 * - Allow Komite Teknis to mark asesmen for sampling audit
 * - Provide documented quality control mechanism
 * - Demonstrate impartiality & quality assurance to auditors
 * 
 * Fields:
 * - is_sampled: Boolean flag to indicate sampling status
 * - sampled_at: Timestamp when marked for sampling
 * - sampled_by: User who marked it (Komite Teknis)
 * - sampling_note: Quality control notes/observations
 * 
 * @see ISO 17024:2012 Clause 4.3 (Impartiality)
 * @see ISO 17024:2012 Clause 9.4 (Internal audits)
 * @see BNSP Pedoman 201 (Pengendalian Mutu)
 * ============================================================================
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('asesmen', function (Blueprint $table) {
            // Sampling status
            $table->boolean('is_sampled')->default(false)->after('status')
                  ->comment('Flag for sampling audit by Komite Teknis');
            
            // Sampling timestamp
            $table->timestamp('sampled_at')->nullable()->after('is_sampled')
                  ->comment('When marked for sampling');
            
            // Who marked it for sampling
            $table->unsignedBigInteger('sampled_by')->nullable()->after('sampled_at')
                  ->comment('Komite Teknis user who marked for sampling');
            
            // Quality control notes
            $table->text('sampling_note')->nullable()->after('sampled_by')
                  ->comment('Sampling audit notes/observations');
            
            // Foreign key constraint
            $table->foreign('sampled_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
            
            // Index for filtering
            $table->index('is_sampled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asesmen', function (Blueprint $table) {
            $table->dropForeign(['sampled_by']);
            $table->dropIndex(['is_sampled']);
            $table->dropColumn([
                'is_sampled',
                'sampled_at',
                'sampled_by',
                'sampling_note',
            ]);
        });
    }
};

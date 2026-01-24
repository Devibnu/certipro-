<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================================
 * Migration: Create evidence_kuk Table
 * ============================================================================
 * 
 * Stores evidence (files/links) per KUK for asesmen process.
 * Supports ISO 17024 / BNSP audit requirements.
 * 
 * Structure:
 * - Each asesmen can have multiple evidence per KUK
 * - Evidence can be file (PDF/JPG/PNG/ZIP) or external link
 * - Files stored in private storage (not publicly accessible)
 * - Cascade delete when asesmen is deleted
 * 
 * @see ISO 17024:2012 Clause 7.4 (Examination process)
 * @see BNSP Pedoman 201 (Keamanan Data)
 * ============================================================================
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('evidence_kuk', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->unsignedBigInteger('asesmen_id');
            $table->unsignedBigInteger('kuk_id');
            $table->unsignedBigInteger('uploaded_by');
            
            // Evidence type
            $table->enum('type', ['file', 'link'])->default('file');
            
            // File evidence (stored in private storage)
            $table->string('file_path', 500)->nullable()->comment('Relative path in storage/app/private/evidence');
            $table->string('file_name_original', 255)->nullable()->comment('Original filename for display');
            $table->string('file_mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable()->comment('File size in bytes');
            
            // Link evidence
            $table->string('link_url', 1000)->nullable();
            
            // Description
            $table->text('description')->nullable()->comment('Deskripsi bukti/evidence');
            
            // Timestamps
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('asesmen_id')
                  ->references('id')
                  ->on('asesmen')
                  ->onDelete('cascade');
                  
            $table->foreign('kuk_id')
                  ->references('id')
                  ->on('kuk')
                  ->onDelete('cascade');
                  
            $table->foreign('uploaded_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            
            // Indexes for performance
            $table->index(['asesmen_id', 'kuk_id'], 'idx_evidence_asesmen_kuk');
            $table->index('uploaded_by', 'idx_evidence_uploader');
            $table->index('type', 'idx_evidence_type');
            $table->index('created_at', 'idx_evidence_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evidence_kuk');
    }
};

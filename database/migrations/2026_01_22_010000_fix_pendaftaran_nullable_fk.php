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
     * CRITICAL FIX: Make user_id and skema_sertifikasi_id NOT NULL
     * This prevents "Data asesi tidak ditemukan" errors at runtime
     */
    public function up(): void
    {
        // STEP 1: Identify and report orphaned records
        $orphanedUsers = DB::select("
            SELECT id, nomor_pendaftaran, user_id, skema_sertifikasi_id, status 
            FROM pendaftaran_sertifikasi 
            WHERE user_id IS NULL
        ");
        
        $orphanedSkema = DB::select("
            SELECT id, nomor_pendaftaran, user_id, skema_sertifikasi_id, status 
            FROM pendaftaran_sertifikasi 
            WHERE skema_sertifikasi_id IS NULL
        ");
        
        if (count($orphanedUsers) > 0) {
            echo "\n⚠️  WARNING: Found " . count($orphanedUsers) . " pendaftaran without user_id\n";
            foreach ($orphanedUsers as $record) {
                echo "   - ID: {$record->id}, Nomor: {$record->nomor_pendaftaran}, Status: {$record->status}\n";
            }
        }
        
        if (count($orphanedSkema) > 0) {
            echo "\n⚠️  WARNING: Found " . count($orphanedSkema) . " pendaftaran without skema_sertifikasi_id\n";
            foreach ($orphanedSkema as $record) {
                echo "   - ID: {$record->id}, Nomor: {$record->nomor_pendaftaran}, Status: {$record->status}\n";
            }
        }
        
        // STEP 2: Clean orphan records (only if status = draft or ditolak)
        $deletedCount = DB::table('pendaftaran_sertifikasi')
            ->where(function($query) {
                $query->whereNull('user_id')
                      ->orWhereNull('skema_sertifikasi_id');
            })
            ->whereIn('status', ['draft', 'ditolak'])
            ->delete();
        
        if ($deletedCount > 0) {
            echo "\n✅ Deleted {$deletedCount} orphaned draft/rejected records\n";
        }
        
        // STEP 3: Check if any critical orphaned records remain
        $remainingOrphans = DB::table('pendaftaran_sertifikasi')
            ->where(function($query) {
                $query->whereNull('user_id')
                      ->orWhereNull('skema_sertifikasi_id');
            })
            ->count();
        
        if ($remainingOrphans > 0) {
            echo "\n❌ ERROR: {$remainingOrphans} orphaned records remain with active status\n";
            echo "   Please manually review and fix these records before running this migration\n";
            throw new \Exception("Cannot proceed: Orphaned records exist with active status");
        }
        
        // STEP 4: Make columns NOT NULL
        Schema::table('pendaftaran_sertifikasi', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->unsignedBigInteger('skema_sertifikasi_id')->nullable(false)->change();
        });
        
        echo "\n✅ Columns set to NOT NULL: user_id, skema_sertifikasi_id\n";
        
        // STEP 5: Change FK strategy to RESTRICT for data integrity
        DB::statement("
            ALTER TABLE pendaftaran_sertifikasi 
              DROP FOREIGN KEY pendaftaran_sertifikasi_user_id_foreign
        ");
        
        DB::statement("
            ALTER TABLE pendaftaran_sertifikasi 
              ADD CONSTRAINT pendaftaran_sertifikasi_user_id_foreign 
                FOREIGN KEY (user_id) 
                REFERENCES users(id) 
                ON DELETE RESTRICT
        ");
        
        echo "✅ FK user_id changed to ON DELETE RESTRICT\n";
        
        DB::statement("
            ALTER TABLE pendaftaran_sertifikasi 
              DROP FOREIGN KEY pendaftaran_sertifikasi_skema_sertifikasi_id_foreign
        ");
        
        DB::statement("
            ALTER TABLE pendaftaran_sertifikasi 
              ADD CONSTRAINT pendaftaran_sertifikasi_skema_sertifikasi_id_foreign 
                FOREIGN KEY (skema_sertifikasi_id) 
                REFERENCES skema_sertifikasi(id) 
                ON DELETE RESTRICT
        ");
        
        echo "✅ FK skema_sertifikasi_id changed to ON DELETE RESTRICT\n\n";
        echo "🎉 Migration completed successfully!\n";
        echo "   - user_id: NOT NULL + RESTRICT\n";
        echo "   - skema_sertifikasi_id: NOT NULL + RESTRICT\n";
        echo "   - Data integrity enforced at database level\n\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        echo "\n⚠️  Rolling back to nullable columns (not recommended for production)\n";
        
        // Revert FK to CASCADE
        DB::statement("
            ALTER TABLE pendaftaran_sertifikasi 
              DROP FOREIGN KEY pendaftaran_sertifikasi_user_id_foreign
        ");
        
        DB::statement("
            ALTER TABLE pendaftaran_sertifikasi 
              ADD CONSTRAINT pendaftaran_sertifikasi_user_id_foreign 
                FOREIGN KEY (user_id) 
                REFERENCES users(id) 
                ON DELETE CASCADE
        ");
        
        DB::statement("
            ALTER TABLE pendaftaran_sertifikasi 
              DROP FOREIGN KEY pendaftaran_sertifikasi_skema_sertifikasi_id_foreign
        ");
        
        DB::statement("
            ALTER TABLE pendaftaran_sertifikasi 
              ADD CONSTRAINT pendaftaran_sertifikasi_skema_sertifikasi_id_foreign 
                FOREIGN KEY (skema_sertifikasi_id) 
                REFERENCES skema_sertifikasi(id) 
                ON DELETE CASCADE
        ");
        
        // Revert to nullable
        Schema::table('pendaftaran_sertifikasi', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->unsignedBigInteger('skema_sertifikasi_id')->nullable()->change();
        });
        
        echo "✅ Rollback completed\n\n";
    }
};

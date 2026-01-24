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
     * CRITICAL FIX: Add keputusan_sertifikasi_id to sertifikat table
     * This ensures certificates can ONLY be issued if a decision exists
     */
    public function up(): void
    {
        echo "\n🔧 Starting migration: Add keputusan_sertifikasi_id to sertifikat\n\n";
        
        // STEP 1: Add column (nullable first for safe migration)
        Schema::table('sertifikat', function (Blueprint $table) {
            $table->unsignedBigInteger('keputusan_sertifikasi_id')
                ->nullable()
                ->after('pendaftaran_id')
                ->comment('FK to keputusan_sertifikasi - MANDATORY for certificate issuance');
        });
        
        echo "✅ Column added: keputusan_sertifikasi_id (nullable)\n";
        
        // STEP 2: Populate existing records
        $updated = DB::statement("
            UPDATE sertifikat s
            INNER JOIN keputusan_sertifikasi k 
              ON s.pendaftaran_id = k.pendaftaran_id
            SET s.keputusan_sertifikasi_id = k.id
            WHERE s.keputusan_sertifikasi_id IS NULL
        ");
        
        $populatedCount = DB::table('sertifikat')
            ->whereNotNull('keputusan_sertifikasi_id')
            ->count();
            
        echo "✅ Populated {$populatedCount} existing certificates with keputusan_id\n";
        
        // STEP 3: Check for certificates without keputusan (CRITICAL!)
        $orphanedCerts = DB::select("
            SELECT 
                s.id,
                s.nomor_sertifikat,
                s.nama_peserta,
                s.tanggal_terbit
            FROM sertifikat s
            LEFT JOIN keputusan_sertifikasi k ON s.pendaftaran_id = k.pendaftaran_id
            WHERE s.keputusan_sertifikasi_id IS NULL
        ");
        
        if (count($orphanedCerts) > 0) {
            echo "\n⚠️  WARNING: Found " . count($orphanedCerts) . " certificates without keputusan!\n";
            foreach ($orphanedCerts as $cert) {
                echo "   - Cert #{$cert->nomor_sertifikat}: {$cert->nama_peserta} (issued: {$cert->tanggal_terbit})\n";
            }
            
            echo "\n❌ ERROR: Cannot proceed - certificates exist without keputusan\n";
            echo "   Action required:\n";
            echo "   1. Review these certificates manually\n";
            echo "   2. Create keputusan records for them, OR\n";
            echo "   3. Revoke these certificates\n";
            echo "   4. Then re-run this migration\n\n";
            
            throw new \Exception("Orphaned certificates found - manual intervention required");
        }
        
        echo "✅ All certificates have valid keputusan\n";
        
        // STEP 4: Make NOT NULL
        Schema::table('sertifikat', function (Blueprint $table) {
            $table->unsignedBigInteger('keputusan_sertifikasi_id')
                ->nullable(false)
                ->change();
        });
        
        echo "✅ Column set to NOT NULL\n";
        
        // STEP 5: Add foreign key constraint with RESTRICT
        Schema::table('sertifikat', function (Blueprint $table) {
            $table->foreign('keputusan_sertifikasi_id', 'sertifikat_keputusan_fk')
                ->references('id')
                ->on('keputusan_sertifikasi')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });
        
        echo "✅ FK constraint added: ON DELETE RESTRICT (prevent keputusan deletion)\n";
        
        // STEP 6: Add index for performance
        DB::statement("
            CREATE INDEX idx_sertifikat_keputusan 
            ON sertifikat(keputusan_sertifikasi_id)
        ");
        
        echo "✅ Index created: idx_sertifikat_keputusan\n";
        
        // STEP 7: Add unique constraint (one certificate per pendaftaran)
        $duplicates = DB::select("
            SELECT pendaftaran_id, COUNT(*) as total
            FROM sertifikat
            GROUP BY pendaftaran_id
            HAVING COUNT(*) > 1
        ");
        
        if (count($duplicates) > 0) {
            echo "\n⚠️  WARNING: Found " . count($duplicates) . " pendaftaran with multiple certificates\n";
            foreach ($duplicates as $dup) {
                echo "   - Pendaftaran ID {$dup->pendaftaran_id}: {$dup->total} certificates\n";
            }
            echo "   Skipping unique constraint creation\n";
            echo "   Please review and remove duplicates manually\n\n";
        } else {
            Schema::table('sertifikat', function (Blueprint $table) {
                $table->unique('pendaftaran_id', 'sertifikat_pendaftaran_unique');
            });
            echo "✅ Unique constraint added: one certificate per pendaftaran\n";
        }
        
        echo "\n🎉 Migration completed successfully!\n";
        echo "   - keputusan_sertifikasi_id: NOT NULL + RESTRICT\n";
        echo "   - Certificates now require valid keputusan\n";
        echo "   - Database-level integrity enforced\n\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        echo "\n⚠️  Rolling back: Remove keputusan_sertifikasi_id from sertifikat\n";
        
        // Drop unique constraint if exists
        try {
            Schema::table('sertifikat', function (Blueprint $table) {
                $table->dropUnique('sertifikat_pendaftaran_unique');
            });
            echo "✅ Unique constraint dropped\n";
        } catch (\Exception $e) {
            echo "⚠️  Unique constraint not found (already dropped or never created)\n";
        }
        
        // Drop index
        try {
            DB::statement("DROP INDEX idx_sertifikat_keputusan ON sertifikat");
            echo "✅ Index dropped\n";
        } catch (\Exception $e) {
            echo "⚠️  Index not found\n";
        }
        
        // Drop foreign key and column
        Schema::table('sertifikat', function (Blueprint $table) {
            $table->dropForeign('sertifikat_keputusan_fk');
            $table->dropColumn('keputusan_sertifikasi_id');
        });
        
        echo "✅ Column and FK removed: keputusan_sertifikasi_id\n";
        echo "✅ Rollback completed\n\n";
    }
};

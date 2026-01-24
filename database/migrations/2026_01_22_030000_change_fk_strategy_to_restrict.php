<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * CRITICAL FIX: Change FK strategies to RESTRICT for historical data
     * Prevents accidental deletion of audit trail and legal documents
     */
    public function up(): void
    {
        echo "\n🔧 Starting migration: Change FK strategies to RESTRICT\n\n";
        
        // ============================================================
        // ASESMEN TABLE: Preserve audit trail
        // ============================================================
        
        echo "📋 Processing asesmen table...\n";
        
        // Add backup column for asesor name (in case user is soft-deleted)
        DB::statement("
            ALTER TABLE asesmen 
            ADD COLUMN asesor_nama_backup VARCHAR(255) NULL AFTER asesor_id
        ");
        echo "✅ Added asesor_nama_backup column\n";
        
        // Populate backup for existing records
        DB::statement("
            UPDATE asesmen a
            INNER JOIN users u ON a.asesor_id = u.id
            SET a.asesor_nama_backup = u.name
            WHERE a.asesor_nama_backup IS NULL
        ");
        echo "✅ Populated backup names for existing asesmen\n";
        
        // Change asesor_id to SET NULL (preserve record, backup name kept)
        DB::statement("
            ALTER TABLE asesmen 
              DROP FOREIGN KEY asesmen_asesor_id_foreign
        ");
        
        DB::statement("
            ALTER TABLE asesmen 
              ADD CONSTRAINT asesmen_asesor_id_foreign 
                FOREIGN KEY (asesor_id) 
                REFERENCES users(id) 
                ON DELETE SET NULL
                ON UPDATE CASCADE
        ");
        echo "✅ FK asesor_id: ON DELETE SET NULL (audit trail preserved)\n\n";
        
        // ============================================================
        // KEPUTUSAN_SERTIFIKASI TABLE: Legal document - NEVER DELETE
        // ============================================================
        
        echo "📋 Processing keputusan_sertifikasi table...\n";
        
        // FK to users (ditetapkan_oleh) → RESTRICT
        DB::statement("
            ALTER TABLE keputusan_sertifikasi 
              DROP FOREIGN KEY keputusan_sertifikasi_ditetapkan_oleh_foreign
        ");
        
        DB::statement("
            ALTER TABLE keputusan_sertifikasi 
              ADD CONSTRAINT keputusan_sertifikasi_ditetapkan_oleh_foreign 
                FOREIGN KEY (ditetapkan_oleh) 
                REFERENCES users(id) 
                ON DELETE RESTRICT
                ON UPDATE CASCADE
        ");
        echo "✅ FK ditetapkan_oleh: ON DELETE RESTRICT (cannot delete signer)\n";
        
        // FK to pendaftaran_sertifikasi → RESTRICT
        DB::statement("
            ALTER TABLE keputusan_sertifikasi 
              DROP FOREIGN KEY keputusan_sertifikasi_pendaftaran_id_foreign
        ");
        
        DB::statement("
            ALTER TABLE keputusan_sertifikasi 
              ADD CONSTRAINT keputusan_sertifikasi_pendaftaran_id_foreign 
                FOREIGN KEY (pendaftaran_id) 
                REFERENCES pendaftaran_sertifikasi(id) 
                ON DELETE RESTRICT
                ON UPDATE CASCADE
        ");
        echo "✅ FK pendaftaran_id: ON DELETE RESTRICT (cannot delete if decision exists)\n";
        
        // FK to asesmen → RESTRICT
        DB::statement("
            ALTER TABLE keputusan_sertifikasi 
              DROP FOREIGN KEY keputusan_sertifikasi_asesmen_id_foreign
        ");
        
        DB::statement("
            ALTER TABLE keputusan_sertifikasi 
              ADD CONSTRAINT keputusan_sertifikasi_asesmen_id_foreign 
                FOREIGN KEY (asesmen_id) 
                REFERENCES asesmen(id) 
                ON DELETE RESTRICT
                ON UPDATE CASCADE
        ");
        echo "✅ FK asesmen_id: ON DELETE RESTRICT (cannot delete if decision exists)\n\n";
        
        // ============================================================
        // SERTIFIKAT TABLE: Legal document - ABSOLUTE PROTECTION
        // ============================================================
        
        echo "📋 Processing sertifikat table...\n";
        
        // FK to users (diterbitkan_oleh) → RESTRICT
        DB::statement("
            ALTER TABLE sertifikat 
              DROP FOREIGN KEY sertifikat_diterbitkan_oleh_foreign
        ");
        
        DB::statement("
            ALTER TABLE sertifikat 
              ADD CONSTRAINT sertifikat_diterbitkan_oleh_foreign 
                FOREIGN KEY (diterbitkan_oleh) 
                REFERENCES users(id) 
                ON DELETE RESTRICT
                ON UPDATE CASCADE
        ");
        echo "✅ FK diterbitkan_oleh: ON DELETE RESTRICT (cannot delete issuer)\n";
        
        // FK to pendaftaran_sertifikasi → RESTRICT
        DB::statement("
            ALTER TABLE sertifikat 
              DROP FOREIGN KEY sertifikat_pendaftaran_id_foreign
        ");
        
        DB::statement("
            ALTER TABLE sertifikat 
              ADD CONSTRAINT sertifikat_pendaftaran_id_foreign 
                FOREIGN KEY (pendaftaran_id) 
                REFERENCES pendaftaran_sertifikasi(id) 
                ON DELETE RESTRICT
                ON UPDATE CASCADE
        ");
        echo "✅ FK pendaftaran_id: ON DELETE RESTRICT (cannot delete if certificate exists)\n\n";
        
        // ============================================================
        // SUMMARY
        // ============================================================
        
        echo "🎉 Migration completed successfully!\n\n";
        echo "📊 FK Strategy Summary:\n";
        echo "   asesmen.asesor_id → SET NULL (backup name preserved)\n";
        echo "   keputusan.ditetapkan_oleh → RESTRICT (legal signature protected)\n";
        echo "   keputusan.pendaftaran_id → RESTRICT (cannot delete if decided)\n";
        echo "   keputusan.asesmen_id → RESTRICT (cannot delete if decided)\n";
        echo "   sertifikat.diterbitkan_oleh → RESTRICT (issuer protected)\n";
        echo "   sertifikat.pendaftaran_id → RESTRICT (cannot delete if certified)\n\n";
        echo "✅ Data integrity enforced at database level\n";
        echo "✅ Audit trail protected from accidental deletion\n";
        echo "✅ Legal documents (keputusan, sertifikat) fully protected\n\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        echo "\n⚠️  Rolling back: Reverting FK strategies to CASCADE\n";
        echo "⚠️  WARNING: This removes data protection and is NOT recommended for production!\n\n";
        
        // Revert asesmen
        DB::statement("ALTER TABLE asesmen DROP FOREIGN KEY asesmen_asesor_id_foreign");
        DB::statement("ALTER TABLE asesmen ADD CONSTRAINT asesmen_asesor_id_foreign FOREIGN KEY (asesor_id) REFERENCES users(id) ON DELETE CASCADE");
        DB::statement("ALTER TABLE asesmen DROP COLUMN asesor_nama_backup");
        echo "✅ Reverted asesmen FKs to CASCADE\n";
        
        // Revert keputusan_sertifikasi
        DB::statement("ALTER TABLE keputusan_sertifikasi DROP FOREIGN KEY keputusan_sertifikasi_ditetapkan_oleh_foreign");
        DB::statement("ALTER TABLE keputusan_sertifikasi ADD CONSTRAINT keputusan_sertifikasi_ditetapkan_oleh_foreign FOREIGN KEY (ditetapkan_oleh) REFERENCES users(id) ON DELETE CASCADE");
        
        DB::statement("ALTER TABLE keputusan_sertifikasi DROP FOREIGN KEY keputusan_sertifikasi_pendaftaran_id_foreign");
        DB::statement("ALTER TABLE keputusan_sertifikasi ADD CONSTRAINT keputusan_sertifikasi_pendaftaran_id_foreign FOREIGN KEY (pendaftaran_id) REFERENCES pendaftaran_sertifikasi(id) ON DELETE CASCADE");
        
        DB::statement("ALTER TABLE keputusan_sertifikasi DROP FOREIGN KEY keputusan_sertifikasi_asesmen_id_foreign");
        DB::statement("ALTER TABLE keputusan_sertifikasi ADD CONSTRAINT keputusan_sertifikasi_asesmen_id_foreign FOREIGN KEY (asesmen_id) REFERENCES asesmen(id) ON DELETE CASCADE");
        echo "✅ Reverted keputusan_sertifikasi FKs to CASCADE\n";
        
        // Revert sertifikat
        DB::statement("ALTER TABLE sertifikat DROP FOREIGN KEY sertifikat_diterbitkan_oleh_foreign");
        DB::statement("ALTER TABLE sertifikat ADD CONSTRAINT sertifikat_diterbitkan_oleh_foreign FOREIGN KEY (diterbitkan_oleh) REFERENCES users(id) ON DELETE CASCADE");
        
        DB::statement("ALTER TABLE sertifikat DROP FOREIGN KEY sertifikat_pendaftaran_id_foreign");
        DB::statement("ALTER TABLE sertifikat ADD CONSTRAINT sertifikat_pendaftaran_id_foreign FOREIGN KEY (pendaftaran_id) REFERENCES pendaftaran_sertifikasi(id) ON DELETE CASCADE");
        echo "✅ Reverted sertifikat FKs to CASCADE\n";
        
        echo "\n✅ Rollback completed\n";
        echo "⚠️  Data protection removed - production use NOT recommended\n\n";
    }
};

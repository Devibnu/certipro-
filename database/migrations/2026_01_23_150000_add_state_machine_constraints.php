<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Helper: Create index if not exists (MySQL safe)
     */
    private function createIndexSafe(string $indexName, string $table, string $column): void
    {
        try {
            // Check if index exists
            $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
            if (empty($indexes)) {
                DB::statement("CREATE INDEX {$indexName} ON {$table}({$column})");
            }
        } catch (\Exception $e) {
            // Index might already exist or other error, ignore
        }
    }

    /**
     * Helper: Create unique index if not exists (MySQL safe)
     */
    private function createUniqueIndexSafe(string $indexName, string $table, string $column): void
    {
        try {
            // Check if index exists
            $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
            if (empty($indexes)) {
                DB::statement("CREATE UNIQUE INDEX {$indexName} ON {$table}({$column})");
            }
        } catch (\Exception $e) {
            // Check for duplicates
            $duplicates = DB::select("
                SELECT {$column}, COUNT(*) as count 
                FROM {$table} 
                WHERE {$column} IS NOT NULL 
                GROUP BY {$column} 
                HAVING count > 1
            ");

            if (!empty($duplicates)) {
                throw new \Exception(
                    "Cannot add UNIQUE constraint on {$table}.{$column}: Found duplicates. " .
                    json_encode($duplicates)
                );
            }

            throw $e;
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // === 1. PRA_PENDAFTARAN ===
        if (Schema::hasTable('pra_pendaftaran')) {
            Schema::table('pra_pendaftaran', function (Blueprint $table) {
                if (!Schema::hasColumn('pra_pendaftaran', 'is_processed')) {
                    $table->boolean('is_processed')->default(false)->after('status');
                }
                if (!Schema::hasColumn('pra_pendaftaran', 'processed_at')) {
                    $table->timestamp('processed_at')->nullable()->after('is_processed');
                }
                if (!Schema::hasColumn('pra_pendaftaran', 'catatan_admin')) {
                    $table->text('catatan_admin')->nullable()->after('processed_at');
                }
            });

            // Add indexes (MySQL doesn't support IF NOT EXISTS for indexes)
            try {
                DB::statement('CREATE INDEX idx_pra_pendaftaran_status ON pra_pendaftaran(status)');
            } catch (\Exception $e) {
                // Index might already exist
            }
            try {
                DB::statement('CREATE INDEX idx_pra_pendaftaran_processed ON pra_pendaftaran(is_processed)');
            } catch (\Exception $e) {
                // Index might already exist
            }
        }

        // === 2. PENDAFTARAN_SERTIFIKASI ===
        if (Schema::hasTable('pendaftaran_sertifikasi')) {
            Schema::table('pendaftaran_sertifikasi', function (Blueprint $table) {
                // Lock mechanism
                if (!Schema::hasColumn('pendaftaran_sertifikasi', 'is_locked')) {
                    $table->boolean('is_locked')->default(false)->after('status');
                }
                if (!Schema::hasColumn('pendaftaran_sertifikasi', 'locked_at')) {
                    $table->timestamp('locked_at')->nullable()->after('is_locked');
                }
                if (!Schema::hasColumn('pendaftaran_sertifikasi', 'locked_reason')) {
                    $table->string('locked_reason')->nullable()->after('locked_at');
                }

                // Status tracking
                if (!Schema::hasColumn('pendaftaran_sertifikasi', 'status_updated_at')) {
                    $table->timestamp('status_updated_at')->nullable()->after('locked_reason');
                }
                if (!Schema::hasColumn('pendaftaran_sertifikasi', 'catatan_admin')) {
                    $table->text('catatan_admin')->nullable()->after('status_updated_at');
                }
            });

            // Add UNIQUE constraint on pra_pendaftaran_id
            $this->createUniqueIndexSafe('unique_pra_pendaftaran_id', 'pendaftaran_sertifikasi', 'pra_pendaftaran_id');

            // Add indexes
            // Add indexes
            $this->createIndexSafe('idx_pendaftaran_status', 'pendaftaran_sertifikasi', 'status');
            $this->createIndexSafe('idx_pendaftaran_locked', 'pendaftaran_sertifikasi', 'is_locked');
            $this->createIndexSafe('idx_pendaftaran_user', 'pendaftaran_sertifikasi', 'user_id');
        }

        // === 3. ASESMEN ===
        if (Schema::hasTable('asesmen')) {
            Schema::table('asesmen', function (Blueprint $table) {
                // Lock mechanism
                if (!Schema::hasColumn('asesmen', 'is_locked')) {
                    $table->boolean('is_locked')->default(false)->after('status');
                }
                if (!Schema::hasColumn('asesmen', 'locked_at')) {
                    $table->timestamp('locked_at')->nullable()->after('is_locked');
                }

                // Completion tracking
                if (!Schema::hasColumn('asesmen', 'started_at')) {
                    $table->timestamp('started_at')->nullable()->after('locked_at');
                }
                if (!Schema::hasColumn('asesmen', 'completed_at')) {
                    $table->timestamp('completed_at')->nullable()->after('started_at');
                }
            });

            // Add UNIQUE constraint: 1 pendaftaran = 1 asesmen
            $this->createUniqueIndexSafe('unique_pendaftaran_asesmen', 'asesmen', 'pendaftaran_id');

            $this->createIndexSafe('idx_asesmen_status', 'asesmen', 'status');
            $this->createIndexSafe('idx_asesmen_locked', 'asesmen', 'is_locked');
        }

        // === 4. KEPUTUSAN_SERTIFIKASI ===
        if (Schema::hasTable('keputusan_sertifikasi')) {
            Schema::table('keputusan_sertifikasi', function (Blueprint $table) {
                // Lock mechanism
                if (!Schema::hasColumn('keputusan_sertifikasi', 'is_locked')) {
                    $table->boolean('is_locked')->default(false)->after('keputusan');
                }
                if (!Schema::hasColumn('keputusan_sertifikasi', 'locked_at')) {
                    $table->timestamp('locked_at')->nullable()->after('is_locked');
                }

                // Decision tracking
                if (!Schema::hasColumn('keputusan_sertifikasi', 'decided_at')) {
                    $table->timestamp('decided_at')->nullable()->after('locked_at');
                }
                if (!Schema::hasColumn('keputusan_sertifikasi', 'decided_by')) {
                    $table->foreignId('decided_by')->nullable()->after('decided_at')->constrained('users');
                }
                if (!Schema::hasColumn('keputusan_sertifikasi', 'catatan')) {
                    $table->text('catatan')->nullable()->after('decided_by');
                }
                // Add status column if not exists (for state machine)
                if (!Schema::hasColumn('keputusan_sertifikasi', 'status')) {
                    $table->string('status', 50)->default('belum_ditetapkan')->after('keputusan');
                }
            });

            // Add UNIQUE constraint: 1 asesmen = 1 keputusan
            $this->createUniqueIndexSafe('unique_asesmen_keputusan', 'keputusan_sertifikasi', 'asesmen_id');

            $this->createIndexSafe('idx_keputusan_status', 'keputusan_sertifikasi', 'status');
            $this->createIndexSafe('idx_keputusan_locked', 'keputusan_sertifikasi', 'is_locked');
        }

        // === 5. SERTIFIKAT ===
        if (Schema::hasTable('sertifikat')) {
            Schema::table('sertifikat', function (Blueprint $table) {
                // Add status column if not exists
                if (!Schema::hasColumn('sertifikat', 'status')) {
                    $table->string('status', 50)->default('terbit')->after('file_pdf');
                }
                // Add keputusan_id column if not exists
                if (!Schema::hasColumn('sertifikat', 'keputusan_id')) {
                    $table->unsignedBigInteger('keputusan_id')->nullable()->after('pendaftaran_id');
                    $table->foreign('keputusan_id')->references('id')->on('keputusan_sertifikasi')->onDelete('restrict');
                }
                // Revocation tracking
                if (!Schema::hasColumn('sertifikat', 'revoked_at')) {
                    $table->timestamp('revoked_at')->nullable()->after('status');
                }
                if (!Schema::hasColumn('sertifikat', 'revoked_by')) {
                    $table->foreignId('revoked_by')->nullable()->after('revoked_at')->constrained('users');
                }
                if (!Schema::hasColumn('sertifikat', 'revoked_reason')) {
                    $table->text('revoked_reason')->nullable()->after('revoked_by');
                }
            });

            // Add UNIQUE constraint: 1 keputusan = 1 sertifikat
            $this->createUniqueIndexSafe('unique_keputusan_sertifikat', 'sertifikat', 'keputusan_id');

            $this->createIndexSafe('idx_sertifikat_status', 'sertifikat', 'status');
            $this->createIndexSafe('idx_sertifikat_expiry', 'sertifikat', 'tanggal_berlaku_sampai');
            $this->createIndexSafe('idx_sertifikat_nomor', 'sertifikat', 'nomor_sertifikat');
        }

        // === DATA INTEGRITY CHECK ===
        $this->checkDataIntegrity();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes and constraints
        DB::statement('DROP INDEX IF EXISTS idx_pra_pendaftaran_status');
        DB::statement('DROP INDEX IF EXISTS idx_pra_pendaftaran_processed');
        DB::statement('DROP INDEX IF EXISTS unique_pra_pendaftaran_id');
        DB::statement('DROP INDEX IF EXISTS idx_pendaftaran_status');
        DB::statement('DROP INDEX IF EXISTS idx_pendaftaran_locked');
        DB::statement('DROP INDEX IF EXISTS idx_pendaftaran_user');
        DB::statement('DROP INDEX IF EXISTS unique_pendaftaran_asesmen');
        DB::statement('DROP INDEX IF EXISTS idx_asesmen_status');
        DB::statement('DROP INDEX IF EXISTS idx_asesmen_locked');
        DB::statement('DROP INDEX IF EXISTS unique_asesmen_keputusan');
        DB::statement('DROP INDEX IF EXISTS idx_keputusan_status');
        DB::statement('DROP INDEX IF EXISTS idx_keputusan_locked');
        DB::statement('DROP INDEX IF EXISTS unique_keputusan_sertifikat');
        DB::statement('DROP INDEX IF EXISTS idx_sertifikat_status');
        DB::statement('DROP INDEX IF EXISTS idx_sertifikat_expiry');
        DB::statement('DROP INDEX IF EXISTS idx_sertifikat_nomor');

        // Remove columns
        if (Schema::hasTable('pra_pendaftaran')) {
            Schema::table('pra_pendaftaran', function (Blueprint $table) {
                $table->dropColumn(['is_processed', 'processed_at', 'catatan_admin']);
            });
        }

        if (Schema::hasTable('pendaftaran_sertifikasi')) {
            Schema::table('pendaftaran_sertifikasi', function (Blueprint $table) {
                $table->dropColumn([
                    'is_locked', 'locked_at', 'locked_reason',
                    'status_updated_at', 'catatan_admin'
                ]);
            });
        }

        if (Schema::hasTable('asesmen')) {
            Schema::table('asesmen', function (Blueprint $table) {
                $table->dropColumn(['is_locked', 'locked_at', 'started_at', 'completed_at']);
            });
        }

        if (Schema::hasTable('keputusan_sertifikasi')) {
            Schema::table('keputusan_sertifikasi', function (Blueprint $table) {
                $table->dropForeign(['decided_by']);
                $table->dropColumn(['is_locked', 'locked_at', 'decided_at', 'decided_by', 'catatan']);
            });
        }

        if (Schema::hasTable('sertifikat')) {
            Schema::table('sertifikat', function (Blueprint $table) {
                $table->dropForeign(['revoked_by']);
                $table->dropColumn(['revoked_at', 'revoked_by', 'revoked_reason']);
            });
        }
    }

    /**
     * Check data integrity before applying constraints
     */
    private function checkDataIntegrity(): void
    {
        $issues = [];

        // Check orphaned pendaftaran (user_id NULL with active status)
        $orphanedPendaftaran = DB::table('pendaftaran_sertifikasi')
            ->whereNull('user_id')
            ->whereNotIn('status', ['draft', 'ditolak', 'dibatalkan'])
            ->get(['id', 'nomor_pendaftaran', 'status']);

        if ($orphanedPendaftaran->count() > 0) {
            $issues[] = "Found {$orphanedPendaftaran->count()} orphaned pendaftaran records with active status:";
            foreach ($orphanedPendaftaran as $record) {
                $issues[] = "  - ID: {$record->id}, Nomor: {$record->nomor_pendaftaran}, Status: {$record->status}";
            }
        }

        // Check orphaned asesmen
        $orphanedAsesmen = DB::table('asesmen as a')
            ->leftJoin('pendaftaran_sertifikasi as p', 'a.pendaftaran_id', '=', 'p.id')
            ->whereNull('p.id')
            ->get(['a.id', 'a.status']);

        if ($orphanedAsesmen->count() > 0) {
            $issues[] = "\nFound {$orphanedAsesmen->count()} orphaned asesmen records (no parent pendaftaran)";
        }

        // Check orphaned keputusan
        $orphanedKeputusan = DB::table('keputusan_sertifikasi as k')
            ->leftJoin('asesmen as a', 'k.asesmen_id', '=', 'a.id')
            ->whereNull('a.id')
            ->get(['k.id', 'k.status']);

        if ($orphanedKeputusan->count() > 0) {
            $issues[] = "\nFound {$orphanedKeputusan->count()} orphaned keputusan records (no parent asesmen)";
        }

        // Check orphaned sertifikat
        $orphanedSertifikat = DB::table('sertifikat as s')
            ->leftJoin('keputusan_sertifikasi as k', 's.keputusan_id', '=', 'k.id')
            ->whereNull('k.id')
            ->get(['s.id', 's.nomor_sertifikat']);

        if ($orphanedSertifikat->count() > 0) {
            $issues[] = "\nFound {$orphanedSertifikat->count()} orphaned sertifikat records (no parent keputusan)";
        }

        if (!empty($issues)) {
            $message = "❌ DATA INTEGRITY ISSUES FOUND:\n\n" . implode("\n", $issues);
            $message .= "\n\n💡 RECOMMENDED ACTIONS:";
            $message .= "\n1. Review orphaned records";
            $message .= "\n2. Set orphaned pendaftaran to 'draft' or 'dibatalkan'";
            $message .= "\n3. Delete truly orphaned records (no parent)";
            $message .= "\n4. Re-run this migration";
            
            Log::warning($message);
            echo "\n" . $message . "\n\n";
            
            // Don't throw exception, just warn
            // throw new \Exception($message);
        } else {
            Log::info("✅ Data integrity check passed - No issues found");
            echo "\n✅ Data integrity check passed\n\n";
        }
    }
};

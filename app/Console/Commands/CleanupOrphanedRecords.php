<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanupOrphanedRecords extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'certipro:cleanup-orphans 
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--force : Skip confirmation prompts}
                            {--table= : Specific table to clean (pendaftaran, asesmen, keputusan, sertifikat, all)}';

    /**
     * The console command description.
     */
    protected $description = 'Clean up orphaned records safely (with dry-run mode)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $table = $this->option('table') ?? 'all';
        
        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No data will be deleted');
        } else {
            $this->error('⚠️  LIVE MODE - Data will be permanently deleted!');
            
            if (!$force && !$this->confirm('Are you sure you want to proceed?')) {
                $this->info('Operation cancelled.');
                return 1;
            }
        }
        
        $this->newLine();
        $this->info('🧹 Starting cleanup process...');
        $this->newLine();
        
        $totalDeleted = 0;
        
        if ($table === 'all' || $table === 'pendaftaran') {
            $totalDeleted += $this->cleanupPendaftaran($dryRun, $force);
        }
        
        if ($table === 'all' || $table === 'asesmen') {
            $totalDeleted += $this->cleanupAsesmen($dryRun, $force);
        }
        
        if ($table === 'all' || $table === 'keputusan') {
            $totalDeleted += $this->cleanupKeputusan($dryRun, $force);
        }
        
        if ($table === 'all' || $table === 'sertifikat') {
            $totalDeleted += $this->cleanupSertifikat($dryRun, $force);
        }
        
        // Archive old drafts
        if ($table === 'all' || $table === 'archive') {
            $totalDeleted += $this->archiveOldDrafts($dryRun, $force);
        }
        
        // Summary
        $this->newLine();
        $this->displaySummary($totalDeleted, $dryRun);
        
        return 0;
    }
    
    /**
     * Cleanup orphaned pendaftaran records
     */
    protected function cleanupPendaftaran(bool $dryRun, bool $force): int
    {
        $this->line('📋 Cleaning pendaftaran_sertifikasi...');
        
        $deleted = 0;
        
        // Find orphaned records (no user or no skema, AND status = draft/ditolak)
        $orphaned = DB::select("
            SELECT p.id, p.nomor_pendaftaran, p.status, p.created_at
            FROM pendaftaran_sertifikasi p
            LEFT JOIN users u ON p.user_id = u.id
            LEFT JOIN skema_sertifikasi s ON p.skema_sertifikasi_id = s.id
            WHERE (p.user_id IS NULL OR u.id IS NULL OR p.skema_sertifikasi_id IS NULL OR s.id IS NULL)
              AND p.status IN ('draft', 'ditolak')
        ");
        
        if (count($orphaned) > 0) {
            $this->warn("  Found " . count($orphaned) . " orphaned draft/rejected records");
            
            if ($dryRun) {
                foreach (array_slice($orphaned, 0, 5) as $record) {
                    $this->line("    Would delete: ID {$record->id}, {$record->nomor_pendaftaran} ({$record->status})");
                }
                if (count($orphaned) > 5) {
                    $this->line("    ... and " . (count($orphaned) - 5) . " more");
                }
            } else {
                if ($force || $this->confirm('Delete these orphaned pendaftaran?', false)) {
                    $ids = array_column($orphaned, 'id');
                    DB::table('pendaftaran_sertifikasi')->whereIn('id', $ids)->delete();
                    $deleted = count($ids);
                    $this->info("  ✅ Deleted {$deleted} orphaned pendaftaran");
                }
            }
        } else {
            $this->info("  ✅ No orphaned pendaftaran found");
        }
        
        $this->newLine();
        return $deleted;
    }
    
    /**
     * Cleanup orphaned asesmen records
     */
    protected function cleanupAsesmen(bool $dryRun, bool $force): int
    {
        $this->line('📋 Cleaning asesmen...');
        
        $deleted = 0;
        
        // Find asesmen without valid pendaftaran
        $orphaned = DB::select("
            SELECT a.id, a.pendaftaran_id, a.tanggal_asesmen
            FROM asesmen a
            LEFT JOIN pendaftaran_sertifikasi p ON a.pendaftaran_id = p.id
            WHERE p.id IS NULL
        ");
        
        if (count($orphaned) > 0) {
            $this->warn("  Found " . count($orphaned) . " orphaned asesmen");
            
            if ($dryRun) {
                foreach (array_slice($orphaned, 0, 5) as $record) {
                    $this->line("    Would delete: Asesmen ID {$record->id}");
                }
            } else {
                if ($force || $this->confirm('Delete these orphaned asesmen?', false)) {
                    $ids = array_column($orphaned, 'id');
                    
                    // Delete related asesmen_detail first
                    DB::table('asesmen_detail')->whereIn('asesmen_id', $ids)->delete();
                    
                    // Then delete asesmen
                    DB::table('asesmen')->whereIn('id', $ids)->delete();
                    $deleted = count($ids);
                    $this->info("  ✅ Deleted {$deleted} orphaned asesmen");
                }
            }
        } else {
            $this->info("  ✅ No orphaned asesmen found");
        }
        
        $this->newLine();
        return $deleted;
    }
    
    /**
     * Cleanup orphaned keputusan records
     */
    protected function cleanupKeputusan(bool $dryRun, bool $force): int
    {
        $this->line('📋 Cleaning keputusan_sertifikasi...');
        
        $deleted = 0;
        
        // Find keputusan without valid asesmen
        $orphaned = DB::select("
            SELECT k.id, k.asesmen_id, k.tanggal_keputusan
            FROM keputusan_sertifikasi k
            LEFT JOIN asesmen a ON k.asesmen_id = a.id
            WHERE a.id IS NULL
        ");
        
        if (count($orphaned) > 0) {
            $this->warn("  Found " . count($orphaned) . " orphaned keputusan");
            
            if ($dryRun) {
                foreach (array_slice($orphaned, 0, 5) as $record) {
                    $this->line("    Would delete: Keputusan ID {$record->id}");
                }
            } else {
                $this->error("  ⚠️  WARNING: Keputusan are legal documents!");
                if ($force || $this->confirm('Are you ABSOLUTELY SURE you want to delete these?', false)) {
                    $ids = array_column($orphaned, 'id');
                    DB::table('keputusan_sertifikasi')->whereIn('id', $ids)->delete();
                    $deleted = count($ids);
                    $this->info("  ✅ Deleted {$deleted} orphaned keputusan");
                } else {
                    $this->info("  Skipped keputusan cleanup (user cancelled)");
                }
            }
        } else {
            $this->info("  ✅ No orphaned keputusan found");
        }
        
        $this->newLine();
        return $deleted;
    }
    
    /**
     * Cleanup orphaned or invalid sertifikat records
     */
    protected function cleanupSertifikat(bool $dryRun, bool $force): int
    {
        $this->line('📋 Cleaning sertifikat...');
        
        $deleted = 0;
        
        // Find certificates without keputusan (CRITICAL!)
        $orphaned = DB::select("
            SELECT s.id, s.nomor_sertifikat, s.nama_peserta
            FROM sertifikat s
            LEFT JOIN keputusan_sertifikasi k ON s.pendaftaran_id = k.pendaftaran_id
            WHERE k.id IS NULL
        ");
        
        if (count($orphaned) > 0) {
            $this->error("  CRITICAL: Found " . count($orphaned) . " certificates without keputusan!");
            
            if ($dryRun) {
                foreach (array_slice($orphaned, 0, 5) as $record) {
                    $this->line("    Would delete: {$record->nomor_sertifikat} ({$record->nama_peserta})");
                }
            } else {
                $this->error("  ⚠️  WARNING: These are LEGAL DOCUMENTS!");
                $this->error("  ⚠️  Deletion should be reviewed by legal/compliance team");
                
                if ($force || $this->confirm('Delete these invalid certificates?', false)) {
                    $ids = array_column($orphaned, 'id');
                    DB::table('sertifikat')->whereIn('id', $ids)->delete();
                    $deleted = count($ids);
                    $this->info("  ✅ Deleted {$deleted} invalid certificates");
                } else {
                    $this->info("  Skipped certificate cleanup (user cancelled)");
                }
            }
        } else {
            $this->info("  ✅ No orphaned certificates found");
        }
        
        // Check for certificates issued for "belum kompeten" decisions
        $invalidCerts = DB::select("
            SELECT s.id, s.nomor_sertifikat, k.keputusan
            FROM sertifikat s
            INNER JOIN keputusan_sertifikasi k ON s.pendaftaran_id = k.pendaftaran_id
            WHERE k.keputusan = 'belum_kompeten'
        ");
        
        if (count($invalidCerts) > 0) {
            $this->error("  INCONSISTENCY: Found " . count($invalidCerts) . " certificates for 'belum kompeten'!");
            
            if ($dryRun) {
                foreach (array_slice($invalidCerts, 0, 5) as $record) {
                    $this->line("    Would revoke: {$record->nomor_sertifikat}");
                }
            } else {
                if ($force || $this->confirm('Revoke these invalid certificates?', false)) {
                    $ids = array_column($invalidCerts, 'id');
                    DB::table('sertifikat')->whereIn('id', $ids)->delete();
                    $deleted += count($ids);
                    $this->info("  ✅ Revoked {count($ids)} invalid certificates");
                }
            }
        }
        
        $this->newLine();
        return $deleted;
    }
    
    /**
     * Archive old draft registrations
     */
    protected function archiveOldDrafts(bool $dryRun, bool $force): int
    {
        $this->line('📋 Archiving old draft registrations...');
        
        $deleted = 0;
        
        // Find drafts older than 1 year
        $oldDrafts = DB::select("
            SELECT id, nomor_pendaftaran, created_at
            FROM pendaftaran_sertifikasi
            WHERE status = 'draft'
              AND created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR)
        ");
        
        if (count($oldDrafts) > 0) {
            $this->warn("  Found " . count($oldDrafts) . " old draft registrations (>1 year)");
            
            if ($dryRun) {
                foreach (array_slice($oldDrafts, 0, 5) as $record) {
                    $this->line("    Would archive: {$record->nomor_pendaftaran} (created: {$record->created_at})");
                }
            } else {
                if ($force || $this->confirm('Archive old drafts?', true)) {
                    // Create archive table if not exists
                    if (!Schema::hasTable('pendaftaran_sertifikasi_archive')) {
                        DB::statement("CREATE TABLE pendaftaran_sertifikasi_archive LIKE pendaftaran_sertifikasi");
                        $this->info("  Created archive table");
                    }
                    
                    $ids = array_column($oldDrafts, 'id');
                    
                    // Copy to archive
                    DB::statement("
                        INSERT INTO pendaftaran_sertifikasi_archive 
                        SELECT * FROM pendaftaran_sertifikasi 
                        WHERE id IN (" . implode(',', $ids) . ")
                    ");
                    
                    // Delete originals
                    DB::table('pendaftaran_sertifikasi')->whereIn('id', $ids)->delete();
                    $deleted = count($ids);
                    $this->info("  ✅ Archived {$deleted} old drafts");
                }
            }
        } else {
            $this->info("  ✅ No old drafts to archive");
        }
        
        $this->newLine();
        return $deleted;
    }
    
    /**
     * Display summary
     */
    protected function displaySummary(int $totalDeleted, bool $dryRun): void
    {
        $this->info('📊 CLEANUP SUMMARY');
        $this->line(str_repeat('=', 60));
        
        if ($dryRun) {
            $this->warn("  DRY RUN: {$totalDeleted} records would be deleted/archived");
            $this->newLine();
            $this->comment("To actually delete data, run without --dry-run:");
            $this->line("  php artisan certipro:cleanup-orphans");
        } else {
            $this->info("  Total records deleted/archived: {$totalDeleted}");
            $this->newLine();
            $this->info("✅ Cleanup completed successfully");
            $this->comment("Next steps:");
            $this->line("  1. Run: php artisan certipro:detect-orphans");
            $this->line("  2. Verify no issues remain");
            $this->line("  3. Run database integrity migrations");
        }
        
        $this->newLine();
    }
}

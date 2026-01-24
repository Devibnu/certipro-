<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DetectOrphanedRecords extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'certipro:detect-orphans 
                            {--table= : Specific table to check (pendaftaran, asesmen, keputusan, sertifikat, all)}
                            {--export= : Export results to file (csv, json)}';

    /**
     * The console command description.
     */
    protected $description = 'Detect orphaned records in certification module (missing FK references)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $table = $this->option('table') ?? 'all';
        $export = $this->option('export');
        
        $this->info('🔍 Starting orphaned records detection...');
        $this->newLine();
        
        $results = [];
        
        if ($table === 'all' || $table === 'pendaftaran') {
            $results['pendaftaran'] = $this->checkPendaftaran();
        }
        
        if ($table === 'all' || $table === 'asesmen') {
            $results['asesmen'] = $this->checkAsesmen();
        }
        
        if ($table === 'all' || $table === 'keputusan') {
            $results['keputusan'] = $this->checkKeputusan();
        }
        
        if ($table === 'all' || $table === 'sertifikat') {
            $results['sertifikat'] = $this->checkSertifikat();
        }
        
        // Summary
        $this->newLine();
        $this->displaySummary($results);
        
        // Export if requested
        if ($export) {
            $this->exportResults($results, $export);
        }
        
        return 0;
    }
    
    /**
     * Check pendaftaran_sertifikasi for orphaned records
     */
    protected function checkPendaftaran(): array
    {
        $this->line('📋 Checking pendaftaran_sertifikasi...');
        
        $issues = [];
        
        // Check user_id
        $orphanedUsers = DB::select("
            SELECT p.id, p.nomor_pendaftaran, p.user_id, p.status, p.created_at
            FROM pendaftaran_sertifikasi p
            LEFT JOIN users u ON p.user_id = u.id
            WHERE p.user_id IS NULL OR u.id IS NULL
        ");
        
        if (count($orphanedUsers) > 0) {
            $this->error("  ❌ Found " . count($orphanedUsers) . " pendaftaran without valid user");
            $issues['missing_user'] = $orphanedUsers;
            
            foreach (array_slice($orphanedUsers, 0, 3) as $record) {
                $this->line("     - ID: {$record->id}, Nomor: {$record->nomor_pendaftaran}, Status: {$record->status}");
            }
            if (count($orphanedUsers) > 3) {
                $this->line("     ... and " . (count($orphanedUsers) - 3) . " more");
            }
        } else {
            $this->info("  ✅ All pendaftaran have valid user_id");
        }
        
        // Check skema_sertifikasi_id
        $orphanedSkema = DB::select("
            SELECT p.id, p.nomor_pendaftaran, p.skema_sertifikasi_id, p.status
            FROM pendaftaran_sertifikasi p
            LEFT JOIN skema_sertifikasi s ON p.skema_sertifikasi_id = s.id
            WHERE p.skema_sertifikasi_id IS NULL OR s.id IS NULL
        ");
        
        if (count($orphanedSkema) > 0) {
            $this->error("  ❌ Found " . count($orphanedSkema) . " pendaftaran without valid skema");
            $issues['missing_skema'] = $orphanedSkema;
            
            foreach (array_slice($orphanedSkema, 0, 3) as $record) {
                $this->line("     - ID: {$record->id}, Nomor: {$record->nomor_pendaftaran}");
            }
            if (count($orphanedSkema) > 3) {
                $this->line("     ... and " . (count($orphanedSkema) - 3) . " more");
            }
        } else {
            $this->info("  ✅ All pendaftaran have valid skema_sertifikasi_id");
        }
        
        $this->newLine();
        return $issues;
    }
    
    /**
     * Check asesmen for orphaned records
     */
    protected function checkAsesmen(): array
    {
        $this->line('📋 Checking asesmen...');
        
        $issues = [];
        
        // Check pendaftaran_id
        $orphanedPendaftaran = DB::select("
            SELECT a.id, a.pendaftaran_id, a.tanggal_asesmen, a.status
            FROM asesmen a
            LEFT JOIN pendaftaran_sertifikasi p ON a.pendaftaran_id = p.id
            WHERE p.id IS NULL
        ");
        
        if (count($orphanedPendaftaran) > 0) {
            $this->error("  ❌ Found " . count($orphanedPendaftaran) . " asesmen without valid pendaftaran");
            $issues['missing_pendaftaran'] = $orphanedPendaftaran;
            
            foreach (array_slice($orphanedPendaftaran, 0, 3) as $record) {
                $this->line("     - Asesmen ID: {$record->id}, Pendaftaran ID: {$record->pendaftaran_id}");
            }
        } else {
            $this->info("  ✅ All asesmen have valid pendaftaran_id");
        }
        
        // Check asesor_id
        $orphanedAsesor = DB::select("
            SELECT a.id, a.asesor_id, a.tanggal_asesmen
            FROM asesmen a
            LEFT JOIN users u ON a.asesor_id = u.id
            WHERE a.asesor_id IS NOT NULL AND u.id IS NULL
        ");
        
        if (count($orphanedAsesor) > 0) {
            $this->error("  ❌ Found " . count($orphanedAsesor) . " asesmen without valid asesor");
            $issues['missing_asesor'] = $orphanedAsesor;
            
            foreach (array_slice($orphanedAsesor, 0, 3) as $record) {
                $this->line("     - Asesmen ID: {$record->id}, Asesor ID: {$record->asesor_id}");
            }
        } else {
            $this->info("  ✅ All asesmen have valid asesor_id");
        }
        
        $this->newLine();
        return $issues;
    }
    
    /**
     * Check keputusan_sertifikasi for orphaned records
     */
    protected function checkKeputusan(): array
    {
        $this->line('📋 Checking keputusan_sertifikasi...');
        
        $issues = [];
        
        // Check asesmen_id
        $orphanedAsesmen = DB::select("
            SELECT k.id, k.asesmen_id, k.keputusan, k.tanggal_keputusan
            FROM keputusan_sertifikasi k
            LEFT JOIN asesmen a ON k.asesmen_id = a.id
            WHERE a.id IS NULL
        ");
        
        if (count($orphanedAsesmen) > 0) {
            $this->error("  ❌ Found " . count($orphanedAsesmen) . " keputusan without valid asesmen");
            $issues['missing_asesmen'] = $orphanedAsesmen;
            
            foreach (array_slice($orphanedAsesmen, 0, 3) as $record) {
                $this->line("     - Keputusan ID: {$record->id}, Asesmen ID: {$record->asesmen_id}");
            }
        } else {
            $this->info("  ✅ All keputusan have valid asesmen_id");
        }
        
        // Check ditetapkan_oleh
        $orphanedPenetap = DB::select("
            SELECT k.id, k.ditetapkan_oleh, k.tanggal_keputusan
            FROM keputusan_sertifikasi k
            LEFT JOIN users u ON k.ditetapkan_oleh = u.id
            WHERE u.id IS NULL
        ");
        
        if (count($orphanedPenetap) > 0) {
            $this->error("  ❌ Found " . count($orphanedPenetap) . " keputusan without valid penetap");
            $issues['missing_penetap'] = $orphanedPenetap;
            
            foreach (array_slice($orphanedPenetap, 0, 3) as $record) {
                $this->line("     - Keputusan ID: {$record->id}, Penetap ID: {$record->ditetapkan_oleh}");
            }
        } else {
            $this->info("  ✅ All keputusan have valid ditetapkan_oleh");
        }
        
        $this->newLine();
        return $issues;
    }
    
    /**
     * Check sertifikat for orphaned records
     */
    protected function checkSertifikat(): array
    {
        $this->line('📋 Checking sertifikat...');
        
        $issues = [];
        
        // Check keputusan (CRITICAL!)
        $orphanedKeputusan = DB::select("
            SELECT s.id, s.nomor_sertifikat, s.nama_peserta, s.tanggal_terbit
            FROM sertifikat s
            LEFT JOIN keputusan_sertifikasi k ON s.pendaftaran_id = k.pendaftaran_id
            WHERE k.id IS NULL
        ");
        
        if (count($orphanedKeputusan) > 0) {
            $this->error("  ❌ CRITICAL: Found " . count($orphanedKeputusan) . " certificates without keputusan!");
            $issues['missing_keputusan'] = $orphanedKeputusan;
            
            foreach (array_slice($orphanedKeputusan, 0, 3) as $record) {
                $this->line("     - Cert: {$record->nomor_sertifikat}, Holder: {$record->nama_peserta}");
            }
        } else {
            $this->info("  ✅ All certificates have valid keputusan");
        }
        
        // Check for "belum kompeten" certificates (inconsistency)
        $invalidCerts = DB::select("
            SELECT s.nomor_sertifikat, k.keputusan, s.tanggal_terbit
            FROM sertifikat s
            INNER JOIN keputusan_sertifikasi k ON s.pendaftaran_id = k.pendaftaran_id
            WHERE k.keputusan = 'belum_kompeten'
        ");
        
        if (count($invalidCerts) > 0) {
            $this->error("  ❌ DATA INCONSISTENCY: Found " . count($invalidCerts) . " certificates for 'belum kompeten' decisions!");
            $issues['invalid_certs'] = $invalidCerts;
            
            foreach (array_slice($invalidCerts, 0, 3) as $record) {
                $this->line("     - Cert: {$record->nomor_sertifikat}, Decision: {$record->keputusan}");
            }
        } else {
            $this->info("  ✅ No certificates issued for 'belum kompeten' decisions");
        }
        
        // Check for duplicate certificates
        $duplicates = DB::select("
            SELECT pendaftaran_id, COUNT(*) as total, GROUP_CONCAT(nomor_sertifikat) as certs
            FROM sertifikat
            GROUP BY pendaftaran_id
            HAVING COUNT(*) > 1
        ");
        
        if (count($duplicates) > 0) {
            $this->error("  ❌ Found " . count($duplicates) . " pendaftaran with multiple certificates!");
            $issues['duplicate_certs'] = $duplicates;
            
            foreach (array_slice($duplicates, 0, 3) as $record) {
                $this->line("     - Pendaftaran ID: {$record->pendaftaran_id}, Certs: {$record->total}");
            }
        } else {
            $this->info("  ✅ No duplicate certificates found");
        }
        
        $this->newLine();
        return $issues;
    }
    
    /**
     * Display summary of findings
     */
    protected function displaySummary(array $results): void
    {
        $this->info('📊 SUMMARY OF FINDINGS');
        $this->line(str_repeat('=', 60));
        
        $totalIssues = 0;
        foreach ($results as $table => $issues) {
            $count = count($issues);
            $totalIssues += $count;
            
            if ($count > 0) {
                $this->error("  {$table}: {$count} issue types found");
            } else {
                $this->info("  {$table}: No issues ✅");
            }
        }
        
        $this->newLine();
        
        if ($totalIssues > 0) {
            $this->error("⚠️  Total issue types: {$totalIssues}");
            $this->newLine();
            $this->comment("Recommendations:");
            $this->line("  1. Review orphaned records manually");
            $this->line("  2. Run: php artisan certipro:cleanup-orphans --dry-run");
            $this->line("  3. After review: php artisan certipro:cleanup-orphans");
            $this->line("  4. Then run DB integrity migrations");
        } else {
            $this->info("🎉 No orphaned records found! Database integrity is good.");
        }
        
        $this->newLine();
    }
    
    /**
     * Export results to file
     */
    protected function exportResults(array $results, string $format): void
    {
        $filename = storage_path('app/orphan-detection-' . now()->format('Y-m-d-His') . '.' . $format);
        
        if ($format === 'json') {
            file_put_contents($filename, json_encode($results, JSON_PRETTY_PRINT));
        } elseif ($format === 'csv') {
            // Flatten results for CSV
            $fp = fopen($filename, 'w');
            fputcsv($fp, ['Table', 'Issue Type', 'Record Count']);
            
            foreach ($results as $table => $issues) {
                foreach ($issues as $issueType => $records) {
                    fputcsv($fp, [$table, $issueType, count($records)]);
                }
            }
            
            fclose($fp);
        }
        
        $this->info("📁 Results exported to: {$filename}");
    }
}

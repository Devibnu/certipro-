<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\PendaftaranSertifikasi;
use App\Models\PraPendaftaran;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BackfillPendaftaranFromPraPendaftaran extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'backfill:pendaftaran-from-pra 
                            {--dry-run : Run without creating data}
                            {--force : Force execution without confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Backfill PendaftaranSertifikasi from PraPendaftaran with status DITERIMA';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $isForce = $this->option('force');
        
        $this->info('🔍 Checking for Pra-Pendaftaran with status DITERIMA without PendaftaranSertifikasi...');
        
        // Get pra-pendaftaran yang DITERIMA tapi belum punya pendaftaran sertifikasi
        $praPendaftaranList = PraPendaftaran::where('status', PraPendaftaran::STATUS_DITERIMA)
            ->whereDoesntHave('pendaftaranSertifikasi')
            ->orderBy('id')
            ->get();
        
        if ($praPendaftaranList->isEmpty()) {
            $this->info('✅ No records to backfill. All DITERIMA Pra-Pendaftaran already have PendaftaranSertifikasi.');
            return 0;
        }
        
        $total = $praPendaftaranList->count();
        $this->warn("📊 Found {$total} record(s) to backfill:");
        
        // Display table
        $tableData = $praPendaftaranList->map(function ($pra) {
            return [
                'ID' => $pra->id,
                'Nomor' => $pra->nomor_pra_pendaftaran,
                'Nama' => $pra->nama_lengkap,
                'Email' => $pra->email,
                'Status' => $pra->status,
            ];
        })->toArray();
        
        $this->table(['ID', 'Nomor', 'Nama', 'Email', 'Status'], $tableData);
        
        if ($isDryRun) {
            $this->info('🔒 DRY RUN MODE - No data will be created.');
            return 0;
        }
        
        if (!$isForce && !$this->confirm('Do you want to create PendaftaranSertifikasi for these records?', true)) {
            $this->info('❌ Operation cancelled.');
            return 1;
        }
        
        $this->info('🚀 Starting backfill process...');
        $this->output->progressStart($total);
        
        $created = 0;
        $failed = 0;
        
        foreach ($praPendaftaranList as $praPendaftaran) {
            try {
                DB::transaction(function () use ($praPendaftaran) {
                    // Double-check to prevent race condition
                    if ($praPendaftaran->hasPendaftaranSertifikasi()) {
                        $this->warn("  ⚠️  Skipping ID {$praPendaftaran->id} - already has PendaftaranSertifikasi");
                        return;
                    }
                    
                    // Generate nomor pendaftaran
                    $nomorPendaftaran = 'PS-' . date('Ymd') . '-' . str_pad($praPendaftaran->id, 5, '0', STR_PAD_LEFT);
                    
                    // CREATE Pendaftaran Sertifikasi
                    $pendaftaran = PendaftaranSertifikasi::create([
                        'pra_pendaftaran_id' => $praPendaftaran->id,
                        'nomor_pendaftaran' => $nomorPendaftaran,
                        'nama_lengkap' => $praPendaftaran->nama_lengkap,
                        'email' => $praPendaftaran->email,
                        'no_hp' => $praPendaftaran->no_hp,
                        'tipe_peserta' => $praPendaftaran->tipe_peserta,
                        'nik' => $praPendaftaran->nik,
                        'nim' => $praPendaftaran->nim,
                        'institusi' => $praPendaftaran->institusi,
                        'status' => PendaftaranSertifikasi::STATUS_BELUM_PILIH_SKEMA,
                        'tanggal_daftar' => now(),
                    ]);
                    
                    Log::info('[Backfill] ✅ PendaftaranSertifikasi created', [
                        'pra_id' => $praPendaftaran->id,
                        'pendaftaran_id' => $pendaftaran->id,
                        'nomor_pendaftaran' => $nomorPendaftaran,
                    ]);
                    
                    // Audit log
                    AuditLog::log(
                        AuditLog::ACTION_CREATE,
                        AuditLog::MODULE_PENDAFTARAN,
                        "[BACKFILL] Pendaftaran Sertifikasi dibuat dari Pra-Pendaftaran: {$praPendaftaran->nama_lengkap}",
                        $pendaftaran,
                        null,
                        $pendaftaran->toArray(),
                        [
                            'event' => 'pendaftaran_created_from_pra',
                            'pra_pendaftaran_id' => $praPendaftaran->id,
                            'backfill' => true,
                        ]
                    );
                });
                
                $created++;
                $this->output->progressAdvance();
                
            } catch (\Exception $e) {
                $failed++;
                $this->error("  ❌ Failed to create for ID {$praPendaftaran->id}: " . $e->getMessage());
                Log::error('[Backfill] Failed to create PendaftaranSertifikasi', [
                    'pra_id' => $praPendaftaran->id,
                    'error' => $e->getMessage(),
                ]);
                $this->output->progressAdvance();
            }
        }
        
        $this->output->progressFinish();
        
        $this->newLine();
        $this->info('📊 Backfill Summary:');
        $this->table(
            ['Status', 'Count'],
            [
                ['✅ Created', $created],
                ['❌ Failed', $failed],
                ['📝 Total', $total],
            ]
        );
        
        if ($created > 0) {
            $this->info("✅ Successfully created {$created} PendaftaranSertifikasi record(s).");
        }
        
        if ($failed > 0) {
            $this->error("⚠️  {$failed} record(s) failed. Check laravel.log for details.");
            return 1;
        }
        
        return 0;
    }
}

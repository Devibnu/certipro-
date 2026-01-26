<?php

namespace App\Console\Commands;

use App\Models\Asesmen;
use App\Models\AsesmenDetail;
use App\Models\AsesmenEvidence;
use App\Models\AuditLog;
use App\Models\InAppNotification;
use App\Models\KeputusanSertifikasi;
use App\Models\PendaftaranSertifikasi;
use App\Models\PraPendaftaran;
use App\Models\Sertifikat;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteRetestPeserta extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'retest:delete-peserta {email} {--force : Skip confirmation prompts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'PRODUCTION SAFE: Delete single peserta data for retesting (by email only)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $targetEmail = $this->argument('email');
        
        // =====================================================================
        // SAFETY CHECK 1: Validate email format
        // =====================================================================
        if (!filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
            $this->error('❌ Email tidak valid: ' . $targetEmail);
            return Command::FAILURE;
        }
        
        // =====================================================================
        // SAFETY CHECK 2: Confirm action
        // =====================================================================
        $this->warn('⚠️  PRODUCTION DELETE OPERATION');
        $this->warn('Target Email: ' . $targetEmail);
        $this->newLine();
        
        if (!$this->option('force')) {
            if (!$this->confirm('Apakah Anda YAKIN ingin menghapus SEMUA data peserta ini?', false)) {
                $this->info('❌ Operasi dibatalkan oleh user');
                return Command::FAILURE;
            }
        } else {
            $this->info('⚡ Force mode: Skipping confirmation');
        }
        
        $this->newLine();
        $this->info('🔍 Mencari data peserta...');
        
        // =====================================================================
        // FIND USER
        // =====================================================================
        $user = User::where('email', $targetEmail)->first();
        
        if (!$user) {
            $this->error('❌ User dengan email "' . $targetEmail . '" TIDAK DITEMUKAN');
            $this->info('✅ Tidak ada yang perlu dihapus');
            return Command::SUCCESS;
        }
        
        $this->info('✓ User ditemukan: ' . $user->name . ' (ID: ' . $user->id . ')');
        $this->newLine();
        
        // =====================================================================
        // COUNT DATA BEFORE DELETE (for reporting)
        // =====================================================================
        $counts = [
            'sertifikat' => Sertifikat::whereHas('pendaftaran', function($q) use ($user, $targetEmail) {
                $q->where('user_id', $user->id)
                  ->orWhere('email', $targetEmail);
            })->count(),
            
            'keputusan' => KeputusanSertifikasi::whereHas('pendaftaran', function($q) use ($user, $targetEmail) {
                $q->where('user_id', $user->id)
                  ->orWhere('email', $targetEmail);
            })->count(),
            
            'asesmen' => Asesmen::whereHas('pendaftaran', function($q) use ($user, $targetEmail) {
                $q->where('user_id', $user->id)
                  ->orWhere('email', $targetEmail);
            })->count(),
            
            'pendaftaran' => PendaftaranSertifikasi::where('user_id', $user->id)
                ->orWhere('email', $targetEmail)
                ->count(),
            
            'pra_pendaftaran' => PraPendaftaran::where('email', $targetEmail)->count(),
            
            'notifications' => InAppNotification::where('user_id', $user->id)->count(),
            
            'audit_logs' => AuditLog::where('user_id', $user->id)->count(),
        ];
        
        $this->table(
            ['Entity', 'Count'],
            [
                ['Sertifikat', $counts['sertifikat']],
                ['Keputusan Sertifikasi', $counts['keputusan']],
                ['Asesmen', $counts['asesmen']],
                ['Pendaftaran Sertifikasi', $counts['pendaftaran']],
                ['Pra-Pendaftaran', $counts['pra_pendaftaran']],
                ['In-App Notifications', $counts['notifications']],
                ['Audit Logs', $counts['audit_logs']],
                ['User Account', 1],
            ]
        );
        
        $this->newLine();
        
        if (!$this->option('force')) {
            if (!$this->confirm('Lanjutkan DELETE operasi?', false)) {
                $this->info('❌ Operasi dibatalkan');
                return Command::FAILURE;
            }
        }
        if (!$this->confirm('Lanjutkan DELETE operasi?', false)) {
            $this->info('❌ Operasi dibatalkan');
            return Command::FAILURE;
        }
        
        // =====================================================================
        // DELETE OPERATION (TRANSACTION)
        // =====================================================================
        $this->newLine();
        $this->info('🗑️  Memulai DELETE operation...');
        $this->newLine();
        
        try {
            DB::beginTransaction();
            
            // Get all pendaftaran IDs (needed for cascade deletes)
            $pendaftaranIds = PendaftaranSertifikasi::where('user_id', $user->id)
                ->orWhere('email', $targetEmail)
                ->pluck('id')
                ->toArray();
            
            // 1. DELETE SERTIFIKAT
            $deletedSertifikat = 0;
            if (!empty($pendaftaranIds)) {
                $deletedSertifikat = Sertifikat::whereIn('pendaftaran_id', $pendaftaranIds)->delete();
            }
            $this->line('  ✓ Sertifikat deleted: ' . $deletedSertifikat);
            
            // 2. DELETE KEPUTUSAN SERTIFIKASI
            $deletedKeputusan = 0;
            if (!empty($pendaftaranIds)) {
                $deletedKeputusan = KeputusanSertifikasi::whereIn('pendaftaran_id', $pendaftaranIds)->delete();
            }
            $this->line('  ✓ Keputusan Sertifikasi deleted: ' . $deletedKeputusan);
            
            // 3. DELETE ASESMEN (with details & evidence)
            $deletedAsesmen = 0;
            if (!empty($pendaftaranIds)) {
                $asesmenIds = Asesmen::whereIn('pendaftaran_id', $pendaftaranIds)->pluck('id')->toArray();
                
                if (!empty($asesmenIds)) {
                    // Delete asesmen evidence
                    $deletedEvidence = AsesmenEvidence::whereIn('asesmen_id', $asesmenIds)->delete();
                    $this->line('  ✓ Asesmen Evidence deleted: ' . $deletedEvidence);
                    
                    // Delete asesmen details
                    $deletedDetails = AsesmenDetail::whereIn('asesmen_id', $asesmenIds)->delete();
                    $this->line('  ✓ Asesmen Details deleted: ' . $deletedDetails);
                    
                    // Delete asesmen
                    $deletedAsesmen = Asesmen::whereIn('id', $asesmenIds)->delete();
                }
            }
            $this->line('  ✓ Asesmen deleted: ' . $deletedAsesmen);
            
            // 4. DELETE PENDAFTARAN SERTIFIKASI
            $deletedPendaftaran = PendaftaranSertifikasi::where('user_id', $user->id)
                ->orWhere('email', $targetEmail)
                ->delete();
            $this->line('  ✓ Pendaftaran Sertifikasi deleted: ' . $deletedPendaftaran);
            
            // 5. DELETE PRA-PENDAFTARAN
            $deletedPraPendaftaran = PraPendaftaran::where('email', $targetEmail)->delete();
            $this->line('  ✓ Pra-Pendaftaran deleted: ' . $deletedPraPendaftaran);
            
            // 6. DELETE IN-APP NOTIFICATIONS
            $deletedNotifications = InAppNotification::where('user_id', $user->id)->delete();
            $this->line('  ✓ In-App Notifications deleted: ' . $deletedNotifications);
            
            // 7. DELETE AUDIT LOGS
            $deletedAuditLogs = AuditLog::where('user_id', $user->id)->delete();
            $this->line('  ✓ Audit Logs deleted: ' . $deletedAuditLogs);
            
            // 8. DELETE USER (LAST!)
            $userName = $user->name;
            $userId = $user->id;
            $user->delete();
            $this->line('  ✓ User account deleted: ' . $userName);
            
            // =====================================================================
            // AUDIT LOG (MANUAL - outside transaction for safety)
            // =====================================================================
            Log::warning('[RETEST DELETE] Peserta data deleted for retesting', [
                'email' => $targetEmail,
                'user_id' => $userId,
                'user_name' => $userName,
                'deleted_at' => now()->toDateTimeString(),
                'deleted_by' => 'Console Command',
                'counts' => [
                    'sertifikat' => $deletedSertifikat,
                    'keputusan' => $deletedKeputusan,
                    'asesmen' => $deletedAsesmen,
                    'pendaftaran' => $deletedPendaftaran,
                    'pra_pendaftaran' => $deletedPraPendaftaran,
                    'notifications' => $deletedNotifications,
                    'audit_logs' => $deletedAuditLogs,
                ],
            ]);
            
            DB::commit();
            
            $this->newLine();
            $this->info('✅ DELETE operation SUCCESSFUL');
            $this->newLine();
            
            // =====================================================================
            // VERIFICATION
            // =====================================================================
            $this->info('🔍 Verifying deletion...');
            
            $stillExists = User::where('email', $targetEmail)->exists();
            
            if ($stillExists) {
                $this->error('❌ VERIFICATION FAILED: User masih ada di database!');
                return Command::FAILURE;
            }
            
            $this->info('✓ Verification passed: Email tidak ditemukan di database');
            $this->newLine();
            
            $this->info('🎯 SISTEM SIAP UNTUK RETESTING');
            $this->info('Email: ' . $targetEmail . ' dapat digunakan kembali');
            
            return Command::SUCCESS;
            
        } catch (\Throwable $e) {
            DB::rollBack();
            
            $this->newLine();
            $this->error('❌ DELETE OPERATION FAILED');
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();
            
            Log::error('[RETEST DELETE FAILED]', [
                'email' => $targetEmail,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return Command::FAILURE;
        }
    }
}

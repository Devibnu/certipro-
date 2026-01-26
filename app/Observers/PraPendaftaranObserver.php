<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\PendaftaranSertifikasi;
use App\Models\PraPendaftaran;
use App\Services\PraPendaftaranNotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PraPendaftaranObserver
{
    /**
     * Handle the PraPendaftaran "created" event.
     */
    public function created(PraPendaftaran $praPendaftaran): void
    {
        Log::info('[Observer] PraPendaftaran created event FIRED', [
            'pra_id' => $praPendaftaran->id,
            'email' => $praPendaftaran->email,
        ]);
        
        AuditLog::log(
            AuditLog::ACTION_CREATE,
            AuditLog::MODULE_PRA_PENDAFTARAN,
            "Pra-pendaftaran baru dari {$praPendaftaran->nama_lengkap} ({$praPendaftaran->email})",
            $praPendaftaran,
            null,
            $praPendaftaran->toArray(),
            [
                'event' => 'pra_pendaftaran_dibuat',
                'tipe_peserta' => $praPendaftaran->tipe_peserta,
                'nomor_pra_pendaftaran' => $praPendaftaran->nomor_pra_pendaftaran,
            ]
        );

        // Send notification to peserta
        PraPendaftaranNotificationService::sendCreatedNotification($praPendaftaran);
        
        Log::info('[Observer] Notification service CALLED', [
            'pra_id' => $praPendaftaran->id,
        ]);
    }

    /**
     * Handle the PraPendaftaran "updated" event.
     */
    public function updated(PraPendaftaran $praPendaftaran): void
    {
        $oldValues = $praPendaftaran->getOriginal();
        $newValues = $praPendaftaran->getChanges();
        
        // Check if status changed
        $statusChanged = isset($newValues['status']);
        $oldStatus = $oldValues['status'] ?? null;
        $newStatus = $newValues['status'] ?? null;
        
        // ========================================================================
        // ONLY 3 VALID STATUS TRANSITIONS (Clean Architecture)
        // ========================================================================
        // MENUNGGU_VERIFIKASI → DITERIMA (Admin approves)
        // MENUNGGU_VERIFIKASI → DITOLAK (Admin rejects)
        // No other transitions allowed
        // ========================================================================
        
        if ($statusChanged && $newStatus === PraPendaftaran::STATUS_DITERIMA) {
            // ✅ VALID: Admin approved after document verification
            $action = AuditLog::ACTION_VERIFY;
            $description = "Pra-pendaftaran {$praPendaftaran->nama_lengkap} DITERIMA. Auto-creating Pendaftaran Sertifikasi.";
            $event = 'pra_pendaftaran_diterima';
            
            // ========================================================================
            // IDEMPOTENT CHECK: Only process if truly new transition
            // ========================================================================
            if ($oldStatus !== $newStatus) {
                
                // ====================================================================
                // AUTO-CREATE PENDAFTARAN SERTIFIKASI (IDEMPOTENT)
                // ====================================================================
                DB::transaction(function () use ($praPendaftaran) {
                    
                    // GUARD: Prevent duplicate creation
                    if ($praPendaftaran->hasPendaftaranSertifikasi()) {
                        Log::warning('[Observer] Pendaftaran Sertifikasi already exists', [
                            'pra_id' => $praPendaftaran->id,
                        ]);
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
                    
                    Log::info('[Observer] ✅ PendaftaranSertifikasi AUTO-CREATED', [
                        'pra_id' => $praPendaftaran->id,
                        'pendaftaran_id' => $pendaftaran->id,
                        'nomor_pendaftaran' => $nomorPendaftaran,
                    ]);
                    
                    // Audit log
                    AuditLog::log(
                        AuditLog::ACTION_CREATE,
                        AuditLog::MODULE_PENDAFTARAN,
                        "Pendaftaran Sertifikasi dibuat otomatis dari Pra-Pendaftaran: {$praPendaftaran->nama_lengkap}",
                        $pendaftaran,
                        null,
                        $pendaftaran->toArray(),
                        [
                            'event' => 'pendaftaran_created_from_pra',
                            'pra_pendaftaran_id' => $praPendaftaran->id,
                        ]
                    );
                });
                
                // Send acceptance notification (DIRECT, NO QUEUE)
                PraPendaftaranNotificationService::sendAcceptedNotification($praPendaftaran);
                
                Log::info('[Observer] ✅ Email DITERIMA sent', [
                    'pra_id' => $praPendaftaran->id,
                ]);
            }
            
        } elseif ($statusChanged && $newStatus === PraPendaftaran::STATUS_DITOLAK) {
            // ❌ VALID: Admin rejected with reason
            $action = AuditLog::ACTION_REJECT;
            $description = "Pra-pendaftaran {$praPendaftaran->nama_lengkap} DITOLAK. Alasan: {$praPendaftaran->alasan_penolakan}";
            $event = 'pra_pendaftaran_ditolak';
            
            // ========================================================================
            // IDEMPOTENT EMAIL CHECK
            // Only send email if old_status !== new_status (true state transition)
            // ========================================================================
            if ($oldStatus !== $newStatus) {
                // Send rejection notification with reason
                PraPendaftaranNotificationService::sendRejectedNotification($praPendaftaran);
            }
            
        } else {
            // Other updates (non-status changes)
            $action = AuditLog::ACTION_UPDATE;
            $description = "Pra-pendaftaran {$praPendaftaran->nama_lengkap} diperbarui (data non-status)";
            $event = 'pra_pendaftaran_diperbarui';
        }
        
        AuditLog::log(
            $action,
            AuditLog::MODULE_PRA_PENDAFTARAN,
            $description,
            $praPendaftaran,
            $oldValues,
            $newValues,
            [
                'event' => $event,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'status_changed' => $statusChanged,
                'alasan_penolakan' => $praPendaftaran->alasan_penolakan,
            ]
        );
    }

    /**
     * Handle the PraPendaftaran "deleted" event.
     */
    public function deleted(PraPendaftaran $praPendaftaran): void
    {
        AuditLog::log(
            AuditLog::ACTION_DELETE,
            AuditLog::MODULE_PRA_PENDAFTARAN,
            "Pra-pendaftaran {$praPendaftaran->nama_lengkap} dihapus",
            $praPendaftaran,
            $praPendaftaran->toArray(),
            null,
            ['event' => 'pra_pendaftaran_dihapus']
        );
    }
}

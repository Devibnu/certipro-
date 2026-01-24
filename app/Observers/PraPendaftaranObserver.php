<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\PraPendaftaran;
use App\Services\PraPendaftaranNotificationService;
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
            $description = "Pra-pendaftaran {$praPendaftaran->nama_lengkap} DITERIMA. Menunggu penetapan skema di modul Pendaftaran Sertifikasi.";
            $event = 'pra_pendaftaran_diterima';
            
            // ========================================================================
            // IDEMPOTENT EMAIL CHECK
            // Only send email if old_status !== new_status (true state transition)
            // ========================================================================
            if ($oldStatus !== $newStatus) {
                // Send acceptance notification: "Menunggu penetapan skema"
                PraPendaftaranNotificationService::sendAcceptedNotification($praPendaftaran);
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

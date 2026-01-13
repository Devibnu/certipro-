<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\PraPendaftaran;
use App\Services\PraPendaftaranNotificationService;

class PraPendaftaranObserver
{
    /**
     * Handle the PraPendaftaran "created" event.
     */
    public function created(PraPendaftaran $praPendaftaran): void
    {
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
        
        // Determine action and description based on new status
        if ($statusChanged && $newStatus === PraPendaftaran::STATUS_DITERIMA) {
            $action = AuditLog::ACTION_VERIFY;
            $description = "Pra-pendaftaran {$praPendaftaran->nama_lengkap} diterima (diverifikasi)";
            $event = 'pra_pendaftaran_diterima';
            
            // Send acceptance notification
            PraPendaftaranNotificationService::sendAcceptedNotification($praPendaftaran);
            
        } elseif ($statusChanged && $newStatus === PraPendaftaran::STATUS_DITOLAK) {
            $action = AuditLog::ACTION_REJECT;
            $description = "Pra-pendaftaran {$praPendaftaran->nama_lengkap} ditolak";
            $event = 'pra_pendaftaran_ditolak';
            
            // Send rejection notification
            PraPendaftaranNotificationService::sendRejectedNotification($praPendaftaran);
            
        } elseif ($statusChanged && $newStatus === PraPendaftaran::STATUS_DIPROSES) {
            $action = AuditLog::ACTION_UPDATE;
            $description = "Pra-pendaftaran {$praPendaftaran->nama_lengkap} sedang diverifikasi";
            $event = 'pra_pendaftaran_diproses';
            
        } else {
            $action = AuditLog::ACTION_UPDATE;
            $description = "Pra-pendaftaran {$praPendaftaran->nama_lengkap} diperbarui";
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

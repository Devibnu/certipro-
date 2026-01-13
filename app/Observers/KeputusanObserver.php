<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\KeputusanSertifikasi;

class KeputusanObserver
{
    /**
     * Handle the KeputusanSertifikasi "created" event.
     */
    public function created(KeputusanSertifikasi $keputusan): void
    {
        $asesiName = $keputusan->pendaftaran->user->name ?? 'Unknown';
        $penetapName = $keputusan->penetap->name ?? 'Unknown';
        $keputusanText = strtoupper($keputusan->keputusan);
        
        AuditLog::log(
            AuditLog::ACTION_DECIDE,
            AuditLog::MODULE_KEPUTUSAN,
            "Keputusan sertifikasi ditetapkan: {$asesiName} dinyatakan {$keputusanText} oleh {$penetapName}",
            $keputusan,
            null,
            $keputusan->toArray(),
            [
                'event' => 'keputusan_ditetapkan',
                'pendaftaran_id' => $keputusan->pendaftaran_id,
                'keputusan' => $keputusan->keputusan,
                'ditetapkan_oleh' => $keputusan->ditetapkan_oleh,
                'tanggal_keputusan' => $keputusan->tanggal_keputusan?->format('Y-m-d'),
            ]
        );
    }

    /**
     * Handle the KeputusanSertifikasi "updated" event.
     */
    public function updated(KeputusanSertifikasi $keputusan): void
    {
        $oldValues = $keputusan->getOriginal();
        $newValues = $keputusan->getChanges();
        $asesiName = $keputusan->pendaftaran->user->name ?? 'Unknown';
        
        $event = 'keputusan_diperbarui';
        $description = "Keputusan sertifikasi untuk {$asesiName} diperbarui";
        
        // Check if locked
        if (isset($newValues['is_locked']) && $newValues['is_locked'] == true) {
            $event = 'keputusan_dikunci';
            $description = "Keputusan sertifikasi untuk {$asesiName} dikunci (final)";
        }
        
        // Check if keputusan changed
        if (isset($newValues['keputusan'])) {
            $keputusanText = strtoupper($newValues['keputusan']);
            $event = 'keputusan_diubah';
            $description = "Keputusan sertifikasi untuk {$asesiName} diubah menjadi {$keputusanText}";
        }
        
        AuditLog::log(
            AuditLog::ACTION_UPDATE,
            AuditLog::MODULE_KEPUTUSAN,
            $description,
            $keputusan,
            $oldValues,
            $newValues,
            [
                'event' => $event,
                'is_locked' => $keputusan->is_locked,
            ]
        );
    }

    /**
     * Handle the KeputusanSertifikasi "deleted" event.
     */
    public function deleted(KeputusanSertifikasi $keputusan): void
    {
        $asesiName = $keputusan->pendaftaran->user->name ?? 'Unknown';
        
        AuditLog::log(
            AuditLog::ACTION_DELETE,
            AuditLog::MODULE_KEPUTUSAN,
            "Keputusan sertifikasi untuk {$asesiName} dihapus",
            $keputusan,
            $keputusan->toArray(),
            null,
            ['event' => 'keputusan_dihapus']
        );
    }
}

<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\PendaftaranSertifikasi;

class PendaftaranObserver
{
    /**
     * Handle the PendaftaranSertifikasi "created" event.
     */
    public function created(PendaftaranSertifikasi $pendaftaran): void
    {
        $userName = $pendaftaran->user->name ?? $pendaftaran->nama_lengkap ?? 'Unknown';
        $skemaName = $pendaftaran->skemaSertifikasi->nama_skema ?? 'Unknown';
        
        // Auto-set status peserta on creation
        $pendaftaran->updateStatusPeserta();
        
        AuditLog::log(
            AuditLog::ACTION_CREATE,
            AuditLog::MODULE_PENDAFTARAN,
            "Pendaftaran baru: {$userName} mendaftar skema {$skemaName}",
            $pendaftaran,
            null,
            $pendaftaran->toArray(),
            [
                'event' => 'pendaftaran_dibuat',
                'user_id' => $pendaftaran->user_id,
                'skema_id' => $pendaftaran->skema_sertifikasi_id,
                'status_peserta' => $pendaftaran->status_peserta,
            ]
        );
    }

    /**
     * Handle the PendaftaranSertifikasi "updated" event.
     */
    public function updated(PendaftaranSertifikasi $pendaftaran): void
    {
        $oldValues = $pendaftaran->getOriginal();
        $newValues = $pendaftaran->getChanges();
        $userName = $pendaftaran->user->name ?? $pendaftaran->nama_lengkap ?? 'Unknown';
        
        // Determine event type based on status change
        $event = 'pendaftaran_diperbarui';
        $action = AuditLog::ACTION_UPDATE;
        $description = "Pendaftaran {$userName} diperbarui";
        
        if (isset($newValues['status'])) {
            $newStatus = $newValues['status'];
            $oldStatus = $oldValues['status'] ?? null;
            
            // Auto-update status peserta when internal status changes
            $pendaftaran->updateStatusPeserta();
            
            switch ($newStatus) {
                case PendaftaranSertifikasi::STATUS_DIVERIFIKASI:
                    $event = 'pendaftaran_diverifikasi';
                    $action = AuditLog::ACTION_VERIFY;
                    $description = "Pendaftaran {$userName} diverifikasi - Status Peserta: DITERIMA";
                    break;
                    
                case PendaftaranSertifikasi::STATUS_SIAP_ASESMEN:
                    $event = 'siap_asesmen';
                    $action = AuditLog::ACTION_UPDATE;
                    $description = "Pendaftaran {$userName} siap untuk asesmen - Status Peserta: DIJADWALKAN_ASESMEN";
                    break;
                    
                case PendaftaranSertifikasi::STATUS_MENUNGGU_KEPUTUSAN:
                    $event = 'menunggu_keputusan';
                    $action = AuditLog::ACTION_UPDATE;
                    $description = "Asesmen selesai untuk {$userName} - Status Peserta: MENUNGGU_KEPUTUSAN";
                    break;
                    
                case PendaftaranSertifikasi::STATUS_KOMPETEN_FINAL:
                    $event = 'keputusan_kompeten';
                    $action = AuditLog::ACTION_DECIDE;
                    $description = "Keputusan FINAL: {$userName} dinyatakan KOMPETEN - Status Peserta: LULUS_SERTIFIKASI";
                    break;
                    
                case PendaftaranSertifikasi::STATUS_BELUM_KOMPETEN:
                    $event = 'sedang_asesmen';
                    $action = AuditLog::ACTION_ASSESS;
                    $description = "Asesmen berlangsung untuk {$userName} - Status Peserta: SEDANG_ASESMEN";
                    break;
                    
                case PendaftaranSertifikasi::STATUS_BELUM_KOMPETEN_FINAL:
                    $event = 'keputusan_belum_kompeten';
                    $action = AuditLog::ACTION_DECIDE;
                    $description = "Keputusan FINAL: {$userName} dinyatakan BELUM KOMPETEN - Status Peserta: TIDAK_LULUS";
                    break;
                    
                case PendaftaranSertifikasi::STATUS_DITOLAK:
                    $event = 'pendaftaran_ditolak';
                    $action = AuditLog::ACTION_REJECT;
                    $description = "Pendaftaran {$userName} ditolak - Status Peserta: DITOLAK";
                    break;
            }
        }
        
        AuditLog::log(
            $action,
            AuditLog::MODULE_PENDAFTARAN,
            $description,
            $pendaftaran,
            $oldValues,
            $newValues,
            [
                'event' => $event,
                'old_status' => $oldStatus ?? null,
                'new_status' => $newValues['status'] ?? null,
                'status_peserta' => $pendaftaran->status_peserta,
            ]
        );
    }

    /**
     * Handle the PendaftaranSertifikasi "deleted" event.
     */
    public function deleted(PendaftaranSertifikasi $pendaftaran): void
    {
        $userName = $pendaftaran->user->name ?? $pendaftaran->nama_lengkap ?? 'Unknown';
        
        AuditLog::log(
            AuditLog::ACTION_DELETE,
            AuditLog::MODULE_PENDAFTARAN,
            "Pendaftaran {$userName} dihapus dari sistem",
            $pendaftaran,
            $pendaftaran->toArray(),
            null,
            ['event' => 'pendaftaran_dihapus']
        );
    }
}

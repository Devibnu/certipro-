<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Sertifikat;

class SertifikatObserver
{
    /**
     * Handle the Sertifikat "created" event.
     */
    public function created(Sertifikat $sertifikat): void
    {
        $penerbitName = $sertifikat->penerbit->name ?? 'System';
        
        AuditLog::log(
            AuditLog::ACTION_ISSUE,
            AuditLog::MODULE_SERTIFIKAT,
            "Sertifikat diterbitkan: {$sertifikat->nomor_sertifikat} untuk {$sertifikat->nama_peserta}",
            $sertifikat,
            null,
            $sertifikat->toArray(),
            [
                'event' => 'sertifikat_diterbitkan',
                'nomor_sertifikat' => $sertifikat->nomor_sertifikat,
                'uuid' => $sertifikat->uuid,
                'nama_peserta' => $sertifikat->nama_peserta,
                'skema_sertifikasi' => $sertifikat->skema_sertifikasi,
                'tanggal_terbit' => $sertifikat->tanggal_terbit?->format('Y-m-d'),
                'tanggal_berlaku_sampai' => $sertifikat->tanggal_berlaku_sampai?->format('Y-m-d'),
                'diterbitkan_oleh' => $penerbitName,
            ]
        );
    }

    /**
     * Handle the Sertifikat "updated" event.
     */
    public function updated(Sertifikat $sertifikat): void
    {
        $oldValues = $sertifikat->getOriginal();
        $newValues = $sertifikat->getChanges();
        
        $event = 'sertifikat_diperbarui';
        $action = AuditLog::ACTION_UPDATE;
        $description = "Sertifikat {$sertifikat->nomor_sertifikat} diperbarui";
        
        // Check if revoked (if there's a revoked status)
        if (isset($newValues['status']) && $newValues['status'] === 'dicabut') {
            $event = 'sertifikat_dicabut';
            $action = AuditLog::ACTION_REVOKE;
            $description = "Sertifikat {$sertifikat->nomor_sertifikat} untuk {$sertifikat->nama_peserta} DICABUT";
        }
        
        // Check if PDF regenerated
        if (isset($newValues['file_pdf'])) {
            $event = 'sertifikat_regenerated';
            $description = "PDF sertifikat {$sertifikat->nomor_sertifikat} di-regenerate";
        }
        
        AuditLog::log(
            $action,
            AuditLog::MODULE_SERTIFIKAT,
            $description,
            $sertifikat,
            $oldValues,
            $newValues,
            [
                'event' => $event,
                'nomor_sertifikat' => $sertifikat->nomor_sertifikat,
            ]
        );
    }

    /**
     * Handle the Sertifikat "deleted" event.
     */
    public function deleted(Sertifikat $sertifikat): void
    {
        AuditLog::log(
            AuditLog::ACTION_DELETE,
            AuditLog::MODULE_SERTIFIKAT,
            "Sertifikat {$sertifikat->nomor_sertifikat} untuk {$sertifikat->nama_peserta} dihapus",
            $sertifikat,
            $sertifikat->toArray(),
            null,
            [
                'event' => 'sertifikat_dihapus',
                'nomor_sertifikat' => $sertifikat->nomor_sertifikat,
                'uuid' => $sertifikat->uuid,
            ]
        );
    }
}

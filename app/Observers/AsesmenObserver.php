<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Asesmen;

class AsesmenObserver
{
    /**
     * Handle the Asesmen "created" event.
     */
    public function created(Asesmen $asesmen): void
    {
        $asesiName = $asesmen->pendaftaran->user->name ?? 'Unknown';
        $asesorName = $asesmen->asesor->name ?? 'Unknown';
        
        AuditLog::log(
            AuditLog::ACTION_ASSESS,
            AuditLog::MODULE_ASESMEN,
            "Asesmen dibuat untuk {$asesiName} oleh asesor {$asesorName}",
            $asesmen,
            null,
            $asesmen->toArray(),
            [
                'event' => 'asesmen_dibuat',
                'pendaftaran_id' => $asesmen->pendaftaran_id,
                'asesor_id' => $asesmen->asesor_id,
            ]
        );
    }

    /**
     * Handle the Asesmen "updated" event.
     */
    public function updated(Asesmen $asesmen): void
    {
        $oldValues = $asesmen->getOriginal();
        $newValues = $asesmen->getChanges();
        $asesiName = $asesmen->pendaftaran->user->name ?? 'Unknown';
        
        $event = 'asesmen_diperbarui';
        $action = AuditLog::ACTION_UPDATE;
        $description = "Asesmen untuk {$asesiName} diperbarui";
        
        // Check if status changed
        if (isset($newValues['status'])) {
            $newStatus = $newValues['status'];
            
            switch ($newStatus) {
                case 'selesai':
                case 'completed':
                    $event = 'asesmen_disimpan';
                    $action = AuditLog::ACTION_ASSESS;
                    $description = "Asesmen untuk {$asesiName} disimpan dan diselesaikan";
                    break;
                    
                case 'in_progress':
                case 'berlangsung':
                    $event = 'asesmen_dimulai';
                    $action = AuditLog::ACTION_ASSESS;
                    $description = "Asesmen untuk {$asesiName} dimulai";
                    break;
            }
        }
        
        // Check if rekomendasi changed (final assessment)
        if (isset($newValues['rekomendasi'])) {
            $event = 'asesmen_disimpan';
            $action = AuditLog::ACTION_ASSESS;
            $rekomendasi = strtoupper($newValues['rekomendasi']);
            $description = "Asesmen untuk {$asesiName} disimpan dengan rekomendasi: {$rekomendasi}";
        }
        
        AuditLog::log(
            $action,
            AuditLog::MODULE_ASESMEN,
            $description,
            $asesmen,
            $oldValues,
            $newValues,
            [
                'event' => $event,
                'rekomendasi' => $asesmen->rekomendasi ?? null,
            ]
        );
    }

    /**
     * Handle the Asesmen "deleted" event.
     */
    public function deleted(Asesmen $asesmen): void
    {
        $asesiName = $asesmen->pendaftaran->user->name ?? 'Unknown';
        
        AuditLog::log(
            AuditLog::ACTION_DELETE,
            AuditLog::MODULE_ASESMEN,
            "Asesmen untuk {$asesiName} dihapus",
            $asesmen,
            $asesmen->toArray(),
            null,
            ['event' => 'asesmen_dihapus']
        );
    }
}

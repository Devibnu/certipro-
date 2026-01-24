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
                    
                    // AUTO-CREATE KEPUTUSAN when asesmen is completed
                    $this->autoCreateKeputusan($asesmen);
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
            
            // AUTO-CREATE KEPUTUSAN when rekomendasi is set
            $this->autoCreateKeputusan($asesmen);
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
     * Auto-create KeputusanSertifikasi when asesmen is completed.
     * 
     * Business Rule:
     * - Only create if keputusan doesn't exist yet
     * - Determine KOMPETEN/BELUM_KOMPETEN based on all KUK results
     * - Update pendaftaran status automatically
     */
    private function autoCreateKeputusan(Asesmen $asesmen): void
    {
        $pendaftaran = $asesmen->pendaftaran()->with('keputusan')->first();
        
        if (!$pendaftaran) {
            \Log::warning('Cannot auto-create keputusan: pendaftaran not found', [
                'asesmen_id' => $asesmen->id,
            ]);
            return;
        }
        
        // Check if keputusan already exists
        if ($pendaftaran->keputusan) {
            \Log::info('Keputusan already exists, skipping auto-create', [
                'asesmen_id' => $asesmen->id,
                'pendaftaran_id' => $pendaftaran->id,
                'keputusan_id' => $pendaftaran->keputusan->id,
            ]);
            return;
        }
        
        // Determine if KOMPETEN based on all KUK results
        $isKompeten = $this->isAsesmenKompeten($asesmen);
        
        try {
            $keputusan = \App\Models\KeputusanSertifikasi::create([
                'pendaftaran_id' => $pendaftaran->id,
                'asesmen_id' => $asesmen->id,
                'keputusan' => $isKompeten 
                    ? \App\Models\KeputusanSertifikasi::KEPUTUSAN_KOMPETEN 
                    : \App\Models\KeputusanSertifikasi::KEPUTUSAN_BELUM_KOMPETEN,
                'catatan_komite' => 'Keputusan otomatis berdasarkan hasil asesmen.',
                'ditetapkan_oleh' => auth()->id() ?? 1, // System user
                'tanggal_keputusan' => now(),
                'is_locked' => true,
            ]);
            
            // Update pendaftaran status
            $newStatus = $isKompeten
                ? \App\Models\PendaftaranSertifikasi::STATUS_KOMPETEN_FINAL
                : \App\Models\PendaftaranSertifikasi::STATUS_BELUM_KOMPETEN_FINAL;
            
            $pendaftaran->update(['status' => $newStatus]);
            
            \Log::info('Auto-created keputusan sertifikasi', [
                'asesmen_id' => $asesmen->id,
                'pendaftaran_id' => $pendaftaran->id,
                'keputusan_id' => $keputusan->id,
                'keputusan' => $keputusan->keputusan,
                'status' => $newStatus,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to auto-create keputusan', [
                'asesmen_id' => $asesmen->id,
                'pendaftaran_id' => $pendaftaran->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Check if all KUK assessments are KOMPETEN.
     */
    private function isAsesmenKompeten(Asesmen $asesmen): bool
    {
        $details = $asesmen->details()->get();
        
        if ($details->isEmpty()) {
            return false;
        }
        
        // All KUK must be kompeten
        foreach ($details as $detail) {
            if ($detail->hasil_asesmen !== 'kompeten') {
                return false;
            }
        }
        
        return true;
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

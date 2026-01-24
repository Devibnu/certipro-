<?php

namespace App\Services;

use App\Enums\PendaftaranStatus;
use App\Enums\AsesmenStatus;
use App\Enums\KeputusanStatus;
use App\Enums\SertifikatStatus;
use App\Enums\PraPendaftaranStatus;
use App\Exceptions\StateTransitionException;
use App\Models\PendaftaranSertifikasi;
use App\Models\Asesmen;
use App\Models\KeputusanSertifikasi;
use App\Models\Sertifikat;
use App\Models\PraPendaftaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * State Machine Service
 * 
 * Handles all state transitions for certification process
 * Enforces business rules and data integrity
 * 
 * @package App\Services
 */
class StateTransitionService
{
    /**
     * Validate and transition pra-pendaftaran status
     * 
     * @throws StateTransitionException
     */
    public function transitionPraPendaftaran(
        PraPendaftaran $praPendaftaran,
        PraPendaftaranStatus $newStatus,
        ?string $reason = null
    ): PraPendaftaran {
        
        $currentStatusEnum = PraPendaftaranStatus::from($praPendaftaran->status);

        // GUARD 1: Check if transition is allowed
        if (!$currentStatusEnum->canTransitionTo($newStatus)) {
            throw StateTransitionException::invalidTransition(
                $currentStatusEnum->label(),
                $newStatus->label(),
                'Pra Pendaftaran'
            );
        }

        return DB::transaction(function () use ($praPendaftaran, $newStatus, $reason) {
            $oldStatus = $praPendaftaran->status;
            
            $praPendaftaran->update([
                'status' => $newStatus->value,
                'catatan_admin' => $reason,
            ]);

            // Audit log
            activity()
                ->performedOn($praPendaftaran)
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus->value,
                    'reason' => $reason,
                ])
                ->log('Status pra-pendaftaran diubah');

            return $praPendaftaran->fresh();
        });
    }

    /**
     * Validate and transition pendaftaran status
     * 
     * @throws StateTransitionException
     */
    public function transitionPendaftaran(
        PendaftaranSertifikasi $pendaftaran,
        PendaftaranStatus $newStatus,
        ?string $reason = null
    ): PendaftaranSertifikasi {
        
        $currentStatusEnum = PendaftaranStatus::from($pendaftaran->status);

        // GUARD 1: Check if locked
        $this->guardNotLocked($pendaftaran, 'mengubah status');

        // GUARD 2: Check if transition is allowed
        if (!$currentStatusEnum->canTransitionTo($newStatus)) {
            throw StateTransitionException::invalidTransition(
                $currentStatusEnum->label(),
                $newStatus->label(),
                'Pendaftaran Sertifikasi'
            );
        }

        // GUARD 3: Special validation per transition
        match($newStatus) {
            PendaftaranStatus::DIAJUKAN => $this->guardPendaftaranSubmit($pendaftaran),
            PendaftaranStatus::DIKUNCI => $this->guardPendaftaranLock($pendaftaran),
            default => null,
        };

        return DB::transaction(function () use ($pendaftaran, $newStatus, $reason) {
            $oldStatus = $pendaftaran->status;
            
            $pendaftaran->update([
                'status' => $newStatus->value,
                'status_updated_at' => now(),
                'catatan_admin' => $reason,
            ]);

            // If locking, mark as locked
            if ($newStatus === PendaftaranStatus::DIKUNCI) {
                $pendaftaran->update([
                    'is_locked' => true,
                    'locked_at' => now(),
                    'locked_reason' => 'Pendaftaran terkunci untuk proses asesmen',
                ]);
            }

            // Audit log
            activity()
                ->performedOn($pendaftaran)
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus->value,
                    'reason' => $reason,
                ])
                ->log('Status pendaftaran diubah');

            return $pendaftaran->fresh();
        });
    }

    /**
     * Guard: Pendaftaran can be submitted
     * 
     * @throws StateTransitionException
     */
    private function guardPendaftaranSubmit(PendaftaranSertifikasi $pendaftaran): void
    {
        if (!$pendaftaran->skema_sertifikasi_id) {
            throw StateTransitionException::missingRequirement(
                'Skema sertifikasi belum dipilih',
                'mengajukan pendaftaran'
            );
        }

        if (!$pendaftaran->nama_lengkap || !$pendaftaran->email) {
            throw StateTransitionException::missingRequirement(
                'Data diri belum lengkap (nama dan email wajib)',
                'mengajukan pendaftaran'
            );
        }

        if (!$pendaftaran->nik || !$pendaftaran->tempat_lahir || !$pendaftaran->tanggal_lahir) {
            throw StateTransitionException::missingRequirement(
                'Data identitas belum lengkap',
                'mengajukan pendaftaran'
            );
        }

        // Check required documents
        if ($pendaftaran->skemaSertifikasi?->requires_documents) {
            $requiredDocs = ['ktp', 'ijazah', 'pas_foto'];
            foreach ($requiredDocs as $doc) {
                if (!$pendaftaran->{$doc}) {
                    throw StateTransitionException::missingRequirement(
                        "Dokumen {$doc} belum diupload",
                        'mengajukan pendaftaran'
                    );
                }
            }
        }
    }

    /**
     * Guard: Pendaftaran can be locked
     * 
     * @throws StateTransitionException
     */
    private function guardPendaftaranLock(PendaftaranSertifikasi $pendaftaran): void
    {
        if ($pendaftaran->status !== PendaftaranStatus::DIVERIFIKASI->value) {
            throw StateTransitionException::missingRequirement(
                'Pendaftaran harus dalam status DIVERIFIKASI terlebih dahulu',
                'mengunci pendaftaran'
            );
        }

        // Check if jadwal asesmen exists
        if (!$pendaftaran->jadwalAsesmen()->exists()) {
            throw StateTransitionException::missingRequirement(
                'Jadwal asesmen belum dibuat',
                'mengunci pendaftaran'
            );
        }

        // Check if asesor assigned
        if (!$pendaftaran->jadwalAsesmen->asesor_id) {
            throw StateTransitionException::missingRequirement(
                'Asesor belum ditugaskan pada jadwal asesmen',
                'mengunci pendaftaran'
            );
        }
    }

    /**
     * Create and initialize asesmen from locked pendaftaran
     * 
     * @throws StateTransitionException
     */
    public function createAsesmen(PendaftaranSertifikasi $pendaftaran): Asesmen
    {
        // GUARD 1: Pendaftaran must be locked
        if ($pendaftaran->status !== PendaftaranStatus::DIKUNCI->value) {
            throw StateTransitionException::missingRequirement(
                "Pendaftaran belum terkunci. Status saat ini: {$pendaftaran->status_label}",
                'membuat asesmen'
            );
        }

        // GUARD 2: No existing asesmen
        if ($pendaftaran->asesmen()->exists()) {
            throw StateTransitionException::duplicateEntity(
                'Asesmen',
                "pendaftaran_id: {$pendaftaran->id}"
            );
        }

        // GUARD 3: Must have jadwal
        if (!$pendaftaran->jadwalAsesmen) {
            throw StateTransitionException::missingRequirement(
                'Jadwal asesmen tidak ditemukan',
                'membuat asesmen'
            );
        }

        return DB::transaction(function () use ($pendaftaran) {
            $asesmen = Asesmen::create([
                'pendaftaran_id' => $pendaftaran->id,
                'status' => AsesmenStatus::BELUM_DIMULAI->value,
                'tanggal_asesmen' => $pendaftaran->jadwalAsesmen->tanggal,
                'asesor_id' => $pendaftaran->jadwalAsesmen->asesor_id,
                'is_locked' => false,
            ]);

            // Create checklist items from skema
            $this->generateAsesmenChecklist($asesmen, $pendaftaran->skemaSertifikasi);

            activity()
                ->performedOn($asesmen)
                ->log('Asesmen dibuat dari pendaftaran terkunci');

            return $asesmen;
        });
    }

    /**
     * Generate checklist items for asesmen
     */
    private function generateAsesmenChecklist(Asesmen $asesmen, $skemaSertifikasi): void
    {
        // Get all units and KUK from skema
        $units = $skemaSertifikasi->units ?? [];
        
        foreach ($units as $unit) {
            foreach ($unit->kriteria_unjuk_kerja as $kuk) {
                $asesmen->checklistItems()->create([
                    'unit_kompetensi_id' => $unit->id,
                    'kuk_kode' => $kuk->kode,
                    'kuk_deskripsi' => $kuk->deskripsi,
                    'is_completed' => false,
                    'nilai' => null,
                ]);
            }
        }
    }

    /**
     * Transition asesmen status
     * 
     * @throws StateTransitionException
     */
    public function transitionAsesmen(
        Asesmen $asesmen,
        AsesmenStatus $newStatus
    ): Asesmen {
        
        $currentStatusEnum = AsesmenStatus::from($asesmen->status);

        // GUARD 1: Check if locked
        $this->guardNotLocked($asesmen, 'mengubah status asesmen');

        // GUARD 2: Check if transition is allowed
        if (!$currentStatusEnum->canTransitionTo($newStatus)) {
            throw StateTransitionException::invalidTransition(
                $currentStatusEnum->label(),
                $newStatus->label(),
                'Asesmen'
            );
        }

        // GUARD 3: Special validation per transition
        match($newStatus) {
            AsesmenStatus::DALAM_PROSES => $this->guardAsesmenStart($asesmen),
            AsesmenStatus::SELESAI => $this->guardAsesmenComplete($asesmen),
            default => null,
        };

        return DB::transaction(function () use ($asesmen, $newStatus) {
            $asesmen->update([
                'status' => $newStatus->value,
                'updated_at' => now(),
            ]);

            if ($newStatus === AsesmenStatus::DALAM_PROSES) {
                $asesmen->update(['started_at' => now()]);
            }

            if ($newStatus === AsesmenStatus::SELESAI) {
                $asesmen->update([
                    'completed_at' => now(),
                    'is_locked' => true,
                    'locked_at' => now(),
                ]);
            }

            activity()
                ->performedOn($asesmen)
                ->log("Status asesmen diubah ke {$newStatus->label()}");

            return $asesmen->fresh();
        });
    }

    /**
     * Guard: Asesmen can be started
     * 
     * @throws StateTransitionException
     */
    private function guardAsesmenStart(Asesmen $asesmen): void
    {
        // Check if tanggal asesmen has arrived
        if ($asesmen->tanggal_asesmen && now()->lt($asesmen->tanggal_asesmen)) {
            throw StateTransitionException::missingRequirement(
                "Asesmen belum dapat dimulai. Jadwal: {$asesmen->tanggal_asesmen->format('d/m/Y H:i')}",
                'memulai asesmen'
            );
        }

        // Check if asesor is assigned
        if (!$asesmen->asesor_id) {
            throw StateTransitionException::missingRequirement(
                'Asesor belum ditugaskan',
                'memulai asesmen'
            );
        }
    }

    /**
     * Guard: Asesmen can be completed
     * 
     * @throws StateTransitionException
     */
    private function guardAsesmenComplete(Asesmen $asesmen): void
    {
        // Check all checklist completed
        $totalChecklist = $asesmen->checklistItems()->count();
        $completedChecklist = $asesmen->checklistItems()
            ->where('is_completed', true)
            ->whereNotNull('nilai')
            ->count();

        if ($completedChecklist < $totalChecklist) {
            throw StateTransitionException::missingRequirement(
                "Masih ada " . ($totalChecklist - $completedChecklist) . " item checklist yang belum dinilai",
                'menyelesaikan asesmen'
            );
        }

        // Check evidence uploaded (at least 1 per kompetensi)
        $totalKompetensi = $asesmen->pendaftaran->skemaSertifikasi->units->count();
        $uploadedKompetensi = $asesmen->uploads()
            ->distinct('unit_kompetensi_id')
            ->count('unit_kompetensi_id');

        if ($uploadedKompetensi < $totalKompetensi) {
            throw StateTransitionException::missingRequirement(
                "Belum semua kompetensi memiliki evidence. " .
                "Uploaded: {$uploadedKompetensi}/{$totalKompetensi}",
                'menyelesaikan asesmen'
            );
        }
    }

    /**
     * Create keputusan from completed asesmen
     * 
     * @throws StateTransitionException
     */
    public function createKeputusan(Asesmen $asesmen): KeputusanSertifikasi
    {
        // GUARD 1: Asesmen must be completed
        if ($asesmen->status !== AsesmenStatus::SELESAI->value) {
            throw StateTransitionException::missingRequirement(
                "Asesmen belum selesai. Status saat ini: {$asesmen->status_label}",
                'membuat keputusan'
            );
        }

        // GUARD 2: No existing keputusan
        if ($asesmen->keputusan()->exists()) {
            throw StateTransitionException::duplicateEntity(
                'Keputusan Sertifikasi',
                "asesmen_id: {$asesmen->id}"
            );
        }

        return DB::transaction(function () use ($asesmen) {
            $keputusan = KeputusanSertifikasi::create([
                'asesmen_id' => $asesmen->id,
                'pendaftaran_id' => $asesmen->pendaftaran_id,
                'status' => KeputusanStatus::BELUM_DITETAPKAN->value,
                'is_locked' => false,
            ]);

            activity()
                ->performedOn($keputusan)
                ->log('Keputusan dibuat dari asesmen selesai');

            return $keputusan;
        });
    }

    /**
     * Decide keputusan (KOMPETEN / BELUM_KOMPETEN)
     * 
     * @throws StateTransitionException
     */
    public function decideKeputusan(
        KeputusanSertifikasi $keputusan,
        KeputusanStatus $newStatus,
        string $catatan
    ): KeputusanSertifikasi {
        
        // GUARD 1: Check if locked
        $this->guardNotLocked($keputusan, 'menetapkan keputusan');

        // GUARD 2: Must be BELUM_DITETAPKAN
        if ($keputusan->status !== KeputusanStatus::BELUM_DITETAPKAN->value) {
            throw new StateTransitionException(
                'Tidak dapat menetapkan keputusan: Keputusan sudah ditetapkan sebelumnya'
            );
        }

        // GUARD 3: Must have catatan
        if (empty(trim($catatan))) {
            throw StateTransitionException::missingRequirement(
                'Catatan keputusan wajib diisi',
                'menetapkan keputusan'
            );
        }

        // GUARD 4: Must be valid decision status
        if (!in_array($newStatus, [KeputusanStatus::KOMPETEN, KeputusanStatus::BELUM_KOMPETEN])) {
            throw new StateTransitionException(
                'Status keputusan harus KOMPETEN atau BELUM_KOMPETEN'
            );
        }

        return DB::transaction(function () use ($keputusan, $newStatus, $catatan) {
            $keputusan->update([
                'status' => $newStatus->value,
                'catatan' => $catatan,
                'decided_at' => now(),
                'decided_by' => auth()->id(),
                'is_locked' => true,
                'locked_at' => now(),
            ]);

            // If KOMPETEN, auto-create sertifikat
            if ($newStatus === KeputusanStatus::KOMPETEN) {
                $sertifikat = $this->autoIssueSertifikat($keputusan);
                
                activity()
                    ->performedOn($keputusan)
                    ->withProperties([
                        'status' => $newStatus->value,
                        'sertifikat_id' => $sertifikat->id,
                        'nomor_sertifikat' => $sertifikat->nomor_sertifikat,
                    ])
                    ->log('Keputusan ditetapkan KOMPETEN - Sertifikat otomatis diterbitkan');
            } else {
                activity()
                    ->performedOn($keputusan)
                    ->withProperties(['status' => $newStatus->value])
                    ->log('Keputusan ditetapkan BELUM_KOMPETEN');
            }

            return $keputusan->fresh();
        });
    }

    /**
     * Auto-issue sertifikat when keputusan = KOMPETEN
     * 
     * @throws StateTransitionException
     */
    private function autoIssueSertifikat(KeputusanSertifikasi $keputusan): Sertifikat
    {
        // GUARD: Already has sertifikat
        if ($keputusan->sertifikat()->exists()) {
            return $keputusan->sertifikat;
        }

        return DB::transaction(function () use ($keputusan) {
            $pendaftaran = $keputusan->pendaftaran;
            
            // Generate nomor sertifikat
            $nomorSertifikat = $this->generateNomorSertifikat();

            $sertifikat = Sertifikat::create([
                'keputusan_id' => $keputusan->id,
                'pendaftaran_id' => $pendaftaran->id,
                'user_id' => $pendaftaran->user_id,
                'nomor_sertifikat' => $nomorSertifikat,
                'nama_peserta' => $pendaftaran->nama_lengkap,
                'nik' => $pendaftaran->nik,
                'skema_sertifikasi' => $pendaftaran->skemaSertifikasi->nama_skema,
                'skema_sertifikasi_id' => $pendaftaran->skema_sertifikasi_id,
                'tanggal_terbit' => now(),
                'tanggal_berlaku_sampai' => now()->addYears(3),
                'status' => SertifikatStatus::TERBIT->value,
            ]);

            // LOCK ALL PREVIOUS DATA
            $this->lockDataAfterIssuance($sertifikat);

            activity()
                ->performedOn($sertifikat)
                ->withProperties([
                    'nomor' => $nomorSertifikat,
                    'auto_issued' => true,
                ])
                ->log('Sertifikat otomatis diterbitkan dari keputusan KOMPETEN');

            return $sertifikat;
        });
    }

    /**
     * Lock all data after sertifikat issued
     * CRITICAL: Prevent any further modification
     */
    private function lockDataAfterIssuance(Sertifikat $sertifikat): void
    {
        // Lock pendaftaran
        $sertifikat->pendaftaran->update([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_reason' => 'Sertifikat telah diterbitkan (Nomor: ' . $sertifikat->nomor_sertifikat . ')',
        ]);

        // Lock asesmen (already locked when completed, but ensure)
        $sertifikat->keputusan->asesmen->update([
            'is_locked' => true,
            'locked_at' => now(),
        ]);

        // Lock keputusan (already locked when decided, but ensure)
        $sertifikat->keputusan->update([
            'is_locked' => true,
            'locked_at' => now(),
        ]);

        Log::info("🔒 Data locked after sertifikat issuance", [
            'sertifikat_id' => $sertifikat->id,
            'nomor' => $sertifikat->nomor_sertifikat,
            'pendaftaran_id' => $sertifikat->pendaftaran_id,
            'asesmen_id' => $sertifikat->keputusan->asesmen_id,
            'keputusan_id' => $sertifikat->keputusan_id,
        ]);
    }

    /**
     * Generate unique nomor sertifikat
     */
    private function generateNomorSertifikat(): string
    {
        $year = now()->format('Y');
        $prefix = "CERT/LSP/{$year}/";
        
        // Use pessimistic lock to prevent race condition
        $lastSertifikat = Sertifikat::where('nomor_sertifikat', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->lockForUpdate()
            ->first();

        if ($lastSertifikat) {
            // Extract number from end of string (format: CERT/LSP/2026/0001)
            preg_match('/(\d+)$/', $lastSertifikat->nomor_sertifikat, $matches);
            $lastNumber = isset($matches[1]) ? (int)$matches[1] : 0;
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Check if entity is locked
     */
    public function isLocked($entity): bool
    {
        return $entity->is_locked ?? false;
    }

    /**
     * Guard: Prevent modification if locked
     * 
     * @throws StateTransitionException
     */
    public function guardNotLocked($entity, string $action = 'memodifikasi data'): void
    {
        if ($this->isLocked($entity)) {
            $lockedReason = $entity->locked_reason ?? 'Data telah terkunci';
            throw StateTransitionException::entityLocked(
                class_basename($entity),
                $lockedReason
            );
        }
    }

    /**
     * Revoke sertifikat (Admin only)
     * 
     * @throws StateTransitionException
     */
    public function revokeSertifikat(
        Sertifikat $sertifikat,
        string $reason,
        bool $unlockData = false
    ): Sertifikat {
        
        // GUARD: Must be TERBIT
        if ($sertifikat->status !== SertifikatStatus::TERBIT->value) {
            throw new StateTransitionException(
                'Sertifikat hanya dapat dicabut jika statusnya TERBIT'
            );
        }

        // GUARD: Must have reason
        if (empty(trim($reason))) {
            throw StateTransitionException::missingRequirement(
                'Alasan pencabutan wajib diisi',
                'mencabut sertifikat'
            );
        }

        return DB::transaction(function () use ($sertifikat, $reason, $unlockData) {
            $sertifikat->update([
                'status' => SertifikatStatus::DICABUT->value,
                'revoked_at' => now(),
                'revoked_by' => auth()->id(),
                'revoked_reason' => $reason,
            ]);

            // Optionally unlock data for correction
            if ($unlockData) {
                $this->unlockDataAfterRevocation($sertifikat);
            }

            activity()
                ->performedOn($sertifikat)
                ->withProperties([
                    'reason' => $reason,
                    'unlocked' => $unlockData,
                ])
                ->log('Sertifikat dicabut oleh admin');

            return $sertifikat->fresh();
        });
    }

    /**
     * Unlock data after revocation (for correction)
     */
    private function unlockDataAfterRevocation(Sertifikat $sertifikat): void
    {
        $sertifikat->pendaftaran->update(['is_locked' => false, 'locked_at' => null]);
        $sertifikat->keputusan->asesmen->update(['is_locked' => false, 'locked_at' => null]);
        $sertifikat->keputusan->update(['is_locked' => false, 'locked_at' => null]);

        Log::warning("🔓 Data unlocked after sertifikat revocation for correction", [
            'sertifikat_id' => $sertifikat->id,
            'nomor' => $sertifikat->nomor_sertifikat,
            'revoked_by' => auth()->id(),
        ]);
    }
}

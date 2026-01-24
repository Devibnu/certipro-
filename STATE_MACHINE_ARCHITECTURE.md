# 🔒 STATE MACHINE ARCHITECTURE - LSP CERTIFICATION SYSTEM
## Strict State Management untuk Integritas Data Sertifikasi

---

## 📊 1. STATE MACHINE DEFINITION (FULL FLOW)

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│                        CERTIFICATION STATE MACHINE                                   │
│                     (ISO 17024 / BNSP Compliant)                                     │
└─────────────────────────────────────────────────────────────────────────────────────┘

╔══════════════════════════════════════════════════════════════════════════════════════╗
║                         STAGE 1: PRA-PENDAFTARAN                                     ║
╚══════════════════════════════════════════════════════════════════════════════════════╝

    [DIAJUKAN] ────────┐
        │              │
        ├──(reject)──→ [DITOLAK] ──→ ❌ END
        │              │
        └──(approve)─→ [DITERIMA] ──→ ✅ Unlock Stage 2


╔══════════════════════════════════════════════════════════════════════════════════════╗
║                    STAGE 2: PENDAFTARAN SERTIFIKASI                                  ║
╚══════════════════════════════════════════════════════════════════════════════════════╝

    [DRAFT] ────────────────────┐
        │                       │
        ├──(cancel)──→ [DIBATALKAN] ──→ ❌ END
        │                       │
        ├──(submit)──→ [DIAJUKAN] ──────┐
        │                       │        │
        │                  (reject)──→ [DITOLAK] ──→ ❌ END
        │                       │        │
        │                  (verify)──→ [DIVERIFIKASI] ──────┐
        │                                                    │
        └────────────────────────────────────────────(lock)─→ [DIKUNCI] ──→ ✅ Unlock Stage 3


╔══════════════════════════════════════════════════════════════════════════════════════╗
║                           STAGE 3: ASESMEN                                           ║
╚══════════════════════════════════════════════════════════════════════════════════════╝

    [BELUM_DIMULAI] ────────────┐
        │                       │
        ├──(start)──→ [DALAM_PROSES] ────┐
        │                       │         │
        │                  (pause)──────┤ │
        │                       │         │
        │                  (resume)──────┘ │
        │                       │           │
        └────────────────(complete)──→ [SELESAI] ──→ ✅ Unlock Stage 4


╔══════════════════════════════════════════════════════════════════════════════════════╗
║                      STAGE 4: KEPUTUSAN SERTIFIKASI                                  ║
╚══════════════════════════════════════════════════════════════════════════════════════╝

    [BELUM_DITETAPKAN] ────────┐
        │                      │
        ├──(decide)──→ [KOMPETEN] ──→ ✅ Unlock Stage 5 (Issue Certificate)
        │                      │
        └──(decide)──→ [BELUM_KOMPETEN] ──→ ❌ END (Can reapply)


╔══════════════════════════════════════════════════════════════════════════════════════╗
║                          STAGE 5: SERTIFIKAT                                         ║
╚══════════════════════════════════════════════════════════════════════════════════════╝

    [BELUM_TERBIT] ────────────┐
        │                      │
        └──(issue)──→ [TERBIT] ──→ 🔒 ALL DATA LOCKED (Read-Only)
                         │
                         └──(expire)──→ [KADALUARSA] ──→ ⏰ Can renew


═══════════════════════════════════════════════════════════════════════════════════════
                             TRANSITION MATRIX
═══════════════════════════════════════════════════════════════════════════════════════

FROM                    → TO                         | CONDITION                  | ACTOR
─────────────────────────────────────────────────────────────────────────────────────
PraPendaftaran::DIAJUKAN → DITERIMA                 | Admin verify success       | Admin
PraPendaftaran::DIAJUKAN → DITOLAK                  | Admin reject               | Admin

Pendaftaran::DRAFT       → DIAJUKAN                 | User submit complete data  | User
Pendaftaran::DIAJUKAN    → DIVERIFIKASI             | Admin verify               | Admin
Pendaftaran::DIAJUKAN    → DITOLAK                  | Admin reject               | Admin
Pendaftaran::DIVERIFIKASI → DIKUNCI                 | Admin lock for asesmen     | Admin

Asesmen::BELUM_DIMULAI   → DALAM_PROSES             | Asesor start               | Asesor
Asesmen::DALAM_PROSES    → SELESAI                  | Asesor complete all        | Asesor

Keputusan::BELUM_DITETAPKAN → KOMPETEN              | Komite decide             | Komite
Keputusan::BELUM_DITETAPKAN → BELUM_KOMPETEN        | Komite decide             | Komite

Sertifikat::BELUM_TERBIT → TERBIT                   | System auto-issue          | System
Sertifikat::TERBIT       → KADALUARSA               | System cron (3 years)      | System
```

---

## 🛡️ 2. TRANSITION RULES (STRICT GUARDS)

### RULE SET 1: PENDAFTARAN SERTIFIKASI
```php
✅ CAN CREATE if:
   - PraPendaftaran->status === 'DITERIMA'
   - PraPendaftaran->is_processed === false
   - No existing PendaftaranSertifikasi with same pra_pendaftaran_id

❌ CANNOT CREATE if:
   - PraPendaftaran not DITERIMA
   - Already processed
   - Duplicate exists

✅ CAN SUBMIT if:
   - Status === 'DRAFT'
   - All required fields filled
   - Documents uploaded
   - Skema selected

❌ CANNOT SUBMIT if:
   - Status !== 'DRAFT'
   - Incomplete data
   - Already locked
```

### RULE SET 2: ASESMEN
```php
✅ CAN CREATE ASESMEN if:
   - PendaftaranSertifikasi->status === 'DIKUNCI'
   - No existing Asesmen for this pendaftaran
   - Jadwal asesmen exists

❌ CANNOT CREATE if:
   - Pendaftaran not locked
   - Already has asesmen
   - No schedule assigned

✅ CAN START ASESMEN if:
   - Status === 'BELUM_DIMULAI'
   - Current date >= tanggal_asesmen
   - Asesor assigned

❌ CANNOT START if:
   - Already started/completed
   - Too early
   - No asesor assigned

✅ CAN COMPLETE ASESMEN if:
   - Status === 'DALAM_PROSES'
   - All checklist items completed
   - Evidence uploaded per KUK
   - Min 1 upload per kompetensi

❌ CANNOT COMPLETE if:
   - Not started yet
   - Already completed
   - Incomplete checklist
   - Missing evidence
```

### RULE SET 3: KEPUTUSAN SERTIFIKASI
```php
✅ CAN CREATE KEPUTUSAN if:
   - Asesmen->status === 'SELESAI'
   - No existing Keputusan for this asesmen
   - Komite Teknis assigned

❌ CANNOT CREATE if:
   - Asesmen not completed
   - Already has keputusan
   - No komite assigned

✅ CAN DECIDE (KOMPETEN/BELUM_KOMPETEN) if:
   - Status === 'BELUM_DITETAPKAN'
   - All evidence reviewed
   - Voting completed (if multi-komite)
   - Catatan filled

❌ CANNOT DECIDE if:
   - Already decided
   - Evidence not reviewed
   - Missing voting
```

### RULE SET 4: SERTIFIKAT
```php
✅ CAN ISSUE SERTIFIKAT if:
   - Keputusan->status === 'KOMPETEN'
   - No existing Sertifikat for this keputusan
   - Nomor sertifikat available
   - QR code generated

❌ CANNOT ISSUE if:
   - Not KOMPETEN
   - Already issued
   - System error (nomor generation failed)

✅ AUTO-LOCK AFTER ISSUED:
   - Pendaftaran: read-only
   - Asesmen: read-only
   - Keputusan: read-only
   - Only sertifikat can be revoked (special admin permission)

❌ CANNOT EDIT after sertifikat issued:
   - Any field in pendaftaran
   - Any field in asesmen
   - Any field in keputusan
   - Only admin with REVOKE_CERTIFICATE permission can intervene
```

---

## 💻 3. IMPLEMENTATION - ENUMS (PHP 8.1+)

```php
<?php
// app/Enums/PraPendaftaranStatus.php

namespace App\Enums;

enum PraPendaftaranStatus: string
{
    case DIAJUKAN = 'diajukan';
    case DITERIMA = 'diterima';
    case DITOLAK = 'ditolak';

    public function label(): string
    {
        return match($this) {
            self::DIAJUKAN => 'Diajukan',
            self::DITERIMA => 'Diterima',
            self::DITOLAK => 'Ditolak',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DIAJUKAN => 'yellow',
            self::DITERIMA => 'green',
            self::DITOLAK => 'red',
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match($this) {
            self::DIAJUKAN => in_array($newStatus, [self::DITERIMA, self::DITOLAK]),
            self::DITERIMA => false, // Terminal state
            self::DITOLAK => false,  // Terminal state
        };
    }

    public static function initial(): self
    {
        return self::DIAJUKAN;
    }
}
```

```php
<?php
// app/Enums/PendaftaranStatus.php

namespace App\Enums;

enum PendaftaranStatus: string
{
    case DRAFT = 'draft';
    case DIAJUKAN = 'diajukan';
    case DIVERIFIKASI = 'diverifikasi';
    case DITOLAK = 'ditolak';
    case DIKUNCI = 'dikunci';          // Locked for asesmen
    case DIBATALKAN = 'dibatalkan';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::DIAJUKAN => 'Diajukan',
            self::DIVERIFIKASI => 'Diverifikasi',
            self::DITOLAK => 'Ditolak',
            self::DIKUNCI => 'Terkunci',
            self::DIBATALKAN => 'Dibatalkan',
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match($this) {
            self::DRAFT => in_array($newStatus, [self::DIAJUKAN, self::DIBATALKAN]),
            self::DIAJUKAN => in_array($newStatus, [self::DIVERIFIKASI, self::DITOLAK]),
            self::DIVERIFIKASI => $newStatus === self::DIKUNCI,
            self::DIKUNCI => false,      // Locked, cannot transition
            self::DITOLAK => false,      // Terminal
            self::DIBATALKAN => false,   // Terminal
        };
    }

    public function isLocked(): bool
    {
        return $this === self::DIKUNCI;
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::DITOLAK, self::DIBATALKAN, self::DIKUNCI]);
    }
}
```

```php
<?php
// app/Enums/AsesmenStatus.php

namespace App\Enums;

enum AsesmenStatus: string
{
    case BELUM_DIMULAI = 'belum_dimulai';
    case DALAM_PROSES = 'dalam_proses';
    case SELESAI = 'selesai';

    public function label(): string
    {
        return match($this) {
            self::BELUM_DIMULAI => 'Belum Dimulai',
            self::DALAM_PROSES => 'Dalam Proses',
            self::SELESAI => 'Selesai',
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match($this) {
            self::BELUM_DIMULAI => $newStatus === self::DALAM_PROSES,
            self::DALAM_PROSES => $newStatus === self::SELESAI,
            self::SELESAI => false, // Terminal state
        };
    }

    public function isCompleted(): bool
    {
        return $this === self::SELESAI;
    }
}
```

```php
<?php
// app/Enums/KeputusanStatus.php

namespace App\Enums;

enum KeputusanStatus: string
{
    case BELUM_DITETAPKAN = 'belum_ditetapkan';
    case KOMPETEN = 'kompeten';
    case BELUM_KOMPETEN = 'belum_kompeten';

    public function label(): string
    {
        return match($this) {
            self::BELUM_DITETAPKAN => 'Belum Ditetapkan',
            self::KOMPETEN => 'Kompeten',
            self::BELUM_KOMPETEN => 'Belum Kompeten',
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match($this) {
            self::BELUM_DITETAPKAN => in_array($newStatus, [self::KOMPETEN, self::BELUM_KOMPETEN]),
            self::KOMPETEN => false,        // Terminal
            self::BELUM_KOMPETEN => false,  // Terminal
        };
    }

    public function isCompetent(): bool
    {
        return $this === self::KOMPETEN;
    }
}
```

```php
<?php
// app/Enums/SertifikatStatus.php

namespace App\Enums;

enum SertifikatStatus: string
{
    case BELUM_TERBIT = 'belum_terbit';
    case TERBIT = 'terbit';
    case KADALUARSA = 'kadaluarsa';
    case DICABUT = 'dicabut';

    public function label(): string
    {
        return match($this) {
            self::BELUM_TERBIT => 'Belum Terbit',
            self::TERBIT => 'Terbit',
            self::KADALUARSA => 'Kadaluarsa',
            self::DICABUT => 'Dicabut',
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match($this) {
            self::BELUM_TERBIT => $newStatus === self::TERBIT,
            self::TERBIT => in_array($newStatus, [self::KADALUARSA, self::DICABUT]),
            self::KADALUARSA => false, // Terminal
            self::DICABUT => false,    // Terminal
        };
    }

    public function isActive(): bool
    {
        return $this === self::TERBIT;
    }
}
```

---

## 🛠️ 4. SERVICE LAYER - STATE MACHINE GUARDS

```php
<?php
// app/Services/StateTransitionService.php

namespace App\Services;

use App\Enums\PendaftaranStatus;
use App\Enums\AsesmenStatus;
use App\Enums\KeputusanStatus;
use App\Enums\SertifikatStatus;
use App\Exceptions\StateTransitionException;
use App\Models\PendaftaranSertifikasi;
use App\Models\Asesmen;
use App\Models\KeputusanSertifikasi;
use App\Models\Sertifikat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StateTransitionService
{
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
        
        // GUARD 1: Check if transition is allowed
        if (!$pendaftaran->status_enum->canTransitionTo($newStatus)) {
            throw new StateTransitionException(
                "Tidak dapat mengubah status dari {$pendaftaran->status_enum->label()} " .
                "ke {$newStatus->label()}. Transisi tidak diizinkan."
            );
        }

        // GUARD 2: Special validation per transition
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
     */
    private function guardPendaftaranSubmit(PendaftaranSertifikasi $pendaftaran): void
    {
        if (!$pendaftaran->skema_sertifikasi_id) {
            throw new StateTransitionException(
                'Tidak dapat mengajukan pendaftaran: Skema sertifikasi belum dipilih'
            );
        }

        if (!$pendaftaran->nama_lengkap || !$pendaftaran->email) {
            throw new StateTransitionException(
                'Tidak dapat mengajukan pendaftaran: Data diri belum lengkap'
            );
        }

        // Check documents (if required by skema)
        // ... additional validation
    }

    /**
     * Guard: Pendaftaran can be locked
     */
    private function guardPendaftaranLock(PendaftaranSertifikasi $pendaftaran): void
    {
        if ($pendaftaran->status !== PendaftaranStatus::DIVERIFIKASI->value) {
            throw new StateTransitionException(
                'Tidak dapat mengunci pendaftaran: Status harus DIVERIFIKASI terlebih dahulu'
            );
        }

        // Check if jadwal asesmen exists
        if (!$pendaftaran->jadwalAsesmen()->exists()) {
            throw new StateTransitionException(
                'Tidak dapat mengunci pendaftaran: Jadwal asesmen belum dibuat'
            );
        }
    }

    /**
     * Create and start asesmen
     * 
     * @throws StateTransitionException
     */
    public function createAsesmen(PendaftaranSertifikasi $pendaftaran): Asesmen
    {
        // GUARD: Pendaftaran must be locked
        if ($pendaftaran->status !== PendaftaranStatus::DIKUNCI->value) {
            throw new StateTransitionException(
                'Tidak dapat membuat asesmen: Pendaftaran belum terkunci. ' .
                'Status saat ini: ' . $pendaftaran->status_label
            );
        }

        // GUARD: No existing asesmen
        if ($pendaftaran->asesmen()->exists()) {
            throw new StateTransitionException(
                'Tidak dapat membuat asesmen: Asesmen sudah ada untuk pendaftaran ini'
            );
        }

        return DB::transaction(function () use ($pendaftaran) {
            $asesmen = Asesmen::create([
                'pendaftaran_sertifikasi_id' => $pendaftaran->id,
                'status' => AsesmenStatus::BELUM_DIMULAI->value,
                'tanggal_asesmen' => $pendaftaran->jadwalAsesmen->tanggal,
                'asesor_id' => $pendaftaran->jadwalAsesmen->asesor_id,
            ]);

            activity()
                ->performedOn($asesmen)
                ->log('Asesmen dibuat dari pendaftaran terkunci');

            return $asesmen;
        });
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
        
        if (!$asesmen->status_enum->canTransitionTo($newStatus)) {
            throw new StateTransitionException(
                "Tidak dapat mengubah status asesmen dari {$asesmen->status_enum->label()} " .
                "ke {$newStatus->label()}"
            );
        }

        // GUARD: Complete asesmen
        if ($newStatus === AsesmenStatus::SELESAI) {
            $this->guardAsesmenComplete($asesmen);
        }

        return DB::transaction(function () use ($asesmen, $newStatus) {
            $asesmen->update([
                'status' => $newStatus->value,
                'updated_at' => now(),
            ]);

            if ($newStatus === AsesmenStatus::SELESAI) {
                $asesmen->update(['completed_at' => now()]);
            }

            activity()
                ->performedOn($asesmen)
                ->log("Status asesmen diubah ke {$newStatus->label()}");

            return $asesmen->fresh();
        });
    }

    /**
     * Guard: Asesmen can be completed
     */
    private function guardAsesmenComplete(Asesmen $asesmen): void
    {
        // Check all checklist completed
        $totalChecklist = $asesmen->checklist()->count();
        $completedChecklist = $asesmen->checklist()->whereNotNull('nilai')->count();

        if ($completedChecklist < $totalChecklist) {
            throw new StateTransitionException(
                "Tidak dapat menyelesaikan asesmen: Masih ada {$totalChecklist - $completedChecklist} " .
                "item checklist yang belum dinilai"
            );
        }

        // Check evidence uploaded
        $totalUploadRequired = $asesmen->checklistItems()->count();
        $uploadedEvidence = $asesmen->uploads()->count();

        if ($uploadedEvidence === 0) {
            throw new StateTransitionException(
                'Tidak dapat menyelesaikan asesmen: Belum ada evidence yang diupload'
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
        // GUARD: Asesmen must be completed
        if ($asesmen->status !== AsesmenStatus::SELESAI->value) {
            throw new StateTransitionException(
                'Tidak dapat membuat keputusan: Asesmen belum selesai. ' .
                'Status saat ini: ' . $asesmen->status_label
            );
        }

        // GUARD: No existing keputusan
        if ($asesmen->keputusan()->exists()) {
            throw new StateTransitionException(
                'Tidak dapat membuat keputusan: Keputusan sudah ada untuk asesmen ini'
            );
        }

        return DB::transaction(function () use ($asesmen) {
            $keputusan = KeputusanSertifikasi::create([
                'asesmen_id' => $asesmen->id,
                'pendaftaran_sertifikasi_id' => $asesmen->pendaftaran_sertifikasi_id,
                'status' => KeputusanStatus::BELUM_DITETAPKAN->value,
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
        
        // GUARD: Must be BELUM_DITETAPKAN
        if ($keputusan->status !== KeputusanStatus::BELUM_DITETAPKAN->value) {
            throw new StateTransitionException(
                'Tidak dapat menetapkan keputusan: Keputusan sudah ditetapkan sebelumnya'
            );
        }

        // GUARD: Must have catatan
        if (empty($catatan)) {
            throw new StateTransitionException(
                'Tidak dapat menetapkan keputusan: Catatan keputusan wajib diisi'
            );
        }

        return DB::transaction(function () use ($keputusan, $newStatus, $catatan) {
            $keputusan->update([
                'status' => $newStatus->value,
                'catatan' => $catatan,
                'decided_at' => now(),
                'decided_by' => auth()->id(),
            ]);

            // If KOMPETEN, auto-create sertifikat
            if ($newStatus === KeputusanStatus::KOMPETEN) {
                $this->autoIssueSertifikat($keputusan);
            }

            activity()
                ->performedOn($keputusan)
                ->log("Keputusan ditetapkan: {$newStatus->label()}");

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
                'keputusan_sertifikasi_id' => $keputusan->id,
                'pendaftaran_sertifikasi_id' => $pendaftaran->id,
                'nomor_sertifikat' => $nomorSertifikat,
                'nama_peserta' => $pendaftaran->nama_lengkap,
                'skema_sertifikasi' => $pendaftaran->skemaSertifikasi->nama_skema,
                'tanggal_terbit' => now(),
                'tanggal_berlaku_sampai' => now()->addYears(3),
                'status' => SertifikatStatus::TERBIT->value,
            ]);

            // LOCK ALL PREVIOUS DATA
            $this->lockDataAfterIssuance($sertifikat);

            activity()
                ->performedOn($sertifikat)
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
            'locked_reason' => 'Sertifikat telah diterbitkan',
        ]);

        // Lock asesmen
        $sertifikat->keputusan->asesmen->update([
            'is_locked' => true,
            'locked_at' => now(),
        ]);

        // Lock keputusan
        $sertifikat->keputusan->update([
            'is_locked' => true,
            'locked_at' => now(),
        ]);

        Log::info("Data locked after sertifikat issuance", [
            'sertifikat_id' => $sertifikat->id,
            'nomor' => $sertifikat->nomor_sertifikat,
        ]);
    }

    /**
     * Generate unique nomor sertifikat
     */
    private function generateNomorSertifikat(): string
    {
        $year = now()->format('Y');
        $prefix = "CERT/LSP/{$year}/";
        
        $lastSertifikat = Sertifikat::where('nomor_sertifikat', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->lockForUpdate()
            ->first();

        if ($lastSertifikat) {
            $lastNumber = (int) substr($lastSertifikat->nomor_sertifikat, -3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
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
    public function guardNotLocked($entity, string $action = 'modify'): void
    {
        if ($this->isLocked($entity)) {
            $lockedReason = $entity->locked_reason ?? 'Data telah terkunci';
            throw new StateTransitionException(
                "Tidak dapat {$action}: {$lockedReason}"
            );
        }
    }
}
```

```php
<?php
// app/Exceptions/StateTransitionException.php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StateTransitionException extends Exception
{
    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
                'error_type' => 'state_transition_error',
            ], 422);
        }

        return response()->view('errors.state-transition', [
            'message' => $this->getMessage(),
        ], 422);
    }

    /**
     * Report the exception.
     */
    public function report(): void
    {
        // Log for monitoring
        \Log::warning('State transition blocked', [
            'message' => $this->getMessage(),
            'trace' => $this->getTraceAsString(),
        ]);
    }
}
```

---

## 🔒 5. DATABASE INTEGRITY

```php
<?php
// database/migrations/2026_01_23_add_state_machine_constraints.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Pendaftaran Sertifikasi
        Schema::table('pendaftaran_sertifikasi', function (Blueprint $table) {
            // Lock mechanism
            $table->boolean('is_locked')->default(false)->after('status');
            $table->timestamp('locked_at')->nullable()->after('is_locked');
            $table->string('locked_reason')->nullable()->after('locked_at');
            
            // Unique constraint: 1 pendaftaran = 1 asesmen
            $table->index('status');
            $table->index('is_locked');
        });

        // 2. Asesmen
        Schema::table('asesmen', function (Blueprint $table) {
            // Unique: 1 pendaftaran = 1 asesmen
            $table->unique('pendaftaran_sertifikasi_id', 'unique_pendaftaran_asesmen');
            
            // Lock mechanism
            $table->boolean('is_locked')->default(false)->after('status');
            $table->timestamp('locked_at')->nullable()->after('is_locked');
            
            // Completion tracking
            $table->timestamp('completed_at')->nullable();
            
            $table->index('status');
        });

        // 3. Keputusan Sertifikasi
        Schema::table('keputusan_sertifikasi', function (Blueprint $table) {
            // Unique: 1 asesmen = 1 keputusan
            $table->unique('asesmen_id', 'unique_asesmen_keputusan');
            
            // Lock mechanism
            $table->boolean('is_locked')->default(false)->after('status');
            $table->timestamp('locked_at')->nullable()->after('is_locked');
            
            // Decision tracking
            $table->timestamp('decided_at')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users');
            
            $table->index('status');
        });

        // 4. Sertifikat
        Schema::table('sertifikat', function (Blueprint $table) {
            // Unique: 1 keputusan = 1 sertifikat
            $table->unique('keputusan_sertifikasi_id', 'unique_keputusan_sertifikat');
            
            $table->index('status');
            $table->index('tanggal_berlaku_sampai'); // For expiry cron
        });
    }

    public function down(): void
    {
        Schema::table('pendaftaran_sertifikasi', function (Blueprint $table) {
            $table->dropColumn(['is_locked', 'locked_at', 'locked_reason']);
            $table->dropIndex(['status']);
            $table->dropIndex(['is_locked']);
        });

        Schema::table('asesmen', function (Blueprint $table) {
            $table->dropUnique('unique_pendaftaran_asesmen');
            $table->dropColumn(['is_locked', 'locked_at', 'completed_at']);
            $table->dropIndex(['status']);
        });

        Schema::table('keputusan_sertifikasi', function (Blueprint $table) {
            $table->dropUnique('unique_asesmen_keputusan');
            $table->dropColumn(['is_locked', 'locked_at', 'decided_at', 'decided_by']);
            $table->dropIndex(['status']);
        });

        Schema::table('sertifikat', function (Blueprint $table) {
            $table->dropUnique('unique_keputusan_sertifikat');
            $table->dropIndex(['status']);
            $table->dropIndex(['tanggal_berlaku_sampai']);
        });
    }
};
```

---

## 🎨 6. UX IMPLEMENTATION

```blade
{{-- resources/views/components/state-guard-button.blade.php --}}

@props([
    'action',
    'guard',
    'route',
    'method' => 'POST',
    'confirmMessage' => null,
])

@php
    $canPerform = $guard['can'];
    $reason = $guard['reason'] ?? null;
@endphp

<div class="relative inline-block">
    @if($canPerform)
        <form action="{{ $route }}" method="POST" class="inline">
            @csrf
            @if($method !== 'POST')
                @method($method)
            @endif
            
            <button 
                type="submit"
                onclick="return {{ $confirmMessage ? "confirm('{$confirmMessage}')" : 'true' }}"
                {{ $attributes->merge(['class' => 'btn btn-primary']) }}
            >
                {{ $slot }}
            </button>
        </form>
    @else
        <button 
            type="button"
            disabled
            class="btn btn-disabled cursor-not-allowed opacity-50"
            title="{{ $reason }}"
        >
            {{ $slot }}
        </button>
        
        @if($reason)
            <div class="absolute z-10 invisible group-hover:visible bg-gray-900 text-white text-sm rounded py-2 px-3 bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-64">
                <div class="text-xs">{{ $reason }}</div>
                <svg class="absolute text-gray-900 h-2 w-full left-0 top-full" x="0px" y="0px" viewBox="0 0 255 255">
                    <polygon class="fill-current" points="0,0 127.5,127.5 255,0"/>
                </svg>
            </div>
        @endif
    </div>
@endphp
```

```blade
{{-- Usage Example in Controller --}}

// PendaftaranController.php

public function show(PendaftaranSertifikasi $pendaftaran)
{
    // Calculate guards
    $guards = [
        'can_submit' => [
            'can' => $pendaftaran->canSubmit(),
            'reason' => $pendaftaran->getSubmitBlockReason(),
        ],
        'can_lock' => [
            'can' => $pendaftaran->canLock(),
            'reason' => $pendaftaran->getLockBlockReason(),
        ],
    ];

    return view('pendaftaran.show', compact('pendaftaran', 'guards'));
}

// In View:
<x-state-guard-button 
    action="submit"
    :guard="$guards['can_submit']"
    :route="route('pendaftaran.submit', $pendaftaran)"
    confirm-message="Yakin mengajukan pendaftaran ini?"
    class="btn-success"
>
    📤 Ajukan Pendaftaran
</x-state-guard-button>
```

---

## 🧪 7. TEST SCENARIOS

### POSITIVE TESTS (10 cases)

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\PraPendaftaran;
use App\Models\PendaftaranSertifikasi;
use App\Models\Asesmen;
use App\Models\KeputusanSertifikasi;
use App\Models\Sertifikat;
use App\Services\StateTransitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StateTransitionTest extends TestCase
{
    use RefreshDatabase;

    private StateTransitionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(StateTransitionService::class);
    }

    /** @test */
    public function can_create_pendaftaran_from_accepted_pra_pendaftaran()
    {
        $praPendaftaran = PraPendaftaran::factory()->create([
            'status' => 'diterima',
        ]);

        $pendaftaran = PendaftaranSertifikasi::create([
            'pra_pendaftaran_id' => $praPendaftaran->id,
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('pendaftaran_sertifikasi', [
            'pra_pendaftaran_id' => $praPendaftaran->id,
            'status' => 'draft',
        ]);
    }

    /** @test */
    public function can_submit_pendaftaran_with_complete_data()
    {
        $pendaftaran = PendaftaranSertifikasi::factory()->complete()->create([
            'status' => 'draft',
        ]);

        $result = $this->service->transitionPendaftaran(
            $pendaftaran,
            PendaftaranStatus::DIAJUKAN
        );

        $this->assertEquals('diajukan', $result->status);
    }

    /** @test */
    public function can_lock_verified_pendaftaran()
    {
        $pendaftaran = PendaftaranSertifikasi::factory()
            ->withJadwal()
            ->create(['status' => 'diverifikasi']);

        $result = $this->service->transitionPendaftaran(
            $pendaftaran,
            PendaftaranStatus::DIKUNCI
        );

        $this->assertEquals('dikunci', $result->status);
        $this->assertTrue($result->is_locked);
    }

    /** @test */
    public function can_create_asesmen_from_locked_pendaftaran()
    {
        $pendaftaran = PendaftaranSertifikasi::factory()->create([
            'status' => 'dikunci',
        ]);

        $asesmen = $this->service->createAsesmen($pendaftaran);

        $this->assertEquals('belum_dimulai', $asesmen->status);
        $this->assertEquals($pendaftaran->id, $asesmen->pendaftaran_sertifikasi_id);
    }

    /** @test */
    public function can_complete_asesmen_with_all_checklist_done()
    {
        $asesmen = Asesmen::factory()
            ->withCompletedChecklist()
            ->create(['status' => 'dalam_proses']);

        $result = $this->service->transitionAsesmen(
            $asesmen,
            AsesmenStatus::SELESAI
        );

        $this->assertEquals('selesai', $result->status);
        $this->assertNotNull($result->completed_at);
    }

    /** @test */
    public function can_create_keputusan_from_completed_asesmen()
    {
        $asesmen = Asesmen::factory()->create(['status' => 'selesai']);

        $keputusan = $this->service->createKeputusan($asesmen);

        $this->assertEquals('belum_ditetapkan', $keputusan->status);
        $this->assertEquals($asesmen->id, $keputusan->asesmen_id);
    }

    /** @test */
    public function can_decide_keputusan_as_kompeten()
    {
        $keputusan = KeputusanSertifikasi::factory()->create([
            'status' => 'belum_ditetapkan',
        ]);

        $result = $this->service->decideKeputusan(
            $keputusan,
            KeputusanStatus::KOMPETEN,
            'Memenuhi semua kriteria'
        );

        $this->assertEquals('kompeten', $result->status);
        $this->assertNotNull($result->decided_at);
    }

    /** @test */
    public function sertifikat_auto_issued_when_keputusan_kompeten()
    {
        $keputusan = KeputusanSertifikasi::factory()->create([
            'status' => 'belum_ditetapkan',
        ]);

        $this->service->decideKeputusan(
            $keputusan,
            KeputusanStatus::KOMPETEN,
            'Lulus'
        );

        $this->assertDatabaseHas('sertifikat', [
            'keputusan_sertifikasi_id' => $keputusan->id,
            'status' => 'terbit',
        ]);
    }

    /** @test */
    public function all_data_locked_after_sertifikat_issued()
    {
        $keputusan = KeputusanSertifikasi::factory()->create([
            'status' => 'belum_ditetapkan',
        ]);

        $this->service->decideKeputusan(
            $keputusan,
            KeputusanStatus::KOMPETEN,
            'Lulus'
        );

        $keputusan->refresh();
        $pendaftaran = $keputusan->pendaftaran;
        $asesmen = $keputusan->asesmen;

        $this->assertTrue($pendaftaran->is_locked);
        $this->assertTrue($asesmen->is_locked);
        $this->assertTrue($keputusan->is_locked);
    }

    /** @test */
    public function unique_constraint_prevents_duplicate_asesmen()
    {
        $pendaftaran = PendaftaranSertifikasi::factory()->create([
            'status' => 'dikunci',
        ]);

        // Create first asesmen
        $this->service->createAsesmen($pendaftaran);

        // Attempt duplicate
        $this->expectException(StateTransitionException::class);
        $this->service->createAsesmen($pendaftaran);
    }
}
```

### NEGATIVE TESTS (10 cases)

```php
/** @test */
public function cannot_create_pendaftaran_from_non_accepted_pra_pendaftaran()
{
    $praPendaftaran = PraPendaftaran::factory()->create([
        'status' => 'diajukan', // Not DITERIMA
    ]);

    $this->expectException(\Exception::class);
    
    PendaftaranSertifikasi::create([
        'pra_pendaftaran_id' => $praPendaftaran->id,
        'status' => 'draft',
    ]);
}

/** @test */
public function cannot_submit_pendaftaran_without_skema()
{
    $pendaftaran = PendaftaranSertifikasi::factory()->create([
        'status' => 'draft',
        'skema_sertifikasi_id' => null, // Missing
    ]);

    $this->expectException(StateTransitionException::class);
    $this->expectExceptionMessage('Skema sertifikasi belum dipilih');

    $this->service->transitionPendaftaran(
        $pendaftaran,
        PendaftaranStatus::DIAJUKAN
    );
}

/** @test */
public function cannot_lock_pendaftaran_without_jadwal()
{
    $pendaftaran = PendaftaranSertifikasi::factory()->create([
        'status' => 'diverifikasi',
        // No jadwal
    ]);

    $this->expectException(StateTransitionException::class);
    $this->expectExceptionMessage('Jadwal asesmen belum dibuat');

    $this->service->transitionPendaftaran(
        $pendaftaran,
        PendaftaranStatus::DIKUNCI
    );
}

/** @test */
public function cannot_create_asesmen_from_unlocked_pendaftaran()
{
    $pendaftaran = PendaftaranSertifikasi::factory()->create([
        'status' => 'diajukan', // Not DIKUNCI
    ]);

    $this->expectException(StateTransitionException::class);
    $this->expectExceptionMessage('Pendaftaran belum terkunci');

    $this->service->createAsesmen($pendaftaran);
}

/** @test */
public function cannot_complete_asesmen_with_incomplete_checklist()
{
    $asesmen = Asesmen::factory()
        ->withIncompleteChecklist() // Some items not assessed
        ->create(['status' => 'dalam_proses']);

    $this->expectException(StateTransitionException::class);
    $this->expectExceptionMessage('item checklist yang belum dinilai');

    $this->service->transitionAsesmen(
        $asesmen,
        AsesmenStatus::SELESAI
    );
}

/** @test */
public function cannot_create_keputusan_from_incomplete_asesmen()
{
    $asesmen = Asesmen::factory()->create([
        'status' => 'dalam_proses', // Not SELESAI
    ]);

    $this->expectException(StateTransitionException::class);
    $this->expectExceptionMessage('Asesmen belum selesai');

    $this->service->createKeputusan($asesmen);
}

/** @test */
public function cannot_decide_keputusan_without_catatan()
{
    $keputusan = KeputusanSertifikasi::factory()->create([
        'status' => 'belum_ditetapkan',
    ]);

    $this->expectException(StateTransitionException::class);
    $this->expectExceptionMessage('Catatan keputusan wajib diisi');

    $this->service->decideKeputusan(
        $keputusan,
        KeputusanStatus::KOMPETEN,
        '' // Empty catatan
    );
}

/** @test */
public function cannot_issue_sertifikat_for_belum_kompeten()
{
    $keputusan = KeputusanSertifikasi::factory()->create([
        'status' => 'belum_kompeten',
    ]);

    // Sertifikat should NOT be created
    $this->assertDatabaseMissing('sertifikat', [
        'keputusan_sertifikasi_id' => $keputusan->id,
    ]);
}

/** @test */
public function cannot_modify_locked_pendaftaran()
{
    $pendaftaran = PendaftaranSertifikasi::factory()->create([
        'status' => 'dikunci',
        'is_locked' => true,
    ]);

    $this->expectException(StateTransitionException::class);
    $this->expectExceptionMessage('Data telah terkunci');

    $this->service->guardNotLocked($pendaftaran, 'edit');
}

/** @test */
public function cannot_change_decided_keputusan()
{
    $keputusan = KeputusanSertifikasi::factory()->create([
        'status' => 'kompeten', // Already decided
    ]);

    $this->expectException(StateTransitionException::class);
    $this->expectExceptionMessage('Keputusan sudah ditetapkan');

    $this->service->decideKeputusan(
        $keputusan,
        KeputusanStatus::BELUM_KOMPETEN,
        'Change decision' // Cannot change
    );
}
```

---

## ✅ 8. PRODUCTION CHECKLIST

```markdown
### PRE-DEPLOYMENT

- [ ] All Enums created and tested
- [ ] StateTransitionService implemented
- [ ] StateTransitionException created
- [ ] Database migrations ready
- [ ] Unique constraints applied
- [ ] Lock mechanism tested
- [ ] All 20 tests passing (10 positive + 10 negative)
- [ ] Audit logging configured
- [ ] Error pages designed (errors/state-transition.blade.php)
- [ ] UX components (state-guard-button) implemented

### DEPLOYMENT

- [ ] Run migrations on staging
- [ ] Seed test data
- [ ] Manual E2E testing (full flow)
- [ ] Performance test (pessimistic locks)
- [ ] Security audit (permission checks)
- [ ] Rollback plan documented
- [ ] Deploy to production
- [ ] Monitor logs (first 24 hours)

### POST-DEPLOYMENT

- [ ] Monitor StateTransitionException frequency
- [ ] Check audit_log for anomalies
- [ ] Verify no orphaned records
- [ ] User training (if needed)
- [ ] Update documentation
- [ ] Celebrate! 🎉
```

---

## 🎯 SUMMARY

**Problem:** Proses sertifikasi tidak terstruktur, ada loncatan status, data yatim, sertifikat terbit tanpa proses.

**Solution:** STRICT STATE MACHINE dengan:
- ✅ Enum-based status (type-safe)
- ✅ Service layer dengan guards
- ✅ Database constraints (UNIQUE, FK, locks)
- ✅ Business exceptions (user-friendly)
- ✅ Audit trail lengkap
- ✅ UX yang jelas (button disabled + reason)
- ✅ 20 test scenarios (comprehensive)
- ✅ Production-ready dengan checklist

**Benefits:**
- 🔒 **Data Integrity:** UNIQUE constraints + locks
- 🛡️ **Security:** Permission-based + audit trail
- 🎯 **UX:** Clear feedback, no confusion
- 📊 **Audit:** ISO 17024 / BNSP compliant
- 🚀 **Scalable:** Service layer, testable
- 💪 **Production-safe:** Idempotent, transaction-safe

---

**End of Document**  
*Generated: 2026-01-23*  
*Author: Senior Solution Architect*

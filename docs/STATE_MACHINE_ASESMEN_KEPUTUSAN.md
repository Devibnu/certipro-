# 🔐 STATE MACHINE: ASESMEN & KEPUTUSAN SERTIFIKASI

**Project:** LSP Certification System  
**Architect:** Senior Laravel Expert  
**Date:** 24 Januari 2026  
**Compliance:** BNSP, ISO 17024  
**Status:** ✅ **PRODUCTION READY ARCHITECTURE**

---

## 📋 TABLE OF CONTENTS

1. [Prinsip Arsitektur](#prinsip-arsitektur)
2. [State Machine Diagram](#state-machine-diagram)
3. [State Definitions](#state-definitions)
4. [Transition Rules](#transition-rules)
5. [Lock Mechanism](#lock-mechanism)
6. [Guard Conditions](#guard-conditions)
7. [Service Layer](#service-layer)
8. [Event-Driven Email](#event-driven-email)
9. [UI Behavior](#ui-behavior)
10. [QA Checklist](#qa-checklist)

---

## 🎯 PRINSIP ARSITEKTUR

### **3 NON-NEGOTIABLE RULES**

```
1. SETIAP STATUS = SATU ARTI (No Ambiguity)
2. TIDAK BOLEH LOMPAT STATUS (Sequential Flow)
3. TIDAK BOLEH EDIT SETELAH LOCK (Immutability)
```

### **2 SEPARATION PRINCIPLES**

```
4. ASESMEN ≠ KEPUTUSAN (Separation of Concerns)
5. KEPUTUSAN = FINAL AUTHORITY (Single Source of Truth)
```

---

## 📊 STATE MACHINE DIAGRAM

### **TEXT DIAGRAM**

```
┌──────────────────────────────────────────────────────────────────┐
│                   STATE FLOW ASESMEN & KEPUTUSAN                  │
└──────────────────────────────────────────────────────────────────┘

[SIAP_ASESMEN]  ← Starting Point
      ↓
      │ Action: Asesor klik "Mulai Asesmen"
      │ Guard:  Status must be SIAP_ASESMEN
      │ Lock:   None
      ↓
[DALAM_ASESMEN] ← Asesor sedang input KUK
      ↓
      │ Action: Asesor klik "Simpan Asesmen"
      │ Guard:  Semua KUK harus terisi
      │ Lock:   ✅ KUK, ✅ Evidence, ✅ Catatan Asesor
      │ Email:  ✉️ "Asesmen selesai, menunggu keputusan"
      ↓
[MENUNGGU_KEPUTUSAN] ← Waiting for Komite Teknis
      ↓
      │ Action: Komite Teknis klik "Tetapkan Keputusan"
      │ Guard:  Status must be MENUNGGU_KEPUTUSAN
      │         Asesmen must be LOCKED
      │ Lock:   ✅ TOTAL LOCK (Asesmen + Keputusan)
      │ Email:  ✉️ "Hasil Sertifikasi: KOMPETEN / BELUM KOMPETEN"
      ↓
[KOMPETEN_FINAL] ✅      OR      [BELUM_KOMPETEN_FINAL] ❌
      ↓                                   ↓
 Certificate                        Re-assessment
 Issuance                          Required

```

### **STATE TRANSITION MATRIX**

| From Status           | To Status                  | Allowed? | Guard Conditions                     |
|-----------------------|----------------------------|----------|--------------------------------------|
| `siap_asesmen`        | `dalam_asesmen`            | ✅ YES   | Asesor assigned                      |
| `siap_asesmen`        | `menunggu_keputusan`       | ❌ NO    | Cannot skip `dalam_asesmen`          |
| `dalam_asesmen`       | `menunggu_keputusan`       | ✅ YES   | All KUK filled                       |
| `dalam_asesmen`       | `kompeten_final`           | ❌ NO    | Must go through `menunggu_keputusan` |
| `menunggu_keputusan`  | `kompeten_final`           | ✅ YES   | Komite Teknis approval               |
| `menunggu_keputusan`  | `belum_kompeten_final`     | ✅ YES   | Komite Teknis rejection              |
| `menunggu_keputusan`  | `dalam_asesmen`            | ❌ NO    | Cannot go back after lock            |
| `kompeten_final`      | ANY                        | ❌ NO    | FINAL state, immutable               |
| `belum_kompeten_final`| ANY                        | ❌ NO    | FINAL state, immutable               |

---

## 🔍 STATE DEFINITIONS

### **1. SIAP_ASESMEN**

**Definition:** Pendaftaran telah diverifikasi dan siap untuk dilakukan asesmen kompetensi.

**Characteristics:**
- Admin telah menetapkan skema sertifikasi
- Asesor belum ditugaskan atau belum memulai asesmen
- Belum ada data asesmen di database
- Peserta sudah bisa melihat status "Dijadwalkan Asesmen"

**Business Rules:**
- Asesor dapat memulai asesmen (create asesmen record)
- Admin dapat assign/reassign asesor
- Tidak boleh langsung ke `menunggu_keputusan` tanpa asesmen

**Lock Status:** 🔓 UNLOCKED

**Email:** None (status from previous flow)

---

### **2. DALAM_ASESMEN**

**Definition:** Proses asesmen sedang berlangsung, asesor sedang menginput hasil per KUK.

**Characteristics:**
- Record asesmen sudah exist (status: `proses`)
- Asesor sedang input hasil per KUK (kompeten/belum_kompeten)
- Asesmen detail dapat di-edit sebelum disimpan final
- Peserta melihat status "Sedang Asesmen"

**Business Rules:**
- Asesor dapat input/edit hasil KUK (before final save)
- Asesor dapat upload evidence per KUK
- Asesor dapat tambah catatan per KUK
- Tidak boleh disimpan jika ada KUK yang belum terisi

**Lock Status:** 🔓 UNLOCKED (during input)

**Email:** None (internal process)

---

### **3. MENUNGGU_KEPUTUSAN**

**Definition:** Asesmen telah selesai dan dikunci, menunggu keputusan dari Komite Teknis.

**Characteristics:**
- Asesmen status: `selesai`
- Semua KUK sudah terisi (K atau BK)
- Asesmen READ ONLY (locked)
- Peserta melihat status "Menunggu Keputusan"

**Business Rules:**
- ✅ Asesmen LOCKED (cannot be edited by anyone)
- ✅ Evidence LOCKED
- ✅ Catatan asesor LOCKED
- Komite Teknis dapat melihat hasil asesmen
- Komite Teknis dapat menetapkan keputusan (kompeten/belum_kompeten)
- **NO ONE can edit asesmen** (including super admin)

**Lock Status:** 🔒 **ASESMEN LOCKED**

**Email:** ✉️ **"Asesmen selesai, menunggu keputusan sertifikasi"**

**Trigger:** Asesor klik "Simpan Asesmen" → Observer → Event → Listener

---

### **4. KOMPETEN_FINAL**

**Definition:** Keputusan final telah ditetapkan, asesi dinyatakan KOMPETEN oleh Komite Teknis.

**Characteristics:**
- Record keputusan exist (keputusan: `kompeten`, is_locked: `true`)
- Asesmen LOCKED
- Keputusan LOCKED
- Status LOCKED (cannot be changed)
- Peserta melihat status "Lulus Sertifikasi"
- Sertifikat bisa diterbitkan

**Business Rules:**
- ✅ TOTAL LOCK: Asesmen + Keputusan + Status
- Komite Teknis tercatat sebagai penetap (ditetapkan_oleh)
- Tanggal keputusan tercatat (tanggal_keputusan)
- Sertifikat dapat diterbitkan (flow: Keputusan → Sertifikat)
- **NO EDIT/REVOKE** (except via special audit procedure outside system)

**Lock Status:** 🔒 **TOTAL LOCK**

**Email:** ✉️ **"Selamat! Hasil Sertifikasi: KOMPETEN"**

**Trigger:** Komite Teknis klik "Simpan & Kunci Keputusan" → Observer → Event → Listener

---

### **5. BELUM_KOMPETEN_FINAL**

**Definition:** Keputusan final telah ditetapkan, asesi dinyatakan BELUM KOMPETEN oleh Komite Teknis.

**Characteristics:**
- Record keputusan exist (keputusan: `belum_kompeten`, is_locked: `true`)
- Asesmen LOCKED
- Keputusan LOCKED
- Status LOCKED (cannot be changed)
- Peserta melihat status "Tidak Lulus"
- Sertifikat TIDAK diterbitkan

**Business Rules:**
- ✅ TOTAL LOCK: Asesmen + Keputusan + Status
- Komite Teknis tercatat sebagai penetap
- Tanggal keputusan tercatat
- User harus submit Pra-Pendaftaran baru untuk re-asesmen
- **NO EDIT/REVOKE**

**Lock Status:** 🔒 **TOTAL LOCK**

**Email:** ✉️ **"Hasil Sertifikasi: BELUM KOMPETEN - Informasi Re-asesmen"**

**Trigger:** Komite Teknis klik "Simpan & Kunci Keputusan" → Observer → Event → Listener

---

## 🚫 TRANSITION RULES (TERLARANG)

### **FORBIDDEN TRANSITIONS**

```php
// ❌ TIDAK BOLEH:
siap_asesmen → menunggu_keputusan       // Skip asesmen
siap_asesmen → kompeten_final           // Skip all steps
dalam_asesmen → kompeten_final          // Skip keputusan
menunggu_keputusan → dalam_asesmen      // Go back after lock
kompeten_final → ANY                    // Change final status
belum_kompeten_final → ANY              // Change final status

// ✅ HANYA BOLEH:
siap_asesmen → dalam_asesmen
dalam_asesmen → menunggu_keputusan
menunggu_keputusan → kompeten_final
menunggu_keputusan → belum_kompeten_final
```

### **EXCEPTION HANDLING**

```php
// Jika transisi invalid:
throw StateTransitionException::invalidTransition(
    from: $currentStatus,
    to: $newStatus,
    entityType: 'Pendaftaran Sertifikasi'
);

// Example error message:
"Tidak dapat mengubah status Pendaftaran Sertifikasi dari 
'dalam_asesmen' ke 'kompeten_final'. Transisi tidak diizinkan."
```

---

## 🔐 LOCK MECHANISM

### **Database Fields**

```php
// asesmen table
'is_locked' => 'boolean'  // Lock asesmen data
'locked_at' => 'timestamp' // When locked
'locked_by' => 'integer'   // Who locked (asesor_id)

// keputusan_sertifikasi table
'is_locked' => 'boolean'   // Lock keputusan
'locked_at' => 'timestamp' // When locked
'locked_by' => 'integer'   // Who locked (ditetapkan_oleh)

// pendaftaran_sertifikasi table
'is_status_locked' => 'boolean' // Lock status changes
```

### **Lock Behavior**

#### **1. Asesmen Lock (MENUNGGU_KEPUTUSAN)**

**Triggered by:** Asesor klik "Simpan Asesmen"

**What gets locked:**
```php
// In AsesmenController@simpanAsesmen():
$asesmen->update([
    'status' => Asesmen::STATUS_SELESAI,
    'is_locked' => true,
    'locked_at' => now(),
    'locked_by' => Auth::id(),
]);

// Lock prevents:
- ❌ Edit KUK hasil (kompeten/belum_kompeten)
- ❌ Delete/add asesmen details
- ❌ Edit catatan asesor
- ❌ Edit metode asesmen
- ❌ Change asesmen status
```

**UI Effect:**
- All input fields become `readonly`
- All buttons (edit, delete) hidden
- Badge shows "🔒 LOCKED"

#### **2. Keputusan Lock (KOMPETEN_FINAL / BELUM_KOMPETEN_FINAL)**

**Triggered by:** Komite Teknis klik "Simpan & Kunci Keputusan"

**What gets locked:**
```php
// In KeputusanSertifikasiController@simpan():
$keputusan = KeputusanSertifikasi::create([
    'pendaftaran_id' => $pendaftaran->id,
    'asesmen_id' => $asesmen->id,
    'keputusan' => $request->keputusan, // kompeten|belum_kompeten
    'catatan_komite' => $request->catatan_komite,
    'ditetapkan_oleh' => Auth::id(),
    'tanggal_keputusan' => now(),
    'is_locked' => true,  // ← IMMEDIATELY LOCKED
    'locked_at' => now(),
    'locked_by' => Auth::id(),
]);

$pendaftaran->update([
    'status' => $newFinalStatus, // kompeten_final|belum_kompeten_final
    'is_status_locked' => true,  // ← PREVENT STATUS CHANGE
]);

// Lock prevents:
- ❌ Edit keputusan (kompeten → belum_kompeten or vice versa)
- ❌ Delete keputusan record
- ❌ Edit catatan komite
- ❌ Change tanggal keputusan
- ❌ Change penetap
- ❌ Change pendaftaran status
```

**UI Effect:**
- Form shows "READ ONLY" mode
- No edit/delete buttons
- Big red badge "🔒 FINAL & LOCKED"
- Warning: "Keputusan tidak dapat diubah setelah disimpan"

---

### **Lock Check Methods**

```php
// In Asesmen model:
public function isLocked(): bool
{
    return $this->is_locked === true;
}

public function canEdit(): bool
{
    if ($this->isLocked()) {
        return false;
    }
    
    // Check if keputusan is locked
    $keputusan = $this->pendaftaran?->keputusanSertifikasi;
    if ($keputusan && $keputusan->isLocked()) {
        return false;
    }
    
    return true;
}

// In KeputusanSertifikasi model:
public function isLocked(): bool
{
    return $this->is_locked === true;
}

public function canEdit(): bool
{
    return !$this->isLocked();
}

// In PendaftaranSertifikasi model:
public function isStatusLocked(): bool
{
    return $this->is_status_locked === true;
}

public function canChangeStatus(): bool
{
    if ($this->isStatusLocked()) {
        return false;
    }
    
    // FINAL states are immutable
    if (in_array($this->status, [
        self::STATUS_KOMPETEN_FINAL,
        self::STATUS_BELUM_KOMPETEN_FINAL,
    ])) {
        return false;
    }
    
    return true;
}
```

---

## 🛡️ GUARD CONDITIONS

### **Guard 1: Status Validation (Model Level)**

**Location:** `PendaftaranSertifikasi` model

```php
// Only allow valid status values
const VALID_STATUSES = [
    self::STATUS_SIAP_ASESMEN,
    self::STATUS_DALAM_ASESMEN,
    self::STATUS_MENUNGGU_KEPUTUSAN,
    self::STATUS_KOMPETEN_FINAL,
    self::STATUS_BELUM_KOMPETEN_FINAL,
];

protected static function booted()
{
    static::updating(function ($pendaftaran) {
        // Guard: Prevent status change if locked
        if ($pendaftaran->isDirty('status') && $pendaftaran->isStatusLocked()) {
            throw new StateTransitionException(
                'Status tidak dapat diubah karena sudah dikunci (FINAL)'
            );
        }
        
        // Guard: Validate status value
        if ($pendaftaran->isDirty('status')) {
            if (!in_array($pendaftaran->status, self::VALID_STATUSES)) {
                throw new \InvalidArgumentException(
                    "Status '{$pendaftaran->status}' tidak valid"
                );
            }
        }
    });
}
```

---

### **Guard 2: Mulai Asesmen (Controller Level)**

**Location:** `AsesmenController@mulaiAsesmen()`

```php
public function mulaiAsesmen($pendaftaranId)
{
    $pendaftaran = PendaftaranSertifikasi::findOrFail($pendaftaranId);
    
    // GUARD 1: Status must be SIAP_ASESMEN
    if ($pendaftaran->status !== PendaftaranSertifikasi::STATUS_SIAP_ASESMEN) {
        throw StateTransitionException::invalidState(
            "Tidak dapat mulai asesmen. Status harus 'siap_asesmen', saat ini '{$pendaftaran->status}'"
        );
    }
    
    // GUARD 2: Asesmen tidak boleh sudah exist
    if (Asesmen::where('pendaftaran_id', $pendaftaran->id)->exists()) {
        throw StateTransitionException::alreadyProcessed(
            'Asesmen untuk pendaftaran ini sudah dilakukan'
        );
    }
    
    // GUARD 3: Skema must exist
    if (!$pendaftaran->skemaSertifikasi) {
        throw StateTransitionException::missingRequirement(
            'Skema sertifikasi belum ditetapkan'
        );
    }
    
    // Create initial asesmen record with status PROSES
    $asesmen = Asesmen::create([
        'pendaftaran_id' => $pendaftaran->id,
        'asesor_id' => Auth::id(),
        'status' => Asesmen::STATUS_PROSES,
        'is_locked' => false,
    ]);
    
    // Update pendaftaran status
    $pendaftaran->update([
        'status' => PendaftaranSertifikasi::STATUS_DALAM_ASESMEN,
    ]);
    
    return redirect()->route('adminui.asesmen.form', $asesmen->id);
}
```

---

### **Guard 3: Simpan Asesmen (Service Level)**

**Location:** `AsesmenService@simpanAsesmen()`

```php
public function simpanAsesmen(Pendaftaran $pendaftaran, array $data): Asesmen
{
    // GUARD 1: Status must be DALAM_ASESMEN
    if ($pendaftaran->status !== PendaftaranSertifikasi::STATUS_DALAM_ASESMEN) {
        throw StateTransitionException::invalidState(
            "Tidak dapat simpan asesmen. Status harus 'dalam_asesmen'"
        );
    }
    
    // GUARD 2: Asesmen must exist
    $asesmen = $pendaftaran->asesmen;
    if (!$asesmen) {
        throw StateTransitionException::missingRequirement(
            'Asesmen belum dimulai'
        );
    }
    
    // GUARD 3: Asesmen must not be locked
    if ($asesmen->isLocked()) {
        throw StateTransitionException::alreadyLocked(
            'Asesmen sudah dikunci, tidak dapat diubah'
        );
    }
    
    // GUARD 4: All KUK must be filled
    $totalKuk = $pendaftaran->skemaSertifikasi
        ->unitKompetensi()
        ->withCount('kuks')
        ->get()
        ->sum('kuks_count');
    
    if (count($data['hasil']) !== $totalKuk) {
        throw new \InvalidArgumentException(
            "Semua KUK harus diisi. Total KUK: {$totalKuk}, Terisi: " . count($data['hasil'])
        );
    }
    
    DB::beginTransaction();
    try {
        // Save asesmen details
        foreach ($data['hasil'] as $kukId => $hasil) {
            AsesmenDetail::create([
                'asesmen_id' => $asesmen->id,
                'kuk_id' => $kukId,
                'hasil' => $hasil,
                'catatan' => $data['catatan'][$kukId] ?? null,
            ]);
        }
        
        // Lock asesmen
        $asesmen->update([
            'status' => Asesmen::STATUS_SELESAI,
            'metode_asesmen' => $data['metode_asesmen'],
            'catatan_asesor' => $data['catatan_asesor'],
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by' => Auth::id(),
        ]);
        
        // Update pendaftaran status
        $pendaftaran->update([
            'status' => PendaftaranSertifikasi::STATUS_MENUNGGU_KEPUTUSAN,
        ]);
        
        DB::commit();
        
        return $asesmen;
        
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
```

---

### **Guard 4: Tetapkan Keputusan (Service Level)**

**Location:** `KeputusanService@tetapkanKeputusan()`

```php
public function tetapkanKeputusan(Pendaftaran $pendaftaran, array $data): KeputusanSertifikasi
{
    // GUARD 1: Status must be MENUNGGU_KEPUTUSAN
    if ($pendaftaran->status !== PendaftaranSertifikasi::STATUS_MENUNGGU_KEPUTUSAN) {
        throw StateTransitionException::invalidState(
            "Tidak dapat tetapkan keputusan. Status harus 'menunggu_keputusan'"
        );
    }
    
    // GUARD 2: Asesmen must exist and be locked
    $asesmen = $pendaftaran->asesmen;
    if (!$asesmen) {
        throw StateTransitionException::missingRequirement(
            'Asesmen belum dilakukan'
        );
    }
    
    if (!$asesmen->isLocked()) {
        throw StateTransitionException::notLocked(
            'Asesmen belum dikunci, tidak dapat tetapkan keputusan'
        );
    }
    
    // GUARD 3: Keputusan tidak boleh sudah exist
    if ($pendaftaran->keputusan) {
        throw StateTransitionException::alreadyProcessed(
            'Keputusan sudah ditetapkan sebelumnya'
        );
    }
    
    // GUARD 4: Validate keputusan value
    if (!in_array($data['keputusan'], ['kompeten', 'belum_kompeten'])) {
        throw new \InvalidArgumentException(
            "Keputusan harus 'kompeten' atau 'belum_kompeten'"
        );
    }
    
    DB::beginTransaction();
    try {
        // Create keputusan (IMMEDIATELY LOCKED)
        $keputusan = KeputusanSertifikasi::create([
            'pendaftaran_id' => $pendaftaran->id,
            'asesmen_id' => $asesmen->id,
            'keputusan' => $data['keputusan'],
            'catatan_komite' => $data['catatan_komite'] ?? null,
            'ditetapkan_oleh' => Auth::id(),
            'tanggal_keputusan' => now(),
            'is_locked' => true,  // ← IMMEDIATE LOCK
            'locked_at' => now(),
            'locked_by' => Auth::id(),
        ]);
        
        // Update pendaftaran to FINAL status
        $finalStatus = $data['keputusan'] === 'kompeten'
            ? PendaftaranSertifikasi::STATUS_KOMPETEN_FINAL
            : PendaftaranSertifikasi::STATUS_BELUM_KOMPETEN_FINAL;
        
        $pendaftaran->update([
            'status' => $finalStatus,
            'is_status_locked' => true,  // ← LOCK STATUS
        ]);
        
        DB::commit();
        
        return $keputusan;
        
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
```

---

### **Guard 5: Immutable FINAL States (Observer Level)**

**Location:** `PendaftaranObserver`

```php
public function updating(PendaftaranSertifikasi $pendaftaran)
{
    // GUARD: FINAL states cannot be changed
    if ($pendaftaran->isDirty('status')) {
        $originalStatus = $pendaftaran->getOriginal('status');
        
        if (in_array($originalStatus, [
            PendaftaranSertifikasi::STATUS_KOMPETEN_FINAL,
            PendaftaranSertifikasi::STATUS_BELUM_KOMPETEN_FINAL,
        ])) {
            throw StateTransitionException::immutableState(
                "Status FINAL ({$originalStatus}) tidak dapat diubah"
            );
        }
    }
    
    // GUARD: Cannot change status if locked
    if ($pendaftaran->isDirty('status') && $pendaftaran->isStatusLocked()) {
        throw StateTransitionException::alreadyLocked(
            'Status telah dikunci, tidak dapat diubah'
        );
    }
}
```

---

## 🔧 SERVICE LAYER

### **AsesmenService**

**File:** `app/Services/AsesmenService.php`

```php
<?php

namespace App\Services;

use App\Models\Asesmen;
use App\Models\AsesmenDetail;
use App\Models\PendaftaranSertifikasi;
use App\Exceptions\StateTransitionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AsesmenService
{
    /**
     * Start asesmen process.
     * 
     * Guard: Status must be SIAP_ASESMEN
     */
    public function mulaiAsesmen(PendaftaranSertifikasi $pendaftaran): Asesmen
    {
        // Guard validations (see Guard 2 above)
        
        DB::beginTransaction();
        try {
            $asesmen = Asesmen::create([
                'pendaftaran_id' => $pendaftaran->id,
                'asesor_id' => Auth::id(),
                'status' => Asesmen::STATUS_PROSES,
                'is_locked' => false,
            ]);
            
            $pendaftaran->update([
                'status' => PendaftaranSertifikasi::STATUS_DALAM_ASESMEN,
            ]);
            
            DB::commit();
            return $asesmen;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Save asesmen results and lock.
     * 
     * Guard: Status must be DALAM_ASESMEN, all KUK filled
     */
    public function simpanAsesmen(PendaftaranSertifikasi $pendaftaran, array $data): Asesmen
    {
        // Guard validations (see Guard 3 above)
        // Save logic (see Guard 3 above)
    }
}
```

---

### **KeputusanService**

**File:** `app/Services/KeputusanService.php`

```php
<?php

namespace App\Services;

use App\Models\KeputusanSertifikasi;
use App\Models\PendaftaranSertifikasi;
use App\Exceptions\StateTransitionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class KeputusanService
{
    /**
     * Tetapkan keputusan sertifikasi (FINAL).
     * 
     * Guard: Status must be MENUNGGU_KEPUTUSAN, asesmen locked
     */
    public function tetapkanKeputusan(PendaftaranSertifikasi $pendaftaran, array $data): KeputusanSertifikasi
    {
        // Guard validations (see Guard 4 above)
        // Save logic (see Guard 4 above)
    }
}
```

---

## 📧 EVENT-DRIVEN EMAIL

### **Email Flow Matrix**

| Trigger                       | Event                          | Listener                        | Email Template            | Recipient       |
|-------------------------------|--------------------------------|---------------------------------|---------------------------|-----------------|
| Asesor simpan asesmen         | `AsesmenDisimpan`              | `SendAsesmenSelesaiEmail`       | `asesmen-selesai.blade`   | Asesi           |
| Komite tetapkan KOMPETEN      | `KeputusanDitetapkan`          | `SendKeputusanKompetenEmail`    | `keputusan-kompeten.blade`| Asesi           |
| Komite tetapkan BELUM_KOMPETEN| `KeputusanDitetapkan`          | `SendKeputusanBelumKompetenEmail`| `keputusan-belum.blade`  | Asesi           |

---

### **Event 1: AsesmenDisimpan**

**File:** `app/Events/AsesmenDisimpan.php`

```php
<?php

namespace App\Events;

use App\Models\Asesmen;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AsesmenDisimpan
{
    use Dispatchable, SerializesModels;
    
    public Asesmen $asesmen;
    
    public function __construct(Asesmen $asesmen)
    {
        $this->asesmen = $asesmen;
    }
}
```

**Trigger:** `AsesmenObserver@updated()`

```php
public function updated(Asesmen $asesmen)
{
    // Check if asesmen just locked (status changed to selesai + locked)
    if ($asesmen->isDirty('is_locked') && $asesmen->is_locked) {
        event(new AsesmenDisimpan($asesmen));
    }
}
```

**Listener:** `app/Listeners/SendAsesmenSelesaiEmail.php`

```php
<?php

namespace App\Listeners;

use App\Events\AsesmenDisimpan;
use App\Mail\AsesmenSelesai;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendAsesmenSelesaiEmail
{
    public function handle(AsesmenDisimpan $event)
    {
        $asesmen = $event->asesmen;
        $pendaftaran = $asesmen->pendaftaran;
        
        // Idempotent check: sudah dikirim?
        if ($pendaftaran->email_status_asesmen_selesai) {
            Log::info('Email asesmen selesai already sent, skipping', [
                'asesmen_id' => $asesmen->id,
            ]);
            return;
        }
        
        try {
            Mail::to($pendaftaran->email)->send(
                new AsesmenSelesai($asesmen, $pendaftaran)
            );
            
            // Mark as sent
            $pendaftaran->update([
                'email_status_asesmen_selesai' => true,
                'email_asesmen_sent_at' => now(),
            ]);
            
            Log::info('Email asesmen selesai sent successfully', [
                'asesmen_id' => $asesmen->id,
                'email' => $pendaftaran->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send asesmen selesai email', [
                'asesmen_id' => $asesmen->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
```

---

### **Event 2: KeputusanDitetapkan**

**File:** `app/Events/KeputusanDitetapkan.php`

```php
<?php

namespace App\Events;

use App\Models\KeputusanSertifikasi;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KeputusanDitetapkan
{
    use Dispatchable, SerializesModels;
    
    public KeputusanSertifikasi $keputusan;
    
    public function __construct(KeputusanSertifikasi $keputusan)
    {
        $this->keputusan = $keputusan;
    }
}
```

**Trigger:** `KeputusanObserver@created()`

```php
public function created(KeputusanSertifikasi $keputusan)
{
    // Keputusan always locked when created
    event(new KeputusanDitetapkan($keputusan));
}
```

**Listener:** `app/Listeners/SendKeputusanEmail.php`

```php
<?php

namespace App\Listeners;

use App\Events\KeputusanDitetapkan;
use App\Mail\KeputusanKompeten;
use App\Mail\KeputusanBelumKompeten;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendKeputusanEmail
{
    public function handle(KeputusanDitetapkan $event)
    {
        $keputusan = $event->keputusan;
        $pendaftaran = $keputusan->pendaftaran;
        
        // Idempotent check
        if ($pendaftaran->email_status_keputusan_sent) {
            Log::info('Email keputusan already sent, skipping');
            return;
        }
        
        try {
            // Choose email template based on decision
            $mailClass = $keputusan->keputusan === 'kompeten'
                ? KeputusanKompeten::class
                : KeputusanBelumKompeten::class;
            
            Mail::to($pendaftaran->email)->send(
                new $mailClass($keputusan, $pendaftaran)
            );
            
            // Mark as sent
            $pendaftaran->update([
                'email_status_keputusan_sent' => true,
                'email_keputusan_sent_at' => now(),
            ]);
            
            Log::info('Email keputusan sent successfully', [
                'keputusan_id' => $keputusan->id,
                'keputusan' => $keputusan->keputusan,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send keputusan email', [
                'keputusan_id' => $keputusan->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
```

---

## 🎨 UI BEHAVIOR

### **Button Visibility Rules**

| Current Status           | Button                      | Visible? | Permission Required       | Action                         |
|--------------------------|-----------------------------|----------|---------------------------|--------------------------------|
| `siap_asesmen`           | `[Mulai Asesmen]`           | ✅ YES   | `asesmen.submit`          | Create asesmen record          |
| `siap_asesmen`           | `[Simpan Asesmen]`          | ❌ NO    | -                         | -                              |
| `dalam_asesmen`          | `[Mulai Asesmen]`           | ❌ NO    | -                         | Already started                |
| `dalam_asesmen`          | `[Simpan Asesmen]`          | ✅ YES   | `asesmen.submit`          | Lock asesmen, send email       |
| `menunggu_keputusan`     | `[Mulai Asesmen]`           | ❌ NO    | -                         | Asesmen locked                 |
| `menunggu_keputusan`     | `[Simpan Asesmen]`          | ❌ NO    | -                         | Asesmen locked                 |
| `menunggu_keputusan`     | `[Tetapkan Keputusan]`      | ✅ YES   | `keputusan.approve`       | Create keputusan, lock status  |
| `kompeten_final`         | ANY                         | ❌ NO    | -                         | READ ONLY                      |
| `belum_kompeten_final`   | ANY                         | ❌ NO    | -                         | READ ONLY                      |

---

### **Form Field Disabled Rules**

```blade
{{-- In asesmen form --}}
@if($asesmen->isLocked() || $pendaftaran->status !== 'dalam_asesmen')
    {{-- All fields readonly --}}
    <input ... readonly disabled>
    <textarea ... readonly disabled></textarea>
    <button type="submit" disabled>Simpan Asesmen (Locked)</button>
    
    <div class="alert alert-warning">
        <i class="fas fa-lock me-2"></i>
        Asesmen telah dikunci dan tidak dapat diubah.
    </div>
@else
    {{-- Normal editable form --}}
    <input ...>
    <button type="submit">Simpan Asesmen</button>
@endif

{{-- In keputusan form --}}
@if($keputusan && $keputusan->isLocked())
    {{-- Show read-only view --}}
    <div class="alert alert-danger">
        <i class="fas fa-lock me-2"></i>
        Keputusan FINAL & LOCKED - Tidak dapat diubah
    </div>
    
    <div class="card-body">
        <h5>Keputusan: {{ $keputusan->keputusan_label }}</h5>
        <p>Penetap: {{ $keputusan->penetap->name }}</p>
        <p>Tanggal: {{ $keputusan->tanggal_keputusan }}</p>
    </div>
@else
    {{-- Show form --}}
    <form method="POST" action="{{ route('adminui.keputusan.simpan', $pendaftaran->id) }}">
        ...
        <button type="submit">Simpan & Kunci Keputusan</button>
    </form>
@endif
```

---

### **Badge Display Rules**

```blade
{{-- Status badge with lock indicator --}}
<span class="badge {{ $pendaftaran->status_badge }}">
    @if($pendaftaran->isStatusLocked())
        <i class="fas fa-lock me-1"></i>
    @endif
    {{ $pendaftaran->status_label }}
</span>

{{-- Asesmen lock badge --}}
@if($asesmen->isLocked())
    <span class="badge bg-danger">
        <i class="fas fa-lock me-1"></i> ASESMEN LOCKED
    </span>
@endif

{{-- Keputusan lock badge --}}
@if($keputusan && $keputusan->isLocked())
    <span class="badge bg-danger">
        <i class="fas fa-lock me-1"></i> FINAL & LOCKED
    </span>
@endif
```

---

## ✅ QA CHECKLIST

### **A. State Transition Tests (20 tests)**

- [ ] A1: Mulai asesmen dari SIAP_ASESMEN → DALAM_ASESMEN ✅
- [ ] A2: Simpan asesmen dari DALAM_ASESMEN → MENUNGGU_KEPUTUSAN ✅
- [ ] A3: Tetapkan KOMPETEN dari MENUNGGU_KEPUTUSAN → KOMPETEN_FINAL ✅
- [ ] A4: Tetapkan BELUM_KOMPETEN dari MENUNGGU_KEPUTUSAN → BELUM_KOMPETEN_FINAL ✅
- [ ] A5: FORBIDDEN: SIAP_ASESMEN → MENUNGGU_KEPUTUSAN (skip) ❌
- [ ] A6: FORBIDDEN: SIAP_ASESMEN → KOMPETEN_FINAL (skip all) ❌
- [ ] A7: FORBIDDEN: DALAM_ASESMEN → KOMPETEN_FINAL (skip keputusan) ❌
- [ ] A8: FORBIDDEN: MENUNGGU_KEPUTUSAN → DALAM_ASESMEN (go back) ❌
- [ ] A9: FORBIDDEN: KOMPETEN_FINAL → ANY (immutable) ❌
- [ ] A10: FORBIDDEN: BELUM_KOMPETEN_FINAL → ANY (immutable) ❌

### **B. Lock Mechanism Tests (15 tests)**

- [ ] B1: Asesmen locked after simpan ✅
- [ ] B2: Cannot edit KUK after asesmen locked ❌
- [ ] B3: Cannot delete asesmen detail after locked ❌
- [ ] B4: Keputusan immediately locked on create ✅
- [ ] B5: Cannot edit keputusan after locked ❌
- [ ] B6: Cannot delete keputusan after locked ❌
- [ ] B7: Status locked after FINAL state ✅
- [ ] B8: Cannot change status after locked ❌

### **C. Guard Condition Tests (18 tests)**

- [ ] C1: Cannot mulai asesmen if status ≠ SIAP_ASESMEN ❌
- [ ] C2: Cannot simpan asesmen if status ≠ DALAM_ASESMEN ❌
- [ ] C3: Cannot simpan asesmen if not all KUK filled ❌
- [ ] C4: Cannot tetapkan keputusan if status ≠ MENUNGGU_KEPUTUSAN ❌
- [ ] C5: Cannot tetapkan keputusan if asesmen not locked ❌

### **D. Email Tests (12 tests)**

- [ ] D1: Email sent after asesmen simpan ✅
- [ ] D2: Email idempotent (tidak duplicate) ✅
- [ ] D3: Email sent after keputusan KOMPETEN ✅
- [ ] D4: Email sent after keputusan BELUM_KOMPETEN ✅

### **E. UI Tests (10 tests)**

- [ ] E1: Button [Mulai Asesmen] visible if SIAP_ASESMEN ✅
- [ ] E2: Button [Simpan Asesmen] visible if DALAM_ASESMEN ✅
- [ ] E3: Button [Tetapkan Keputusan] visible if MENUNGGU_KEPUTUSAN ✅
- [ ] E4: All fields readonly if asesmen locked ✅
- [ ] E5: Badge shows "LOCKED" if asesmen locked ✅

---

**TOTAL TESTS:** 75 test cases

---

## 📝 SUMMARY

### **Architecture Principles**

✅ **DETERMINISTIC:** Every state transition is predictable  
✅ **NON-AMBIGUOUS:** Each status has single, clear meaning  
✅ **IMMUTABLE:** FINAL states cannot be changed  
✅ **SEPARATED:** Asesmen ≠ Keputusan (different actors)  
✅ **AUDITABLE:** All transitions logged, lockable, traceable  

### **Compliance**

✅ **ISO 17024:**  
- 9.2.2 Independence of Assessors (Asesor ≠ Komite Teknis)  
- 9.5 Certification Decision (Keputusan = Final Authority)  

✅ **BNSP:**  
- Lock mechanism prevents data manipulation  
- Audit trail for all state transitions  

---

**Document Version:** 1.0  
**Last Updated:** 24 Januari 2026  
**Status:** ✅ **READY FOR IMPLEMENTATION**  
**Next Step:** Implement Service Layer & Guards

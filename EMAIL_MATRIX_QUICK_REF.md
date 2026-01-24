# 📧 EMAIL STATUS MATRIX - QUICK REFERENCE

## ✅ STATUS YANG KIRIM EMAIL (7 Email Total)

| # | Status | Trigger Point | Mailable Class |
|---|--------|---------------|----------------|
| 1 | **PRA_DIAJUKAN** | `PraPendaftaranController::store()` | `PraPendaftaranDibuat` |
| 2 | **PRA_DITERIMA** | `PraPendaftaranAdminController::updateStatus()` | `PraPendaftaranDiterima` |
| 3 | **PRA_DITOLAK** | `PraPendaftaranAdminController::updateStatus()` | `PraPendaftaranDitolak` |
| 4 | **SIAP_ASESMEN** | `PendaftaranSertifikasiAdminController::assignSkema()` | `SertifikasiSkemaDitetapkan` |
| 5 | **ASESMEN_SELESAI** | `AsesmenController::simpanAsesmen()` | `AsesmenSelesai` |
| 6 | **KOMPETEN** | `KeputusanSertifikasiController::simpan()` | `KeputusanKompeten` |
| 7 | **BELUM_KOMPETEN** | `KeputusanSertifikasiController::simpan()` | `KeputusanBelumKompeten` |

---

## ❌ STATUS YANG DILARANG KIRIM EMAIL (3 Status)

| Status | Alasan |
|--------|--------|
| **DIAJUKAN** | Auto-created, user sudah dapat email PRA_DITERIMA |
| **ASESMEN_DIMULAI** | Status internal admin, tidak perlu notifikasi user |
| **MENUNGGU_KEPUTUSAN** | Redundant dengan ASESMEN_SELESAI |

---

## 🔴 ATURAN KERAS

1. **1 Status = Max 1 Email** → Tidak boleh double notification
2. **Email = Notifikasi, Bukan Trigger** → Email tidak create data
3. **CTA = View Only** → DILARANG link create/register baru
4. **Try-Catch Wajib** → Email gagal ≠ transaction rollback
5. **Log Wajib** → Setiap email log ke `laravel.log`
6. **Idempotent Link** → Signed URL, aman diklik berkali-kali
7. **Status Internal = No Email** → Hanya milestone yang kirim

---

## 📋 CODE TEMPLATE

### ✅ BENAR:
```php
// Di Controller setelah status berubah
try {
    Mail::to($user->email)->send(new EmailClass($data));
    Log::info('Email sent', ['status' => 'KOMPETEN', 'user_id' => $user->id]);
} catch (\Exception $e) {
    Log::error('Email failed', ['error' => $e->getMessage()]);
    // JANGAN rollback transaction!
}
```

### ❌ SALAH:
```php
// JANGAN kirim email di status internal
if ($status === 'DIAJUKAN') {
    Mail::send(); // ❌ SPAM!
}

// JANGAN create data dari email
Route::get('/daftar-ulang', function() {
    User::create(); // ❌ DUPLICATE!
});
```

---

**Full Documentation:** `/docs/EMAIL_STATUS_MATRIX.md`  
**Version:** 1.0 | **Date:** 2026-01-24 | **Status:** ✅ PRODUCTION

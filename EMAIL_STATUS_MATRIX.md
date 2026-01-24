# EMAIL STATUS MATRIX - SISTEM SERTIFIKASI LSP
**ISO 17024 Compliant | Production Reference Document**

---

## 🔴 PRINSIP ARSITEKTUR (WAJIB DITAATI)

1. **Email BUKAN Trigger** → Email hanya NOTIFIKASI dari perubahan status yang SUDAH FINAL
2. **1 Status = Max 1 Email** → Tidak boleh kirim email ganda untuk status yang sama
3. **Status Internal = No Email** → Hanya status yang user WAJIB tahu yang kirim email
4. **Idempotent Email** → Aman diklik berkali-kali tanpa create data baru
5. **CTA = VIEW Only** → DILARANG link ke form create/register baru
6. **Anti-Spam** → Maksimal 7 email sepanjang lifecycle (dari 15+ status)
7. **Audit Trail** → Setiap email log ke laravel.log dengan metadata lengkap

---

## 📊 MATRIX STATUS vs EMAIL

### A. PRA-PENDAFTARAN (PUBLIC FLOW)

| No | Status | Email? | Tujuan Email | CTA Boleh | CTA DILARANG | Alasan Teknis |
|----|--------|--------|--------------|-----------|--------------|---------------|
| 1 | **PRA_DIAJUKAN** | ✅ **YA** | Konfirmasi form terkirim, beri nomor tracking | Link: Cek Status (read-only) | DILARANG: Link "Daftar Lagi" | User baru submit, butuh confirmation. Email ini sudah ada di `PraPendaftaranController::store()` |
| 2 | **PRA_DITERIMA** | ✅ **YA** | Informasi verifikasi LOLOS, beri akses ke detail pendaftaran sertifikasi | Link: Lihat Detail Pendaftaran (signed URL, read-only) | DILARANG: Link "Lanjut Daftar" yang create record baru | Status FINAL. User WAJIB tahu sudah approved. Email trigger: `PraPendaftaranAdminController::updateStatus()` |
| 3 | **PRA_DITOLAK** | ✅ **YA** | Informasi verifikasi DITOLAK + alasan admin | Link: Cek Status (read-only) | DILARANG: Auto-retry, form baru | Status FINAL. User WAJIB tahu alasan reject. Sudah ada di `PraPendaftaranDitolak.php` |

**CATATAN:** Pendaftaran Sertifikasi AUTO-CREATED saat PRA_DITERIMA. User TIDAK perlu "daftar lagi".

---

### B. PENDAFTARAN SERTIFIKASI (INTERNAL FLOW)

| No | Status | Email? | Tujuan Email | CTA Boleh | CTA DILARANG | Alasan Teknis |
|----|--------|--------|--------------|-----------|--------------|---------------|
| 4 | **DIAJUKAN** | ❌ **TIDAK** | - | - | - | Status internal. Auto-created dari PRA_DITERIMA. User sudah dapat email di step #2. **SPAM jika kirim lagi.** |
| 5 | **SIAP_ASESMEN** | ✅ **YA** | Informasi skema DITETAPKAN, siap proses asesmen | Link: Login Dashboard (view progress) | DILARANG: Link pilih skema lagi | Status MILESTONE. User WAJIB tahu skema sudah fixed. Email trigger: `PendaftaranSertifikasiAdminController::assignSkema()` |
| 6 | **DIBATALKAN** | ⚠️ **OPSIONAL** | Informasi pendaftaran dibatalkan oleh admin/user | Link: Hubungi Admin | DILARANG: Link daftar ulang otomatis | Status RARE. Manual notification via admin jika perlu. Tidak prioritas. |

---

### C. ASESMEN (ASSESSMENT FLOW)

| No | Status | Email? | Tujuan Email | CTA Boleh | CTA DILARANG | Alasan Teknis |
|----|--------|--------|--------------|-----------|--------------|---------------|
| 7 | **ASESMEN_DIMULAI** | ❌ **TIDAK** | - | - | - | Status internal admin. User tidak perlu tahu asesmen "dimulai". **Hanya butuh tahu SELESAI.** |
| 8 | **ASESMEN_SELESAI** | ✅ **YA** | Informasi asesmen SELESAI, masuk tahap review Komite Teknis | Link: Login Dashboard (view status) | DILARANG: Link submit bukti lagi | Status MILESTONE. User WAJIB tahu asesmen sudah done. Email trigger: `AsesmenController::simpanAsesmen()` |
| 9 | **MENUNGGU_KEPUTUSAN** | ❌ **TIDAK** | - | - | - | Status internal. User SUDAH dapat email di step #8 (ASESMEN_SELESAI). **SPAM jika kirim lagi dengan pesan sama.** |

**ALASAN KERAS:** Status 8 & 9 hampir bersamaan. Email ASESMEN_SELESAI sudah mention "menunggu keputusan". DILARANG double notification.

---

### D. KEPUTUSAN SERTIFIKASI (DECISION FLOW)

| No | Status | Email? | Tujuan Email | CTA Boleh | CTA DILARANG | Alasan Teknis |
|----|--------|--------|--------------|-----------|--------------|---------------|
| 10 | **KOMPETEN** | ✅ **YA** | 🎉 Selamat LULUS! Informasi sertifikat akan diterbitkan | Link: Login Dashboard (tunggu sertifikat) | DILARANG: Link cetak sertifikat (belum terbit) | Status FINAL POSITIF. User WAJIB tahu lulus. Email trigger: `KeputusanSertifikasiController::simpan()` |
| 11 | **BELUM_KOMPETEN** | ✅ **YA** | Informasi TIDAK LULUS + catatan asesor + langkah asesmen ulang | Link: Hubungi Admin (untuk jadwal ulang) | DILARANG: Link daftar ulang otomatis | Status FINAL NEGATIF. User WAJIB tahu tidak lulus + feedback. Email trigger: `KeputusanSertifikasiController::simpan()` |

---

### E. SERTIFIKAT (CERTIFICATE ISSUANCE)

| No | Status | Email? | Tujuan Email | CTA Boleh | CTA DILARANG | Alasan Teknis |
|----|--------|--------|--------------|-----------|--------------|---------------|
| 12 | **SERTIFIKAT_TERBIT** | ✅ **YA** | Informasi sertifikat SIAP DIUNDUH + link download PDF | Link: Download Sertifikat (signed URL, PDF) | DILARANG: Link verifikasi yang create log tracking | Status FINAL. User WAJIB tahu sertifikat ready. Email trigger: `SertifikatController::terbitkan()` |

---

## 🚫 STATUS YANG DILARANG KERAS MENGIRIM EMAIL

### Daftar BLACKLIST Status (Total: 3 Status)

| Status | Alasan DILARANG |
|--------|-----------------|
| **DIAJUKAN** | Auto-created, user sudah dapat email PRA_DITERIMA. SPAM jika kirim lagi. |
| **ASESMEN_DIMULAI** | Status internal admin. User hanya butuh tahu SELESAI, bukan DIMULAI. |
| **MENUNGGU_KEPUTUSAN** | Redundant dengan ASESMEN_SELESAI. Double notification = SPAM. |

### Alasan Bisnis:
- User TIDAK PEDULI proses internal admin
- User hanya butuh tahu: "Sudah selesai?" atau "Hasilnya apa?"
- Mengirim email status intermediate = noise & spam

---

## 📧 RINGKASAN EMAIL LIFECYCLE

**Total Email yang Dikirim:** **7 Email** (dari 12 status)

### Timeline Email:
1. **Hari 1:** PRA_DIAJUKAN (konfirmasi submit)
2. **Hari 1-3:** PRA_DITERIMA (approved + akun created) atau PRA_DITOLAK (rejected)
3. **Hari 3-7:** SIAP_ASESMEN (skema ditetapkan)
4. **Hari 7-14:** ASESMEN_SELESAI (asesmen done, tunggu review)
5. **Hari 14-21:** KOMPETEN (lulus) atau BELUM_KOMPETEN (tidak lulus)
6. **Hari 21-30:** SERTIFIKAT_TERBIT (sertifikat ready)

**Prinsip:** User dapat email MAX 1x per minggu, cukup untuk stay informed tanpa spam.

---

## 🛡️ ANTI-PATTERN (DILARANG DI PRODUCTION)

### ❌ JANGAN LAKUKAN INI:

1. **Kirim Email Saat Status Internal Berubah**
   ```php
   // ❌ SALAH - Status internal
   if ($status === 'MENUNGGU_KEPUTUSAN') {
       Mail::send(); // SPAM!
   }
   ```

2. **CTA yang Create Data Baru**
   ```html
   <!-- ❌ SALAH - Bisa create duplicate -->
   <a href="/daftar-ulang">Daftar Lagi</a>
   ```

3. **Email Tanpa Idempotent Guard**
   ```php
   // ❌ SALAH - Bisa kirim berkali-kali
   Mail::to($user)->send(); // No check!
   ```

4. **Double Notification untuk Status Berurutan**
   ```php
   // ❌ SALAH - ASESMEN_SELESAI dan MENUNGGU_KEPUTUSAN kirim 2 email
   Mail::send('Asesmen Selesai');
   Mail::send('Menunggu Keputusan'); // SPAM!
   ```

### ✅ LAKUKAN INI:

1. **Email Hanya di Status FINAL/MILESTONE**
   ```php
   // ✅ BENAR
   if ($status === 'KOMPETEN') { // Status FINAL
       Mail::to($user->email)->send(new KeputusanKompeten($asesmen));
       Log::info('Email KOMPETEN sent', ['user_id' => $user->id]);
   }
   ```

2. **CTA View-Only dengan Signed URL**
   ```html
   <!-- ✅ BENAR - Read-only, idempotent -->
   <a href="{{ signedUrl('/pendaftaran/{id}/detail') }}">Lihat Detail</a>
   ```

3. **Email dengan Try-Catch & Log**
   ```php
   // ✅ BENAR
   try {
       Mail::to($user->email)->send(new EmailClass($data));
       Log::info('Email sent', ['type' => 'KOMPETEN', 'user_id' => $user->id]);
   } catch (\Exception $e) {
       Log::error('Email failed', ['error' => $e->getMessage()]);
       // JANGAN rollback transaction
   }
   ```

4. **One Email Per Status Change**
   ```php
   // ✅ BENAR - Hanya 1 email saat transisi
   $oldStatus = $model->status;
   $model->update(['status' => 'KOMPETEN']);
   
   if ($oldStatus !== 'KOMPETEN' && $model->status === 'KOMPETEN') {
       Mail::send(); // Kirim 1x saja
   }
   ```

---

## 🔍 AUDIT & COMPLIANCE

### ISO 17024 Requirements:
- ✅ Notification at key milestones (approval, decision, certificate)
- ✅ Clear communication of results
- ✅ Audit trail of all notifications
- ✅ No ambiguous process steps

### Data Integrity:
- ✅ Email TIDAK create data (hanya notifikasi)
- ✅ Signed URL mencegah unauthorized access
- ✅ Idempotent link (aman diklik berkali-kali)
- ✅ No double registration possible

### User Experience:
- ✅ Clear next steps di setiap email
- ✅ Tidak spam (max 7 email sepanjang lifecycle)
- ✅ Konsisten (1 status = 1 email)
- ✅ No confusion (status internal tidak kirim email)

---

## 📝 IMPLEMENTATION CHECKLIST

### Backend Developer:
- [ ] Pastikan HANYA 7 status yang trigger email
- [ ] Setiap email harus log ke `laravel.log`
- [ ] Try-catch semua `Mail::send()` (jangan rollback transaction)
- [ ] Test idempotent (klik link berkali-kali = aman)
- [ ] Verify no CTA yang create data baru

### QA Tester:
- [ ] Test 1 user dari PRA_DIAJUKAN sampai SERTIFIKAT_TERBIT
- [ ] Hitung total email diterima (harus = 6 atau 7, tergantung jalur)
- [ ] Klik semua link di email berkali-kali (harus tidak ada duplicate data)
- [ ] Verify email TIDAK terkirim di status internal (DIAJUKAN, ASESMEN_DIMULAI, MENUNGGU_KEPUTUSAN)
- [ ] Check log: setiap email harus ada entry di `laravel.log`

### DevOps:
- [ ] Setup email queue (Laravel Queue) untuk production
- [ ] Monitor failed jobs (`php artisan queue:failed`)
- [ ] Setup alert jika email bounce rate > 5%
- [ ] Backup email templates di version control

---

## 🚀 DEPLOYMENT NOTES

**File Locations:**
- Mailable Classes: `app/Mail/*.php`
- Email Views: `resources/views/emails/**/*.blade.php`
- Controllers: `app/Http/Controllers/AdminUI/*.php`
- Logs: `storage/logs/laravel.log`

**Environment Variables Required:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@lsp-ui.ibnuapps.cloud
MAIL_FROM_NAME="${APP_NAME}"
```

**Monitoring Commands:**
```bash
# Check email logs
tail -f storage/logs/laravel.log | grep "Email"

# Check failed email jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

---

**Document Version:** 1.0  
**Last Updated:** 2026-01-24  
**Approved By:** Senior Architect  
**Status:** ✅ PRODUCTION-READY  

---

## ⚠️ PERINGATAN UNTUK DEVELOPER

**JANGAN PERNAH:**
1. Tambah email tanpa update matrix ini
2. Kirim email di status yang tidak ada di matrix
3. Buat CTA yang mengarah ke form create baru
4. Skip logging saat kirim email
5. Anggap email gagal = transaction gagal (JANGAN rollback!)

**Konsekuensi Melanggar:**
- User confusion (daftar berkali-kali)
- Data duplicate (integritas rusak)
- Spam (user unsubscribe)
- Audit trail broken (compliance gagal)

**Jika Ragu:** Tanya Senior Architect. Jangan asal kirim email.

---

**END OF DOCUMENT**

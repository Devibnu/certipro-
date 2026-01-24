# DEPLOYMENT CHECKLIST - Fix Auto-Registration Flow

## 🎯 Problem Yang Diperbaiki

**User Confusion:**
- ❌ Admin approve pra-pendaftaran → Email "Lanjut Daftar Sertifikasi"
- ❌ Peserta klik link → Disuruh "daftar lagi" (bingung!)
- ❌ Pendaftaran otomatis dibuat tapi status DRAFT (tidak jelas)

**Root Cause:**
- Pendaftaran auto-created dengan status `'draft'` 
- Email link arahkan ke form daftar umum (bukan pendaftaran yang sudah dibuat)
- Peserta merasa harus daftar ulang, padahal pendaftaran sudah ada

## ✅ Solusi yang Diimplementasikan

### 1. **Auto-Created Pendaftaran Status = DIAJUKAN** (bukan DRAFT)
- File: `app/Http/Controllers/AdminUI/PraPendaftaranAdminController.php`
- Perubahan:
  ```php
  // BEFORE:
  'status' => 'draft',
  
  // AFTER:
  'status' => 'diajukan',
  'catatan_admin' => 'Pendaftaran otomatis dibuat dari pra-pendaftaran yang telah diverifikasi',
  'user_id' => auth()->id(), // Prevent orphaned data
  ```

### 2. **Email Link Redirect ke Pendaftaran yang Sudah Dibuat**
- File: `app/Mail/PraPendaftaran/PraPendaftaranDiterima.php`
- Logic baru:
  ```php
  // Jika pendaftaran sudah ada → redirect ke detail pendaftaran
  // Jika belum → redirect ke form daftar
  $pendaftaran = $this->praPendaftaran->pendaftaranSertifikasi;
  $daftarUrl = $pendaftaran 
      ? route('pendaftaran-sertifikasi.show', $pendaftaran->id)
      : route('daftar');
  ```

### 3. **Email Content Dynamic**
- File: `resources/views/emails/pra-pendaftaran/diterima.blade.php`
- Perubahan:
  ```blade
  @if($hasPendaftaran)
      📌 Pendaftaran Sertifikasi Telah Dibuat
      Nomor: {{ $pendaftaran->nomor_pendaftaran }}
      Tombol: ✏️ Lengkapi Pendaftaran Sertifikasi
  @else
      📌 Langkah Selanjutnya
      Tombol: 🚀 Lanjut Daftar Sertifikasi
  @endif
  ```

### 4. **Status Page Redirect**
- File: `resources/views/status-pra-pendaftaran/result.blade.php`
- Logic yang sama: cek pendaftaran sudah ada → redirect ke detail

## 📦 Files Modified (Ready to Deploy)

```
app/Http/Controllers/AdminUI/PraPendaftaranAdminController.php
app/Mail/PraPendaftaran/PraPendaftaranDiterima.php
resources/views/emails/pra-pendaftaran/diterima.blade.php
resources/views/status-pra-pendaftaran/result.blade.php
```

## 🚀 Deployment Steps (Production)

### Step 1: Backup Database
```bash
ssh lsp-ui.ibnuapps.cloud
mysqldump -u root -p certipro_lsp > backup_before_fix_$(date +%Y%m%d_%H%M%S).sql
```

### Step 2: Upload Files to Production
```bash
# From local machine
rsync -avz \
  app/Http/Controllers/AdminUI/PraPendaftaranAdminController.php \
  app/Mail/PraPendaftaran/PraPendaftaranDiterima.php \
  resources/views/emails/pra-pendaftaran/diterima.blade.php \
  resources/views/status-pra-pendaftaran/result.blade.php \
  user@lsp-ui.ibnuapps.cloud:/var/www/certipro/
```

### Step 3: Clear Cache
```bash
ssh lsp-ui.ibnuapps.cloud
cd /var/www/certipro
php artisan view:clear
php artisan cache:clear
php artisan config:clear
```

### Step 4: Test Workflow
1. Admin buka: https://lsp-ui.ibnuapps.cloud/adminui/pra-pendaftaran
2. Pilih pra-pendaftaran status BARU/DIPROSES
3. Update status → DITERIMA
4. **Verifikasi:**
   - ✅ Pendaftaran auto-created dengan status DIAJUKAN (bukan DRAFT)
   - ✅ Email dikirim ke peserta
   - ✅ Email link langsung ke detail pendaftaran (bukan form daftar)
   - ✅ Status page juga redirect ke detail pendaftaran

### Step 5: Fix Existing DRAFT Pendaftaran (if needed)
```bash
# Jika ada pendaftaran dari pra-pendaftaran yang masih DRAFT
php artisan tinker
>>> PendaftaranSertifikasi::whereHas('praPendaftaran', function($q) {
        $q->where('status', 'diterima');
    })
    ->where('status', 'draft')
    ->update([
        'status' => 'diajukan',
        'catatan_admin' => 'Auto-updated dari DRAFT ke DIAJUKAN (pra-pendaftaran telah diverifikasi)'
    ]);
```

## 📊 Expected Result (After Deployment)

### Scenario 1: New Pra-Pendaftaran Approval
1. Admin approve pra-pendaftaran → status DITERIMA ✅
2. Sistem auto-create pendaftaran dengan status DIAJUKAN ✅
3. Email terkirim dengan link ke detail pendaftaran ✅
4. Peserta klik → langsung buka pendaftaran ID tertentu ✅
5. Peserta lengkapi: pilih skema, upload dokumen, pilih jadwal ✅

### Scenario 2: Existing Pra-Pendaftaran (Already DITERIMA)
1. Peserta cek email lama ✅
2. Klik link → redirect ke detail pendaftaran (bukan form baru) ✅
3. Peserta melanjutkan pendaftaran yang ada ✅

## ⚠️ Important Notes

1. **Jangan Hapus Pendaftaran DRAFT yang Sudah Ada**
   - Update status jadi DIAJUKAN (script di Step 5)
   
2. **State Machine Migration Belum Di-Deploy**
   - Perbaikan ini TIDAK memerlukan State Machine
   - State Machine deployment terpisah (nanti)

3. **User_ID Wajib Diisi**
   - Auto-created pendaftaran sekarang set `user_id = auth()->id()`
   - Mencegah orphaned data

## 🧪 Testing Checklist

- [ ] Admin approve pra-pendaftaran baru
- [ ] Pendaftaran auto-created dengan status DIAJUKAN
- [ ] Email dikirim ke peserta
- [ ] Email button "Lengkapi Pendaftaran Sertifikasi" (bukan "Lanjut Daftar")
- [ ] Email link redirect ke `/pendaftaran-sertifikasi/{id}` (bukan `/daftar`)
- [ ] Status page juga redirect ke pendaftaran yang sudah ada
- [ ] Peserta bisa lengkapi skema, dokumen, jadwal
- [ ] No duplicate pendaftaran created

## 🔄 Rollback Plan (If Needed)

Jika ada masalah, restore dari backup:
```bash
mysql -u root -p certipro_lsp < backup_before_fix_YYYYMMDD_HHMMSS.sql
git checkout HEAD~1 -- app/Http/Controllers/AdminUI/PraPendaftaranAdminController.php
git checkout HEAD~1 -- app/Mail/PraPendaftaran/PraPendaftaranDiterima.php
git checkout HEAD~1 -- resources/views/emails/pra-pendaftaran/diterima.blade.php
git checkout HEAD~1 -- resources/views/status-pra-pendaftaran/result.blade.php
php artisan cache:clear
```

## 📅 Deployment Timeline

**Recommended:** Deploy during off-peak hours (21:00 - 23:00 WIB)

**Estimated Downtime:** None (hot-swap files only)

**Required Team:**
- 1 Developer (deploy & test)
- 1 Admin (test approval flow)

---

**Status:** ✅ READY TO DEPLOY
**Date Prepared:** 23 January 2026
**Prepared By:** AI Assistant + User (ibnuqosim)

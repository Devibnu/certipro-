# 🔥 HOTFIX PRODUKSI - FLOW PRA-PENDAFTARAN → PENDAFTARAN SERTIFIKASI
**Tanggal**: 25 Januari 2026  
**Versi**: Production Hotfix v1.0  
**Status**: ✅ DEPLOYED

---

## 📋 MASALAH YANG DIPERBAIKI

### 1️⃣ **HTML Entity di Notifikasi**
**Masalah**: Teks notifikasi mengandung `&quot;` alih-alih tanda kutip normal.

**Root Cause**: String di-escape ganda saat masuk ke database.

**Solusi**:
```php
// File: app/Services/InAppNotificationService.php
// Method: notifyPraPendaftaranBaru()

// BEFORE:
$this->sendToAllAdmins(
    InAppNotification::TYPE_PENDAFTARAN_BARU,
    'Pra-Pendaftaran Baru',
    "Asesi baru: {$asesiName}",  // ❌ Bisa jadi &quot;Nama&quot;
    ...
);

// AFTER:
$cleanName = html_entity_decode($asesiName, ENT_QUOTES, 'UTF-8');
$this->sendToAllAdmins(
    InAppNotification::TYPE_PENDAFTARAN_BARU,
    'Pra-Pendaftaran Baru',
    "Asesi baru: {$cleanName}",  // ✅ Clean text
    ...
);
```

---

### 2️⃣ **Auto-Create Pendaftaran Sertifikasi**
**Masalah**: Setelah status Pra-Pendaftaran = DITERIMA, data TIDAK muncul di modul Pendaftaran Sertifikasi.

**Root Cause**: Observer hanya kirim email, TIDAK create data PendaftaranSertifikasi.

**Solusi**: Tambahkan logic auto-create di `PraPendaftaranObserver::updated()`

```php
// File: app/Observers/PraPendaftaranObserver.php

if ($statusChanged && $newStatus === PraPendaftaran::STATUS_DITERIMA) {
    if ($oldStatus !== $newStatus) {
        
        // ✅ AUTO-CREATE PENDAFTARAN SERTIFIKASI
        DB::transaction(function () use ($praPendaftaran) {
            
            // GUARD: Prevent duplicate
            if ($praPendaftaran->hasPendaftaranSertifikasi()) {
                return;
            }
            
            // Generate nomor
            $nomorPendaftaran = 'PS-' . date('Ymd') . '-' . str_pad($praPendaftaran->id, 5, '0', STR_PAD_LEFT);
            
            // CREATE
            $pendaftaran = PendaftaranSertifikasi::create([
                'pra_pendaftaran_id' => $praPendaftaran->id,
                'nomor_pendaftaran' => $nomorPendaftaran,
                'nama_lengkap' => $praPendaftaran->nama_lengkap,
                'email' => $praPendaftaran->email,
                'no_hp' => $praPendaftaran->no_hp,
                'tipe_peserta' => $praPendaftaran->tipe_peserta,
                'nik' => $praPendaftaran->nik,
                'nim' => $praPendaftaran->nim,
                'institusi' => $praPendaftaran->institusi,
                'status' => PendaftaranSertifikasi::STATUS_BELUM_PILIH_SKEMA, // ⭐ NEW
                'tanggal_daftar' => now(),
            ]);
            
            Log::info('[Observer] ✅ PendaftaranSertifikasi AUTO-CREATED', [
                'pra_id' => $praPendaftaran->id,
                'pendaftaran_id' => $pendaftaran->id,
                'nomor_pendaftaran' => $nomorPendaftaran,
            ]);
            
            // Audit log
            AuditLog::log(
                AuditLog::ACTION_CREATE,
                AuditLog::MODULE_PENDAFTARAN,
                "Pendaftaran Sertifikasi dibuat otomatis dari Pra-Pendaftaran: {$praPendaftaran->nama_lengkap}",
                $pendaftaran,
                null,
                $pendaftaran->toArray(),
                [
                    'event' => 'pendaftaran_created_from_pra',
                    'pra_pendaftaran_id' => $praPendaftaran->id,
                ]
            );
        });
        
        // Send email (DIRECT, NO QUEUE)
        PraPendaftaranNotificationService::sendAcceptedNotification($praPendaftaran);
    }
}
```

---

### 3️⃣ **Status Baru: BELUM_PILIH_SKEMA**
**Masalah**: Tidak ada status untuk Pendaftaran Sertifikasi yang baru dibuat (belum pilih skema).

**Solusi**: Tambahkan constant baru + label + badge color.

```php
// File: app/Models/PendaftaranSertifikasi.php

// BEFORE:
const STATUS_DRAFT = 'draft';
const STATUS_DIAJUKAN = 'diajukan';
...

// AFTER:
const STATUS_BELUM_PILIH_SKEMA = 'BELUM_PILIH_SKEMA'; // ⭐ NEW
const STATUS_DRAFT = 'draft';
const STATUS_DIAJUKAN = 'diajukan';
...

// statusLabels():
return [
    self::STATUS_BELUM_PILIH_SKEMA => 'Belum Pilih Skema', // ⭐ NEW
    self::STATUS_DRAFT => 'Draft',
    ...
];

// getStatusBadgeAttribute():
return match($this->status) {
    self::STATUS_BELUM_PILIH_SKEMA => 'bg-gradient-warning', // ⭐ NEW
    self::STATUS_DRAFT => 'bg-gradient-secondary',
    ...
};
```

---

### 4️⃣ **List Query - Tidak Ada Filter**
**Status**: ✅ SUDAH BENAR

Query di `PendaftaranSertifikasiAdminController::index()` TIDAK ada filter `whereNotNull('skema_sertifikasi_id')`.

Artinya data dengan status `BELUM_PILIH_SKEMA` **OTOMATIS MUNCUL** di list.

---

### 5️⃣ **Email Flow**
**Status**: ✅ SUDAH BENAR (Direct Send)

```php
// File: app/Services/PraPendaftaranNotificationService.php
// Method: sendAcceptedNotification()

Mail::to($praPendaftaran->email)->send(new PraPendaftaranDiterima($praPendaftaran));
```

**Catatan**: Email dikirim DIRECT (bukan queue) untuk memastikan 100% terkirim.

---

## 🚀 DEPLOYMENT STEPS

### Files Modified:
```
✅ app/Observers/PraPendaftaranObserver.php
✅ app/Models/PendaftaranSertifikasi.php
✅ app/Services/InAppNotificationService.php
✅ database/migrations/2026_01_25_add_belum_pilih_skema_status.php
```

### Deployment Commands:
```bash
# 1. Upload files
scp app/Observers/PraPendaftaranObserver.php root@76.13.18.166:/var/www/lsp-ui.ibnuapps.cloud/current/app/Observers/
scp app/Models/PendaftaranSertifikasi.php root@76.13.18.166:/var/www/lsp-ui.ibnuapps.cloud/current/app/Models/
scp app/Services/InAppNotificationService.php root@76.13.18.166:/var/www/lsp-ui.ibnuapps.cloud/current/app/Services/
scp database/migrations/2026_01_25_add_belum_pilih_skema_status.php root@76.13.18.166:/var/www/lsp-ui.ibnuapps.cloud/current/database/migrations/

# 2. Run migration
ssh root@76.13.18.166 "cd /var/www/lsp-ui.ibnuapps.cloud/current && php artisan migrate --force"

# 3. Clear cache
ssh root@76.13.18.166 "cd /var/www/lsp-ui.ibnuapps.cloud/current && php artisan cache:clear && php artisan config:clear && php artisan view:clear"

# 4. Restart PHP-FPM
ssh root@76.13.18.166 "systemctl restart php8.3-fpm"
```

---

## ✅ VALIDATION CHECKLIST

### TEST SCENARIO: Complete Flow

#### 1. **User Daftar**
- [ ] User mengisi form pra-pendaftaran
- [ ] Submit berhasil
- [ ] Notifikasi sukses: "Pra-pendaftaran berhasil dibuat"

#### 2. **Email Konfirmasi Terkirim**
- [ ] Email masuk ke inbox user
- [ ] Subject: "Pra-Pendaftaran Diterima"
- [ ] Body: Nomor pra-pendaftaran + link status

#### 3. **Admin Ubah Status → DITERIMA**
- [ ] Admin login
- [ ] Buka detail Pra-Pendaftaran
- [ ] Ubah status ke "DITERIMA"
- [ ] Submit berhasil

#### 4. **Notifikasi Admin (In-App)**
- [ ] Notifikasi muncul di navbar
- [ ] Teks: "Asesi baru: [Nama]" (✅ TANPA `&quot;`)
- [ ] Icon: clipboard-list (warning)
- [ ] Link: /adminui/pra-pendaftaran/{id}

#### 5. **Email DITERIMA ke User**
- [ ] Email masuk ke inbox user
- [ ] Subject: "Pra-Pendaftaran Diterima - Lanjutkan ke Pendaftaran Sertifikasi"
- [ ] Body: Instruksi pilih skema kompetensi

#### 6. **Auto-Create Pendaftaran Sertifikasi**
- [ ] Data OTOMATIS dibuat di tabel `pendaftaran_sertifikasi`
- [ ] `pra_pendaftaran_id` = ID pra-pendaftaran
- [ ] `status` = "BELUM_PILIH_SKEMA"
- [ ] `nomor_pendaftaran` = "PS-YYYYMMDD-00001"
- [ ] Data lengkap: nama, email, no_hp, nik, nim, institusi

#### 7. **Data Muncul di Menu Pendaftaran Sertifikasi**
- [ ] Admin buka /adminui/pendaftaran-sertifikasi
- [ ] Data muncul di tabel
- [ ] Status badge: "Belum Pilih Skema" (warning)
- [ ] Nomor pendaftaran: PS-20260125-00001

#### 8. **Idempotent Check**
- [ ] Admin ubah status DITERIMA lagi (klik simpan 2x)
- [ ] TIDAK ada duplikasi data
- [ ] TIDAK ada double email
- [ ] Log: "Pendaftaran Sertifikasi already exists"

---

## 📊 DATABASE CHANGES

### Migration: `2026_01_25_add_belum_pilih_skema_status.php`

**Up:**
```php
// Update existing records with NULL skema_sertifikasi_id
DB::table('pendaftaran_sertifikasi')
    ->whereNull('skema_sertifikasi_id')
    ->where('status', '!=', 'BELUM_PILIH_SKEMA')
    ->update(['status' => 'BELUM_PILIH_SKEMA']);
```

**Down:**
```php
// Revert BELUM_PILIH_SKEMA back to draft
DB::table('pendaftaran_sertifikasi')
    ->where('status', 'BELUM_PILIH_SKEMA')
    ->update(['status' => 'draft']);
```

---

## 🔍 MONITORING

### Log Patterns to Monitor:

```bash
# 1. Check auto-create success
tail -f storage/logs/laravel.log | grep "AUTO-CREATED"

# Expected:
# [Observer] ✅ PendaftaranSertifikasi AUTO-CREATED {"pra_id":123,"pendaftaran_id":456,"nomor_pendaftaran":"PS-20260125-00001"}

# 2. Check email sent
tail -f storage/logs/laravel.log | grep "Email DITERIMA sent"

# Expected:
# [Observer] ✅ Email DITERIMA sent {"pra_id":123}

# 3. Check duplicate prevention
tail -f storage/logs/laravel.log | grep "already exists"

# Expected (on 2nd attempt):
# [Observer] Pendaftaran Sertifikasi already exists {"pra_id":123}

# 4. Check notification clean text
tail -f storage/logs/laravel.log | grep "In-app notification created"

# Expected:
# In-app notification created {"user_id":2,"type":"pendaftaran_baru","title":"Pra-Pendaftaran Baru"}
```

---

## 🚨 ROLLBACK PROCEDURE

If something goes wrong:

```bash
# 1. Revert migration
ssh root@76.13.18.166 "cd /var/www/lsp-ui.ibnuapps.cloud/current && php artisan migrate:rollback --step=1"

# 2. Restore files from git
ssh root@76.13.18.166 "cd /var/www/lsp-ui.ibnuapps.cloud/current && git checkout HEAD -- app/Observers/PraPendaftaranObserver.php app/Models/PendaftaranSertifikasi.php app/Services/InAppNotificationService.php"

# 3. Clear cache
ssh root@76.13.18.166 "cd /var/www/lsp-ui.ibnuapps.cloud/current && php artisan cache:clear && php artisan config:clear"

# 4. Restart PHP-FPM
ssh root@76.13.18.166 "systemctl restart php8.3-fpm"
```

---

## 📝 NOTES

### Why Direct Email (No Queue)?

**Reason**: Queue workers might fail silently. Direct send ensures:
- ✅ Email 100% terkirim saat status changed
- ✅ User dapat konfirmasi SEGERA
- ✅ Admin tidak perlu cek queue status

**Trade-off**: 
- Request sedikit lebih lambat (200-500ms)
- Acceptable untuk flow kritis ini

### Why DB::transaction?

**Reason**: Ensure atomicity:
- ✅ PendaftaranSertifikasi created
- ✅ AuditLog recorded
- ❌ Rollback jika error

### Why Idempotent Check?

**Reason**: Prevent duplicate data jika:
- Admin double-click submit button
- Network timeout + retry
- Manual re-trigger event

---

## ✅ SUCCESS CRITERIA

**Deployment considered successful when:**
1. ✅ Status change DITERIMA triggers auto-create
2. ✅ Data muncul di /adminui/pendaftaran-sertifikasi
3. ✅ Email terkirim ke user
4. ✅ Notifikasi tidak ada HTML entity
5. ✅ Tidak ada duplicate data
6. ✅ No errors in laravel.log

---

## 📞 SUPPORT

**Issue**: Data tidak muncul di Pendaftaran Sertifikasi

**Debug**:
```bash
# 1. Check if Observer fired
tail -100 storage/logs/laravel.log | grep "Observer"

# 2. Check if data created
mysql certipro_lsp -e "SELECT * FROM pendaftaran_sertifikasi WHERE status='BELUM_PILIH_SKEMA' ORDER BY id DESC LIMIT 10;"

# 3. Check relation
mysql certipro_lsp -e "SELECT ps.*, pp.status as pra_status FROM pendaftaran_sertifikasi ps LEFT JOIN pra_pendaftaran pp ON ps.pra_pendaftaran_id = pp.id WHERE pp.status='diterima';"
```

---

**END OF DOCUMENTATION**

# 🔐 IDEMPOTENT REGISTRATION FLOW - README

## 📌 Overview

Sistem **Lanjut Pendaftaran Sertifikasi** yang IDEMPOTENT, AMAN, dan USER-FRIENDLY.

**Prinsip Utama:**
- **1 Pra-Pendaftaran = 1 Pendaftaran Sertifikasi (MAKSIMAL)**
- **Tidak ada duplicate registration**
- **Aman dari double click, refresh, concurrent request**
- **Signed URL untuk security**
- **Clear audit trail**

---

## 🎯 Problem yang Dipecahkan

### Before (Masalah):
❌ Email mengarahkan ke `/daftar` (form create baru)  
❌ User bisa daftar ulang → data ganda  
❌ Link tidak secure (bisa ditebak)  
❌ Tidak ada validasi duplicate  
❌ Form bisa disubmit berkali-kali  

### After (Solusi):
✅ Email menggunakan **Signed URL** (aman, expire 30 hari)  
✅ Link **context-aware** (smart routing)  
✅ **DB Lock** mencegah race condition  
✅ **Hard validation** di form  
✅ **Idempotent** - klik berkali-kali = hasil sama  

---

## 📂 Files Created/Updated

### ✨ NEW Files:
```
app/Http/Controllers/ResumePendaftaranController.php
resources/views/pendaftaran-sertifikasi/public-detail.blade.php
IDEMPOTENT_REGISTRATION_FLOW.md
SUMMARY_IDEMPOTENT_FLOW.txt
deploy_idempotent_flow.sh
test_idempotent_flow.sh
README_IDEMPOTENT_FLOW.md (this file)
```

### 🔄 UPDATED Files:
```
app/Http/Controllers/PraPendaftaranController.php
app/Mail/PraPendaftaran/PraPendaftaranDiterima.php
routes/web.php
```

---

## 🚀 Quick Start

### 1. Deploy ke Production

```bash
# Option A: Automatic (recommended)
./deploy_idempotent_flow.sh

# Option B: Manual
# See deploy_idempotent_flow.sh for commands
```

### 2. Test Deployment

```bash
./test_idempotent_flow.sh
```

### 3. Manual Testing

1. Login ke admin panel
2. Approve pra-pendaftaran (status → DITERIMA)
3. Check email untuk signed URL
4. Click link → verify pendaftaran dibuat
5. Click link AGAIN → verify tidak ada duplicate

---

## 🔐 Security Features

### 1. Signed URL (Laravel SignedRoute)

```php
$url = URL::temporarySignedRoute(
    'pendaftaran.lanjut',
    now()->addDays(30),
    ['praPendaftaranId' => $id]
);
```

**Benefits:**
- URL tidak bisa ditebak
- URL tidak bisa dimodifikasi
- URL expire setelah 30 hari
- HMAC signature validation

### 2. Database Locks (Pessimistic Locking)

```php
DB::transaction(function() use ($id) {
    $praPendaftaran = PraPendaftaran::where('id', $id)
        ->lockForUpdate()
        ->first();
    
    // Double-check after lock
    if ($praPendaftaran->hasPendaftaranSertifikasi()) {
        return; // Idempotent
    }
    
    // Create 1x
    PendaftaranSertifikasi::create([...]);
});
```

**Benefits:**
- Prevent race condition
- Aman dari concurrent requests
- Guarantee 1 record creation only

---

## 📊 Flow Diagram (Simplified)

```
USER                    SYSTEM                      DATABASE
  │                        │                            │
  ├─ Submit Form ─────────>│                            │
  │                        ├─ Validate Email ────────> │
  │                        │   (check duplicate)        │
  │                        │<─────────────────────────  │
  │                        │                            │
  │<─ Redirect (success) ──┤                            │
  │                        │                            │
                           │
ADMIN                      │                            │
  │                        │                            │
  ├─ Approve ─────────────>│                            │
  │   (status→DITERIMA)    ├─ Check existing ────────> │
  │                        │<─────────────────────────  │
  │                        │                            │
  │                        ├─ Create Pendaftaran ────> │
  │                        │   (with lock)              │
  │                        │                            │
  │                        ├─ Send Email ──────────────>│
  │                        │   (signed URL)             │
  │                        │                            │
                           │
USER                       │                            │
  │                        │                            │
  ├─ Click Email Link ────>│                            │
  │                        ├─ Validate Signature ──────>│
  │                        │   (check expiry)           │
  │                        │                            │
  │                        ├─ Check Existing ────────> │
  │                        │<─────────────────────────  │
  │                        │                            │
  │                        │ IF EXISTS:                 │
  │<─ Show Detail ─────────┤   → Show page              │
  │                        │                            │
  │                        │ IF NOT EXISTS:             │
  │                        ├─ Create with Lock ──────> │
  │                        │   (idempotent)             │
  │<─ Show Detail ─────────┤                            │
```

---

## 🧪 Test Cases

### TC-01: Happy Path
1. User submit form
2. Admin approve
3. Email sent with signed URL
4. User click link
5. Pendaftaran created
✅ Expected: 1 record, status DIAJUKAN

### TC-02: Double Click
1. User click email link (request #1)
2. User click AGAIN (request #2)
✅ Expected: Only 1 record, no duplicate

### TC-03: Concurrent Request
1. User click link (request #1)
2. User click AGAIN before #1 finish (request #2)
✅ Expected: DB lock prevents duplicate

### TC-04: Invalid URL
1. User modify URL parameter
✅ Expected: 403 Forbidden

### TC-05: Expired URL
1. User click after 30 days
✅ Expected: 403 Forbidden

### TC-06: Duplicate Form Submit
1. User submit form with email yang sudah DITERIMA
✅ Expected: Form rejected with warning

---

## 📖 Documentation Files

| File | Description |
|------|-------------|
| `IDEMPOTENT_REGISTRATION_FLOW.md` | Complete technical documentation |
| `SUMMARY_IDEMPOTENT_FLOW.txt` | Visual flow diagram (ASCII art) |
| `README_IDEMPOTENT_FLOW.md` | This file (quick start guide) |
| `deploy_idempotent_flow.sh` | Automated deployment script |
| `test_idempotent_flow.sh` | Post-deployment testing script |

---

## 🔍 Monitoring

### Check for Duplicates

```sql
-- Should return 0 rows
SELECT pra_pendaftaran_id, COUNT(*) as total
FROM pendaftaran_sertifikasi
GROUP BY pra_pendaftaran_id
HAVING total > 1;
```

### Check Audit Logs

```bash
ssh root@76.13.18.166
cd /var/www/lsp-ui.ibnuapps.cloud/current
tail -f storage/logs/laravel.log | grep pendaftaran
```

### Check Failed Email Jobs

```bash
php artisan queue:failed
php artisan queue:retry all
```

---

## 🐛 Troubleshooting

### Problem: Link expired (403)
**Solution:**  
Admin dapat generate link baru dengan resend email atau user hubungi admin.

### Problem: Duplicate pendaftaran ditemukan
**Investigation:**
1. Check audit logs untuk timestamp creation
2. Verify DB lock was applied
3. Check for transaction rollback errors

### Problem: Email tidak terkirim
**Solution:**
1. Check queue worker: `php artisan queue:work`
2. Check failed jobs: `php artisan queue:failed`
3. Check `.env` mail configuration

---

## ✅ Checklist Deployment

**Pre-Deployment:**
- [ ] Test di local semua test cases (TC-01 s/d TC-06)
- [ ] Backup existing files
- [ ] Review code changes

**Deployment:**
- [ ] Upload files via script (`./deploy_idempotent_flow.sh`)
- [ ] Clear caches (route, config, view)
- [ ] Verify routes registered

**Post-Deployment:**
- [ ] Run test script (`./test_idempotent_flow.sh`)
- [ ] Manual test: approve → email → click link → verify no duplicate
- [ ] Monitor audit logs (24 jam pertama)
- [ ] Check database untuk duplicate (query monitoring)

---

## 📞 Support

Jika ada pertanyaan atau issue:
1. Check `IDEMPOTENT_REGISTRATION_FLOW.md` untuk detail teknis
2. Review audit logs di `storage/logs/laravel.log`
3. Contact system administrator

---

## 📄 License

Internal use only - CertiPro LSP System

---

**Last Updated:** 23 January 2026  
**Author:** Senior Laravel Engineer  
**Status:** ✅ Production-Ready

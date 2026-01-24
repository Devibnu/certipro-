# Deployment Guide: Two-Phase Registration Flow Fix

**Issue:** Pra-Pendaftaran auto-creates Pendaftaran Sertifikasi causing confusion about when skema should be assigned.

**Solution:** Clear separation into 2 phases:
- **Phase 1:** Document verification (Pra-Pendaftaran)
- **Phase 2:** Skema assignment + Pendaftaran creation (Explicit action)

---

## 📋 Changes Summary

### Modified Files

1. **app/Http/Controllers/AdminUI/PraPendaftaranAdminController.php**
   - ✅ Removed auto-create logic from `updateStatus()` method
   - ✅ Added `$skemaList` to index view
   - **Impact:** When admin approves Pra-Pendaftaran, system NO LONGER auto-creates Pendaftaran

2. **app/Http/Controllers/AdminUI/PendaftaranSertifikasiAdminController.php**
   - ✅ Added `createFromPraPendaftaran()` method
   - ✅ Added `$praPendaftaranReady` list to index view
   - **Impact:** Admin can explicitly create Pendaftaran with skema assignment

3. **routes/web.php**
   - ✅ Added new route: `POST /pendaftaran-sertifikasi/create-from-pra/{praPendaftaranId}`
   - **Impact:** Enables explicit Pendaftaran creation flow

4. **resources/views/adminui/pra-pendaftaran/index.blade.php**
   - ✅ Added button for DITERIMA records without Pendaftaran
   - ✅ Added modal for skema selection
   - **Impact:** Admin UI now has clear action button to create Pendaftaran

---

## 🔄 New Workflow

### OLD FLOW (Confusing ❌)
```
User Submit Pra-Pendaftaran
    ↓
Admin Approve (DITERIMA)
    ↓
[AUTO] System creates Pendaftaran WITHOUT skema ❌
    ↓
Admin confused: "Where do I assign skema?"
```

### NEW FLOW (Clear ✅)
```
PHASE 1: Document Verification
User Submit Pra-Pendaftaran → Status: BARU
    ↓
Admin Review Documents
    ↓
Admin Decision:
  • Terima → Status: DITERIMA → Email: "Menunggu penetapan skema"
  • Tolak  → Status: DITOLAK  → Email: "Pra-Pendaftaran ditolak"
    ↓
⚠️ STOP - No automatic Pendaftaran creation

---

PHASE 2: Skema Assignment (Explicit Action)
Admin goes to Pra-Pendaftaran index
    ↓
Admin sees green button ✅ on DITERIMA records
    ↓
Admin clicks "Buat Pendaftaran & Tetapkan Skema"
    ↓
Modal opens → Admin selects Skema Sertifikasi
    ↓
System creates:
  • User account (if not exists)
  • Pendaftaran Sertifikasi with skema
  • Status: SIAP_ASESMEN
  • Email: "Skema Ditetapkan - Siap Asesmen"
```

---

## 🚀 Deployment Steps

### Step 1: Backup Database

```bash
ssh root@76.13.18.166
cd /var/www/lsp-ui.ibnuapps.cloud/current
php artisan backup:run
```

### Step 2: Upload Modified Files

```bash
# From local machine
cd /Users/ibnuqosim/Documents/devlopmentibnu/certipro

# Upload PraPendaftaranAdminController
scp app/Http/Controllers/AdminUI/PraPendaftaranAdminController.php \
    root@76.13.18.166:/var/www/lsp-ui.ibnuapps.cloud/current/app/Http/Controllers/AdminUI/

# Upload PendaftaranSertifikasiAdminController
scp app/Http/Controllers/AdminUI/PendaftaranSertifikasiAdminController.php \
    root@76.13.18.166:/var/www/lsp-ui.ibnuapps.cloud/current/app/Http/Controllers/AdminUI/

# Upload routes
scp routes/web.php \
    root@76.13.18.166:/var/www/lsp-ui.ibnuapps.cloud/current/routes/

# Upload view
scp resources/views/adminui/pra-pendaftaran/index.blade.php \
    root@76.13.18.166:/var/www/lsp-ui.ibnuapps.cloud/current/resources/views/adminui/pra-pendaftaran/
```

### Step 3: Clear Caches

```bash
ssh root@76.13.18.166
cd /var/www/lsp-ui.ibnuapps.cloud/current

php artisan route:cache
php artisan config:cache
php artisan view:cache
php artisan cache:clear
```

### Step 4: Set Permissions

```bash
chown -R www-data:www-data /var/www/lsp-ui.ibnuapps.cloud/current/app
chown -R www-data:www-data /var/www/lsp-ui.ibnuapps.cloud/current/routes
chown -R www-data:www-data /var/www/lsp-ui.ibnuapps.cloud/current/resources
```

### Step 5: Restart Services

```bash
systemctl restart php8.3-fpm
systemctl reload nginx
```

---

## ✅ Testing Checklist

### Test 1: Verify No Auto-Create on Approval
1. Login as admin
2. Go to Pra-Pendaftaran menu
3. Open a record with status BARU
4. Click "Terima"
5. **Expected:** 
   - ✅ Status changes to DITERIMA
   - ✅ Success message: "Pra-Pendaftaran berhasil DITERIMA. Silakan ke modul Pendaftaran Sertifikasi..."
   - ✅ NO Pendaftaran Sertifikasi created
6. Check database:
   ```sql
   SELECT * FROM pra_pendaftaran WHERE id = [ID];
   SELECT * FROM pendaftaran_sertifikasi WHERE pra_pendaftaran_id = [ID];
   -- Should return 0 rows
   ```

### Test 2: Verify Explicit Creation Flow
1. Go back to Pra-Pendaftaran index
2. Find the approved record (status: DITERIMA)
3. **Expected:** Green button ✅ visible next to "Lihat Detail"
4. Click green button "Buat Pendaftaran & Tetapkan Skema"
5. **Expected:** Modal opens with:
   - Peserta information (nama, email, tipe)
   - Dropdown: Pilih Skema Sertifikasi (required)
6. Select a skema → Click "Buat Pendaftaran & Tetapkan Skema"
7. **Expected:**
   - ✅ Success message with nomor pendaftaran
   - ✅ Redirect to Pendaftaran detail page
   - ✅ Status: SIAP_ASESMEN
   - ✅ Skema already assigned
   - ✅ Email sent: "Skema Ditetapkan - Siap Asesmen"
8. Check database:
   ```sql
   SELECT * FROM pendaftaran_sertifikasi WHERE pra_pendaftaran_id = [ID];
   -- Should have 1 record with skema_sertifikasi_id NOT NULL
   -- status = 'siap_asesmen'
   ```

### Test 3: Idempotent Behavior
1. Go back to Pra-Pendaftaran index
2. Find the same approved record
3. **Expected:** Green button ✅ NO LONGER visible (already has Pendaftaran)
4. Try accessing route directly:
   ```
   POST /adminui/pendaftaran-sertifikasi/create-from-pra/{ID}
   ```
5. **Expected:** Error message: "Pendaftaran sertifikasi sudah ada untuk pra-pendaftaran ini"

### Test 4: Guard Conditions
1. Try creating Pendaftaran from status BARU (not DITERIMA):
   **Expected:** Error: "Hanya pra-pendaftaran dengan status DITERIMA..."
2. Try creating without selecting skema:
   **Expected:** Validation error: "Skema sertifikasi WAJIB dipilih..."
3. Try creating from non-existent pra-pendaftaran ID:
   **Expected:** 404 Not Found

---

## 🔒 Permission Requirements

**Route:** `pendaftaran-sertifikasi.create-from-pra`

**Required Permission:** `pendaftaran_sertifikasi.create`

**Check in database:**
```sql
SELECT * FROM permissions WHERE name = 'pendaftaran_sertifikasi.create';
```

If permission doesn't exist, create:
```sql
INSERT INTO permissions (name, display_name, guard_name, created_at, updated_at)
VALUES ('pendaftaran_sertifikasi.create', 'Buat Pendaftaran Sertifikasi', 'web', NOW(), NOW());
```

Assign to admin role:
```sql
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r, permissions p
WHERE r.name = 'admin' AND p.name = 'pendaftaran_sertifikasi.create';
```

---

## 📧 Email Flow Changes

### OLD Email Flow
```
Pra-Pendaftaran DITERIMA → Email: "Pra-Pendaftaran Diterima"
    ↓
[AUTO] Pendaftaran Created (no skema) → NO EMAIL ❌
    ↓
Admin assigns skema → Email: "Skema Ditetapkan"
```

### NEW Email Flow
```
Pra-Pendaftaran DITERIMA → Email: "Pra-Pendaftaran Diterima (Menunggu penetapan skema)"
    ↓
[WAIT - No auto-action]
    ↓
Admin creates Pendaftaran + assigns skema → Email: "Skema Ditetapkan - Siap Asesmen"
```

**Key Difference:** Only 2 emails instead of unclear sequence

---

## 🚨 Rollback Plan

If issues occur, revert changes:

```bash
ssh root@76.13.18.166
cd /var/www/lsp-ui.ibnuapps.cloud/current

# Restore from backup
cp backup/PraPendaftaranAdminController.php.backup \
   app/Http/Controllers/AdminUI/PraPendaftaranAdminController.php

cp backup/PendaftaranSertifikasiAdminController.php.backup \
   app/Http/Controllers/AdminUI/PendaftaranSertifikasiAdminController.php

cp backup/web.php.backup routes/web.php

cp backup/index.blade.php.backup \
   resources/views/adminui/pra-pendaftaran/index.blade.php

# Clear caches
php artisan route:cache
php artisan config:cache
php artisan view:cache

# Restart
systemctl restart php8.3-fpm
```

---

## 📊 Monitoring

### Check Logs
```bash
tail -f /var/www/lsp-ui.ibnuapps.cloud/current/storage/logs/laravel.log | grep "PendaftaranAdmin"
```

### Database Queries

**Pra-Pendaftaran yang DITERIMA tapi belum ada Pendaftaran:**
```sql
SELECT p.id, p.nomor_pra_pendaftaran, p.nama_lengkap, p.email, p.status, p.created_at
FROM pra_pendaftaran p
LEFT JOIN pendaftaran_sertifikasi ps ON ps.pra_pendaftaran_id = p.id
WHERE p.status = 'diterima' AND ps.id IS NULL
ORDER BY p.created_at DESC;
```

**Pendaftaran dengan skema ditetapkan langsung:**
```sql
SELECT ps.id, ps.nomor_pendaftaran, ps.nama_lengkap, ps.status, 
       ss.kode_skema, ss.nama_skema, ps.created_at
FROM pendaftaran_sertifikasi ps
JOIN skema_sertifikasi ss ON ss.id = ps.skema_sertifikasi_id
WHERE ps.status = 'siap_asesmen'
  AND ps.catatan_admin LIKE '%Pendaftaran dibuat dari pra-pendaftaran%'
ORDER BY ps.created_at DESC;
```

---

## 👥 Admin Training Notes

### For Pra-Pendaftaran Admin:

**OLD Workflow (Confusing):**
> "When I approve Pra-Pendaftaran, system creates Pendaftaran automatically but I still need to assign skema. Why not assign skema during approval?"

**NEW Workflow (Clear):**
1. **Step 1:** Review documents → Approve/Reject only
   - Terima → Status: DITERIMA
   - Tolak → Status: DITOLAK
   
2. **Step 2:** Go back to Pra-Pendaftaran index
   - See green button ✅ on DITERIMA records
   - Click button → Modal opens
   
3. **Step 3:** Assign skema immediately
   - Select skema from dropdown
   - Click "Buat Pendaftaran & Tetapkan Skema"
   - System creates Pendaftaran with skema → Status: SIAP_ASESMEN

**Key Message:** "Approval and skema assignment are now 2 separate, clear steps"

---

## 🎯 Success Criteria

✅ **Deployed successfully if:**
1. Admin can approve Pra-Pendaftaran WITHOUT auto-creating Pendaftaran
2. Green button ✅ appears on DITERIMA records in index page
3. Modal allows skema selection
4. Pendaftaran created with status SIAP_ASESMEN directly
5. Email "Skema Ditetapkan" sent immediately
6. No duplicate Pendaftaran possible (idempotent)
7. No errors in Laravel log
8. Admin feedback: "Flow is clear now"

---

## 📝 Documentation Links

- **Main Architecture Doc:** `docs/TWO_PHASE_REGISTRATION_ARCHITECTURE.md`
- **Event-Driven Email:** `docs/EVENT_DRIVEN_EMAIL_ARCHITECTURE.md`
- **RBAC System:** `docs/RBAC_SYSTEM.md`
- **QA Test Scenarios:** `docs/QA_TEST_EMAIL_IDEMPOTENT.md`

---

## 🔧 Technical Notes

### Method Signature
```php
public function createFromPraPendaftaran(Request $request, $praPendaftaranId)
```

### Expected POST Data
```php
[
    'skema_sertifikasi_id' => 1, // Required
]
```

### Response Codes
- **200 (Redirect):** Success - Pendaftaran created
- **302 (Redirect Back):** Validation error or guard condition failed
- **404:** Pra-Pendaftaran not found
- **500:** Server error (check logs)

### Database Locks
- Uses `lockForUpdate()` for user creation (race condition safe)
- Transaction-safe: rollback on any error

---

**Deployment Date:** [To be filled]
**Deployed By:** [To be filled]
**Status:** Ready for deployment
**Estimated Downtime:** 0 minutes (no database changes, only code)

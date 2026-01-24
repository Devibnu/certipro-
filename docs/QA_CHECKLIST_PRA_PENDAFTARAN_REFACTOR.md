# 🔍 QA CHECKLIST - PRA-PENDAFTARAN REFACTOR

**Project:** LSP Certification System  
**Module:** Pra-Pendaftaran (Clean Architecture Refactor)  
**Date:** 24 Januari 2026  
**QA Lead:** _________________  
**Status:** ⏳ **PENDING QA**

---

## 📋 PRE-DEPLOYMENT CHECKLIST

### **A. CODE REVIEW** ✅ [  /  ]

- [ ] **A1.** Model `PraPendaftaran.php` hanya memiliki 3 status constants
- [ ] **A2.** Status constants: `MENUNGGU_VERIFIKASI`, `DITERIMA`, `DITOLAK`
- [ ] **A3.** Tidak ada `STATUS_BARU` atau `STATUS_DIPROSES`
- [ ] **A4.** `statusLabels()` method returns correct labels
- [ ] **A5.** Observer tidak handle status `diproses`
- [ ] **A6.** Controller validation: `in:menunggu_verifikasi,diterima,ditolak`
- [ ] **A7.** Controller memiliki GUARD untuk prevent status change
- [ ] **A8.** Controller TIDAK auto-create pendaftaran sertifikasi
- [ ] **A9.** View index.blade.php TIDAK ada modal skema
- [ ] **A10.** View index.blade.php TIDAK ada button "Tetapkan Skema"

**Sign-off:** __________ (Dev Lead)

---

### **B. DATABASE MIGRATION VALIDATION** ✅ [  /  ]

#### **B.1 Migration File Check**
- [ ] **B1.1** Migration file exists: `2026_01_24_*_refactor_pra_pendaftaran_status_clean_architecture.php`
- [ ] **B1.2** Migration has `up()` method with data mapping
- [ ] **B1.3** Migration has `down()` method for rollback
- [ ] **B1.4** Migration adds `status_email` column
- [ ] **B1.5** Migration adds `email_sent_at` column

#### **B.2 Dry-Run Test (Local)**
```bash
# Run migration on local database first
php artisan migrate --pretend

# Expected output:
# alter table `pra_pendaftaran` modify `status` enum(...)
# alter table `pra_pendaftaran` add `status_email` enum(...)
```

- [ ] **B2.1** Migration pretend shows correct SQL
- [ ] **B2.2** No errors in pretend mode

#### **B.3 Local Test**
```bash
# Run actual migration on local
php artisan migrate

# Check result
mysql -u root -p certipro -e "SHOW COLUMNS FROM pra_pendaftaran LIKE 'status';"
mysql -u root -p certipro -e "SELECT DISTINCT status FROM pra_pendaftaran;"
```

- [ ] **B3.1** Status column enum changed successfully
- [ ] **B3.2** Default value = `menunggu_verifikasi`
- [ ] **B3.3** Existing data mapped correctly (baru → menunggu_verifikasi)
- [ ] **B3.4** `status_email` column exists
- [ ] **B3.5** `email_sent_at` column exists
- [ ] **B3.6** No data loss

#### **B.4 Rollback Test (Local)**
```bash
# Test rollback
php artisan migrate:rollback --step=1

# Verify rollback
mysql -u root -p certipro -e "SHOW COLUMNS FROM pra_pendaftaran LIKE 'status';"
```

- [ ] **B4.1** Rollback successful
- [ ] **B4.2** Status column restored to old enum
- [ ] **B4.3** Data restored to old status values

**Sign-off:** __________ (Database Admin)

---

## 🧪 FUNCTIONAL TESTING

### **C. STATUS VALIDATION TESTS** ✅ [  /  ]

#### **C.1 Initial Status**
```
Action: User submit Pra-Pendaftaran (public form)
Expected: status = 'menunggu_verifikasi'
```

- [ ] **C1.1** Create new Pra-Pendaftaran via public form
- [ ] **C1.2** Check database: `status` = `menunggu_verifikasi`
- [ ] **C1.3** Check UI: Badge shows "Menunggu Verifikasi"

#### **C.2 Status Change: MENUNGGU → DITERIMA**
```
Action: Admin approve Pra-Pendaftaran
Expected:
  - Status changes to 'diterima'
  - Success message appears
  - NO Pendaftaran Sertifikasi created
  - Email sent
```

**Test Steps:**
1. Login as admin
2. Go to `/adminui/pra-pendaftaran`
3. Click "Detail" on record with status `menunggu_verifikasi`
4. Change status to `diterima`
5. Click submit

**Expected Results:**
- [ ] **C2.1** Status changes to `diterima` in database
- [ ] **C2.2** Success message: "✅ Pra-Pendaftaran DITERIMA. Langkah selanjutnya..."
- [ ] **C2.3** NO record in `pendaftaran_sertifikasi` table for this Pra-Pendaftaran
- [ ] **C2.4** Email sent to peserta (check MailHog or mail logs)
- [ ] **C2.5** Email subject: "Pra-Pendaftaran Diterima - Menunggu Penetapan Skema"
- [ ] **C2.6** `status_email` = `pra_diterima`
- [ ] **C2.7** `email_sent_at` timestamp set

#### **C.3 Status Change: MENUNGGU → DITOLAK**
```
Action: Admin reject Pra-Pendaftaran with reason
Expected:
  - Status changes to 'ditolak'
  - Rejection reason saved
  - Email with reason sent
```

**Test Steps:**
1. Go to Pra-Pendaftaran detail
2. Change status to `ditolak`
3. Enter rejection reason: "Dokumen tidak lengkap"
4. Click submit

**Expected Results:**
- [ ] **C3.1** Status changes to `ditolak`
- [ ] **C3.2** `alasan_penolakan` = "Dokumen tidak lengkap"
- [ ] **C3.3** Success message: "❌ Pra-Pendaftaran DITOLAK..."
- [ ] **C3.4** Email sent with rejection reason
- [ ] **C3.5** `status_email` = `pra_ditolak`

#### **C.4 Invalid Status Values**
```
Action: Try to set invalid status via form manipulation
Expected: Validation error
```

**Test Steps:**
1. Use browser DevTools to modify form
2. Try to submit with status = `baru`
3. Try to submit with status = `diproses`
4. Try to submit with status = `siap_asesmen`

**Expected Results:**
- [ ] **C4.1** Status `baru` → Validation error: "Status tidak valid"
- [ ] **C4.2** Status `diproses` → Validation error
- [ ] **C4.3** Status `siap_asesmen` → Validation error
- [ ] **C4.4** No database changes

**Sign-off:** __________ (QA Tester)

---

### **D. GUARD CONDITIONS TESTS** ✅ [  /  ]

#### **D.1 Immutable DITERIMA Status**
```
Action: Try to change status from DITERIMA to other status
Expected: Blocked with error message
```

**Test Steps:**
1. Find Pra-Pendaftaran with status = `diterima`
2. Try to change status to `menunggu_verifikasi`
3. Try to change status to `ditolak`

**Expected Results:**
- [ ] **D1.1** Error message: "❌ Pra-pendaftaran yang sudah DITERIMA tidak dapat diubah statusnya..."
- [ ] **D1.2** Status remains `diterima`
- [ ] **D1.3** No database changes

#### **D.2 Immutable DITOLAK Status**
```
Action: Try to change status from DITOLAK to other status
Expected: Blocked with error message
```

**Test Steps:**
1. Find Pra-Pendaftaran with status = `ditolak`
2. Try to change status to `menunggu_verifikasi`
3. Try to change status to `diterima`

**Expected Results:**
- [ ] **D2.1** Error message: "❌ Pra-pendaftaran yang sudah DITOLAK tidak dapat diubah statusnya..."
- [ ] **D2.2** Status remains `ditolak`
- [ ] **D2.3** User told to submit new Pra-Pendaftaran

#### **D.3 No Auto-Create Pendaftaran**
```
Action: Approve Pra-Pendaftaran (status → DITERIMA)
Expected: NO Pendaftaran Sertifikasi record created
```

**Test Steps:**
1. Get Pra-Pendaftaran ID: {praPendaftaranId}
2. Approve it (status → diterima)
3. Check database:
   ```sql
   SELECT * FROM pendaftaran_sertifikasi 
   WHERE pra_pendaftaran_id = {praPendaftaranId};
   ```

**Expected Results:**
- [ ] **D3.1** Query returns 0 rows
- [ ] **D3.2** `pendaftaran_sertifikasi` table does NOT have record for this Pra-Pendaftaran
- [ ] **D3.3** Admin sees message to go to Pendaftaran Sertifikasi module

**Sign-off:** __________ (QA Tester)

---

### **E. UI/UX VALIDATION** ✅ [  /  ]

#### **E.1 Pra-Pendaftaran Index Page**

**Test URL:** `/adminui/pra-pendaftaran`

**Visual Check:**
- [ ] **E1.1** Table header: "Nama | Kontak | Tipe | NIK/NIM | Status | Tanggal | Aksi"
- [ ] **E1.2** Status badge colors:
  - `menunggu_verifikasi` → Grey badge
  - `diterima` → Green badge
  - `ditolak` → Red badge
- [ ] **E1.3** Status badge text:
  - `menunggu_verifikasi` → "Menunggu Verifikasi"
  - `diterima` → "Diterima (Menunggu Penetapan Skema)"
  - `ditolak` → "Ditolak"
- [ ] **E1.4** Aksi column: ONLY "Detail" button (with eye icon)
- [ ] **E1.5** NO "Tetapkan Skema" button
- [ ] **E1.6** NO green checkmark button
- [ ] **E1.7** NO modal for skema assignment

**Browser DevTools Check:**
- [ ] **E1.8** No JavaScript errors in console
- [ ] **E1.9** No modal HTML code in page source (search for "assignSkemaModal")
- [ ] **E1.10** No skema dropdown in page source

#### **E.2 Pra-Pendaftaran Detail Page**

**Test URL:** `/adminui/pra-pendaftaran/{id}`

**Visual Check:**
- [ ] **E2.1** Status form shows dropdown with 3 options only:
  - Menunggu Verifikasi
  - Diterima
  - Ditolak
- [ ] **E2.2** NO option for "Baru" or "Diproses"
- [ ] **E2.3** If status = DITERIMA: Badge shows "Diterima (Menunggu Penetapan Skema)"
- [ ] **E2.4** NO skema dropdown on this page
- [ ] **E2.5** NO button "Buat Pendaftaran Sertifikasi"

**Sign-off:** __________ (UX Designer)

---

### **F. EMAIL FLOW VALIDATION** ✅ [  /  ]

#### **F.1 Email: Pra-Pendaftaran DITERIMA**

**Trigger:** Admin approve Pra-Pendaftaran

**Test Steps:**
1. Approve Pra-Pendaftaran
2. Check mail queue/MailHog
3. Verify email content

**Expected Results:**
- [ ] **F1.1** Email sent to peserta email address
- [ ] **F1.2** Subject: "✅ Pra-Pendaftaran Anda Diterima - Menunggu Penetapan Skema"
- [ ] **F1.3** Body mentions: "Pra-pendaftaran Anda telah diverifikasi dan DITERIMA"
- [ ] **F1.4** Body mentions: "Menunggu penetapan skema"
- [ ] **F1.5** Body mentions: "Anda akan menerima email lanjutan"
- [ ] **F1.6** Nomor pra-pendaftaran included
- [ ] **F1.7** Email formatted correctly (HTML, responsive)

#### **F.2 Email: Pra-Pendaftaran DITOLAK**

**Trigger:** Admin reject Pra-Pendaftaran with reason

**Test Steps:**
1. Reject Pra-Pendaftaran with reason: "Dokumen KTP tidak jelas"
2. Check mail queue
3. Verify email content

**Expected Results:**
- [ ] **F2.1** Email sent to peserta
- [ ] **F2.2** Subject: "❌ Pra-Pendaftaran Ditolak"
- [ ] **F2.3** Body includes rejection reason: "Dokumen KTP tidak jelas"
- [ ] **F2.4** Body instructs to fix and re-submit
- [ ] **F2.5** Email formatted correctly

#### **F.3 Idempotent Email (No Duplicates)**

**Test:** Approve same Pra-Pendaftaran multiple times

**Test Steps:**
1. Approve Pra-Pendaftaran (status → diterima)
2. Email sent → Check `status_email` = `pra_diterima`
3. Try to "approve" again (shouldn't work due to guard, but test)
4. Check mail queue

**Expected Results:**
- [ ] **F3.1** Only 1 email sent (first approval)
- [ ] **F3.2** Second "approval" blocked by guard condition
- [ ] **F3.3** No duplicate email in queue
- [ ] **F3.4** `status_email` field prevents duplicate

**Sign-off:** __________ (QA Tester)

---

### **G. INTEGRATION WITH PENDAFTARAN SERTIFIKASI** ✅ [  /  ]

#### **G.1 Pendaftaran Sertifikasi Module - List**

**Test URL:** `/adminui/pendaftaran-sertifikasi`

**Expected:**
- [ ] **G1.1** Page shows list of approved Pra-Pendaftaran (status = diterima)
- [ ] **G1.2** Filter/section: "Pra-Pendaftaran Siap Diproses"
- [ ] **G1.3** Each item has button "Buat Pendaftaran"

#### **G.2 Create Pendaftaran from Pra-Pendaftaran**

**Test Steps:**
1. Go to Pendaftaran Sertifikasi module
2. Click "Buat Pendaftaran" for a Pra-Pendaftaran (status = diterima)
3. Select skema: "KKNI-001 - Junior Web Developer"
4. Submit

**Expected Results:**
- [ ] **G2.1** Pendaftaran Sertifikasi record created
- [ ] **G2.2** `pra_pendaftaran_id` field populated
- [ ] **G2.3** `skema_sertifikasi_id` field populated
- [ ] **G2.4** Status = `siap_asesmen` (directly)
- [ ] **G2.5** Nomor pendaftaran generated (REG2026xxxx)
- [ ] **G2.6** Email "Skema Ditetapkan" sent to peserta
- [ ] **G2.7** User account created if not exists

#### **G.3 Prevent Duplicate Pendaftaran**

**Test Steps:**
1. Create Pendaftaran from Pra-Pendaftaran (as above)
2. Try to create again from same Pra-Pendaftaran

**Expected Results:**
- [ ] **G3.1** Error message: "Pendaftaran sertifikasi sudah ada untuk pra-pendaftaran ini"
- [ ] **G3.2** No duplicate record created
- [ ] **G3.3** System shows existing Pendaftaran nomor

**Sign-off:** __________ (Integration Tester)

---

### **H. DATABASE CONSISTENCY** ✅ [  /  ]

#### **H.1 Status Values Check**

**Query:**
```sql
SELECT DISTINCT status FROM pra_pendaftaran ORDER BY status;
```

**Expected Output:**
```
+------------------------+
| status                 |
+------------------------+
| menunggu_verifikasi    |
| diterima               |
| ditolak                |
+------------------------+
3 rows in set
```

- [ ] **H1.1** Only 3 distinct status values
- [ ] **H1.2** NO `baru` status
- [ ] **H1.3** NO `diproses` status
- [ ] **H1.4** NO `siap_asesmen` status (this belongs to pendaftaran_sertifikasi)

#### **H.2 Default Value Check**

**Query:**
```sql
SHOW COLUMNS FROM pra_pendaftaran LIKE 'status';
```

**Expected:**
```
Default: menunggu_verifikasi
Type: enum('menunggu_verifikasi','diterima','ditolak')
```

- [ ] **H2.1** Default value = `menunggu_verifikasi`
- [ ] **H2.2** Enum has exactly 3 values

#### **H.3 Status Email Column**

**Query:**
```sql
SHOW COLUMNS FROM pra_pendaftaran LIKE 'status_email';
```

**Expected:**
```
Type: enum('pra_diterima','pra_ditolak')
Null: YES
Default: NULL
```

- [ ] **H3.1** Column `status_email` exists
- [ ] **H3.2** Column `email_sent_at` exists
- [ ] **H3.3** Both columns nullable
- [ ] **H3.4** Index exists on `status_email`

#### **H.4 Data Integrity**

**Query:**
```sql
SELECT 
  p.id, 
  p.status, 
  p.status_email,
  COUNT(ps.id) as pendaftaran_count
FROM pra_pendaftaran p
LEFT JOIN pendaftaran_sertifikasi ps ON ps.pra_pendaftaran_id = p.id
WHERE p.status = 'diterima'
GROUP BY p.id, p.status, p.status_email;
```

**Expected:**
- [ ] **H4.1** All DITERIMA records either have 0 or 1 pendaftaran (no duplicates)
- [ ] **H4.2** If pendaftaran_count = 0 → Waiting for admin to create in Pendaftaran module
- [ ] **H4.3** If pendaftaran_count = 1 → Already processed

**Sign-off:** __________ (Database Admin)

---

### **I. AUDIT LOGS** ✅ [  /  ]

#### **I.1 Status Change Audit**

**Query:**
```sql
SELECT * FROM audit_logs 
WHERE module = 'pra_pendaftaran' 
AND action IN ('verify', 'reject')
ORDER BY created_at DESC 
LIMIT 5;
```

**Expected:**
- [ ] **I1.1** Action = `verify` for DITERIMA status change
- [ ] **I1.2** Action = `reject` for DITOLAK status change
- [ ] **I1.3** Description clearly mentions "DITERIMA" or "DITOLAK"
- [ ] **I1.4** Old values and new values recorded
- [ ] **I1.5** User ID recorded (admin who made the change)

#### **I.2 Migration Audit**

**Query:**
```sql
SELECT * FROM audit_logs 
WHERE module = 'migration' 
AND description LIKE '%refactor%'
ORDER BY created_at DESC;
```

**Expected:**
- [ ] **I2.1** Migration logged in audit_logs table
- [ ] **I2.2** Description: "Pra-Pendaftaran status refactored: 4 states → 3 states"
- [ ] **I2.3** Metadata includes migration filename
- [ ] **I2.4** Old values: ['baru', 'diproses', 'diterima', 'ditolak']
- [ ] **I2.5** New values: ['menunggu_verifikasi', 'diterima', 'ditolak']

**Sign-off:** __________ (Compliance Officer)

---

### **J. PERFORMANCE & LOAD** ✅ [  /  ]

#### **J.1 Query Performance**

**Test:**
```sql
EXPLAIN SELECT * FROM pra_pendaftaran WHERE status = 'menunggu_verifikasi';
```

**Expected:**
- [ ] **J1.1** Query uses index (if status has index)
- [ ] **J1.2** Execution time < 100ms for 10,000 records

#### **J.2 Email Queue Performance**

**Test:** Approve 10 Pra-Pendaftaran at once

**Expected:**
- [ ] **J2.1** All emails queued successfully
- [ ] **J2.2** No queue overflow
- [ ] **J2.3** Emails processed within 5 minutes

**Sign-off:** __________ (Performance Tester)

---

## 🚨 CRITICAL ISSUES (BLOCKER)

**If ANY of these fail, DO NOT DEPLOY:**

- [ ] **CRITICAL 1:** Migration fails on production-like database
- [ ] **CRITICAL 2:** Data loss during migration
- [ ] **CRITICAL 3:** Auto-create pendaftaran still happens (Guard condition not working)
- [ ] **CRITICAL 4:** Emails not sent when status changes
- [ ] **CRITICAL 5:** Duplicate emails sent (Idempotent check not working)
- [ ] **CRITICAL 6:** Status can be changed after DITERIMA/DITOLAK (Immutable guard not working)
- [ ] **CRITICAL 7:** Skema modal still visible in Pra-Pendaftaran index

**Sign-off:** __________ (QA Lead)

---

## 📊 QA SUMMARY

### **Statistics**

| Category                    | Total Tests | Passed | Failed | Blocked |
|-----------------------------|-------------|--------|--------|---------|
| Code Review                 | 10          |        |        |         |
| Database Migration          | 14          |        |        |         |
| Functional Tests            | 18          |        |        |         |
| Guard Conditions            | 7           |        |        |         |
| UI/UX Validation            | 15          |        |        |         |
| Email Flow                  | 15          |        |        |         |
| Integration                 | 9           |        |        |         |
| Database Consistency        | 11          |        |        |         |
| Audit Logs                  | 9           |        |        |         |
| Performance                 | 4           |        |        |         |
| **TOTAL**                   | **112**     | **0**  | **0**  | **0**   |

### **Test Coverage**

- [ ] Model Layer: **100%** (3/3 status constants, validation, labels)
- [ ] Controller Layer: **100%** (validation, guards, no auto-create)
- [ ] Observer Layer: **100%** (only 2 valid transitions)
- [ ] View Layer: **100%** (no skema modal, correct UI)
- [ ] Email Layer: **100%** (idempotent, event-driven)
- [ ] Integration: **100%** (Pendaftaran Sertifikasi module)

---

## ✅ DEPLOYMENT APPROVAL

### **Pre-Production Sign-off**

| Role                  | Name            | Signature       | Date       |
|-----------------------|-----------------|-----------------|------------|
| Senior Developer      | _______________ | _______________ | __________ |
| QA Lead               | _______________ | _______________ | __________ |
| Database Admin        | _______________ | _______________ | __________ |
| UX Designer           | _______________ | _______________ | __________ |
| Compliance Officer    | _______________ | _______________ | __________ |

### **Production Deployment Approval**

- [ ] **ALL** tests passed (112/112)
- [ ] **NO** critical issues found
- [ ] Rollback plan tested and ready
- [ ] Backup completed
- [ ] Stakeholders notified

**Project Manager Approval:**

Name: _______________  
Signature: _______________  
Date: _______________

---

## 📝 NOTES & OBSERVATIONS

**Issues Found During Testing:**
1. _____________________________________________
2. _____________________________________________
3. _____________________________________________

**Recommendations:**
1. _____________________________________________
2. _____________________________________________
3. _____________________________________________

**Follow-up Actions:**
1. _____________________________________________
2. _____________________________________________
3. _____________________________________________

---

**QA Status:** ⏳ **PENDING**  
**Target Deploy Date:** _______________  
**Actual Deploy Date:** _______________

# STEP 3: Evidence Upload per KUK (Audit Booster ISO 17024 / BNSP)

## 🎯 Overview

Fitur upload evidence per Kriteria Unjuk Kerja (KUK) untuk mendukung proses asesmen kompetensi sesuai standar ISO 17024 dan BNSP. Evidence berupa file atau link yang dikaitkan langsung dengan masing-masing KUK dalam asesmen.

## ✅ Fitur yang Diimplementasikan

### 1. Database Schema

**Tabel `evidence_kuk`:**
```sql
- id (BIGINT UNSIGNED, PK)
- asesmen_id (FK → asesmen.id, CASCADE DELETE)
- kuk_id (FK → kuk.id, CASCADE DELETE)
- uploaded_by (FK → users.id, CASCADE DELETE)
- type (ENUM: 'file', 'link')
- file_path (NULLABLE)
- file_name_original (NULLABLE)
- file_mime_type (NULLABLE)
- file_size (NULLABLE)
- link_url (NULLABLE, max 1000 chars)
- description (NULLABLE, max 500 chars)
- created_at, updated_at
```

### 2. Model EvidenceKuk

**Path:** `app/Models/EvidenceKuk.php`

**Features:**
- Constants untuk type (FILE/LINK), allowed MIME types, max file size (10MB)
- Relations: `asesmen()`, `kuk()`, `uploader()`
- Accessors: `type_label`, `type_icon`, `file_icon`, `file_size_human`, `display_name`
- Methods: `isFile()`, `isLink()`, `fileExists()`, `getFullPath()`
- Boot method: Auto-delete file from storage when model deleted

**Allowed File Types:**
- Documents: PDF, DOC, DOCX, XLS, XLSX
- Images: JPG, JPEG, PNG, GIF
- Archives: ZIP

### 3. EvidenceKukController

**Path:** `app/Http/Controllers/AdminUI/EvidenceKukController.php`

**Endpoints:**

| Method | Route | Description |
|--------|-------|-------------|
| GET | `/adminui/asesmen/{id}/evidence` | List all evidence for asesmen |
| GET | `/adminui/asesmen/{id}/evidence/kuk/{kukId}` | List evidence for specific KUK |
| POST | `/adminui/asesmen/{id}/evidence/kuk/{kukId}/upload` | Upload file evidence |
| POST | `/adminui/asesmen/{id}/evidence/kuk/{kukId}/link` | Add link evidence |
| GET | `/adminui/evidence/{id}/download` | Download evidence file |
| DELETE | `/adminui/evidence/{id}` | Delete evidence |

### 4. RBAC Permissions

**Permissions:**
- `evidence.upload` - Upload/add evidence
- `evidence.view` - View evidence
- `evidence.delete` - Delete evidence

**Role Assignments:**

| Role | Upload | View | Delete |
|------|--------|------|--------|
| Super Admin | ✅ | ✅ | ✅ |
| Admin | ❌ | ✅ | ❌ |
| Asesor | ✅ | ✅ | ✅ |
| Komite Teknis | ❌ | ✅ | ❌ |
| Asesi | ❌ | ❌ | ❌ |

### 5. UI Components

**Files:**
- `resources/views/adminui/asesmen/partials/evidence-kuk.blade.php` - Evidence list per KUK
- `resources/views/adminui/asesmen/partials/evidence-modals.blade.php` - Upload/Link/Delete modals + JavaScript

**Features:**
- Evidence list dengan icons per file type
- Upload button (PDF/JPG/PNG/etc up to 10MB)
- Add Link button untuk external URLs
- Delete button dengan konfirmasi
- Lock indicator jika asesmen sudah selesai

### 6. Storage

**Configuration:**
- Disk: `local` (private)
- Path: `private/evidence/{asesmen_id}/`
- Filename: UUID-based untuk keamanan
- Not publicly accessible (served via download endpoint)

### 7. Audit Logging

**Action Constants:**
- `ACTION_EVIDENCE_UPLOADED` - File uploaded
- `ACTION_EVIDENCE_LINK_ADDED` - Link added
- `ACTION_EVIDENCE_DELETED` - Evidence deleted
- `ACTION_EVIDENCE_DOWNLOADED` - File downloaded

**Module:** `MODULE_EVIDENCE`

**Logged Data:**
- asesmen_id, kuk_id, kuk_kode
- file_name, file_size, file_mime (for files)
- link_url (for links)
- IP address, user agent

### 8. PDF Integration

**Updated File:** `resources/views/audit/pdf/asesmen-kompetensi.blade.php`

Evidence per KUK ditampilkan dalam kolom "Evidence" pada tabel penilaian KUK:
- File: 📎 {filename}
- Link: 🔗 {domain/description}

### 9. Lock Mechanism

Jika asesmen memiliki status `selesai`:
- Evidence tidak bisa ditambahkan
- Evidence tidak bisa dihapus
- UI menampilkan alert "Asesmen telah dikunci"

## 📁 File Changes

### Created Files:
1. `database/migrations/2026_01_14_150000_create_evidence_kuk_table.php`
2. `database/migrations/2026_01_14_150100_add_evidence_permissions.php`
3. `app/Models/EvidenceKuk.php`
4. `app/Http/Controllers/AdminUI/EvidenceKukController.php`
5. `resources/views/adminui/asesmen/partials/evidence-kuk.blade.php`
6. `resources/views/adminui/asesmen/partials/evidence-modals.blade.php`

### Modified Files:
1. `app/Models/Asesmen.php` - Added `evidences()` relation, `isLocked()` method
2. `app/Models/Kuk.php` - Added `evidences()` relation
3. `app/Models/AuditLog.php` - Added evidence action/module constants
4. `app/Http/Controllers/AdminUI/AsesmenController.php` - Eager load evidence in show/exportAuditEvidence
5. `resources/views/adminui/asesmen/show.blade.php` - Integrated evidence column and modals
6. `resources/views/audit/pdf/asesmen-kompetensi.blade.php` - Added evidence column in PDF
7. `routes/web.php` - Added evidence routes

## 🔧 Testing

### Manual Testing Steps:

1. **Login sebagai Asesor:**
   - Buka detail asesmen
   - Verify kolom "Evidence" muncul di tabel KUK
   - Test upload file (PDF/JPG/PNG < 10MB)
   - Test add link (URL valid)
   - Test delete evidence
   - Test download evidence file

2. **Login sebagai Admin/Komite Teknis:**
   - Buka detail asesmen
   - Verify bisa lihat evidence (view only)
   - Verify tidak bisa upload/delete

3. **Test Lock Mechanism:**
   - Set asesmen status = 'selesai'
   - Verify upload/delete buttons hidden
   - Verify alert "Asesmen telah dikunci" muncul

4. **Test Audit PDF:**
   - Download Audit Evidence PDF
   - Verify evidence tercantum di kolom Evidence

5. **Test Audit Log:**
   - Upload/delete evidence
   - Cek audit log untuk action evidence_uploaded/evidence_deleted

## 🔐 Security Features

1. **Private Storage** - File tidak bisa diakses langsung via URL
2. **MIME Type Validation** - Whitelist file types
3. **File Size Limit** - Max 10MB
4. **UUID Filename** - Prevent enumeration attacks
5. **RBAC Permissions** - Role-based access control
6. **Audit Trail** - Full logging untuk ISO 17024 compliance
7. **Cascade Delete** - File otomatis terhapus saat evidence/asesmen dihapus

## 📊 Compliance

- ✅ ISO 17024:2012 - Audit trail & traceability
- ✅ BNSP Guidelines - Evidence per KUK
- ✅ Data integrity - Private storage, audit logging
- ✅ Access control - Role-based permissions

---

**Completed:** 2026-01-14
**Migration Batch:** 27, 28

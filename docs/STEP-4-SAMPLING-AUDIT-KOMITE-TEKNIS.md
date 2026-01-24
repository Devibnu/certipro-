# STEP 4: Sampling Audit Komite Teknis (Quality Control LSP)

## 🎯 Overview

Fitur Sampling Audit untuk Quality Control (QC) oleh Komite Teknis, sesuai standar ISO 17024. Mekanisme ini menunjukkan pengendalian mutu proses sertifikasi dan membuktikan bahwa tidak semua asesmen diputus tanpa review.

## ✅ SOP Narrative (ISO 17024)

> *"Sebagai bagian dari pengendalian mutu, LSP melakukan sampling asesmen secara berkala untuk memastikan konsistensi dan objektivitas penilaian."*
>
> — ISO 17024:2012 Clause 4.3 (Impartiality) & Clause 9.4 (Internal Audits)

## ✅ Fitur yang Diimplementasikan

### 1. Database Schema

**Tabel `asesmen` (Updated):**
```sql
- is_sampled (BOOLEAN, default FALSE)
- sampled_at (TIMESTAMP, nullable)
- sampled_by (FK → users.id, SET NULL on delete)
- sampling_note (TEXT, nullable, max 2000 chars)
```

### 2. Model Asesmen (Updated)

**New Relations:**
- `sampledByUser()` - User who marked for sampling

**New Scopes:**
- `sampled()` - Filter sampled asesmen
- `notSampled()` - Filter non-sampled asesmen

**New Methods:**
- `isSampled()` - Check if marked for sampling
- `canModifySampling()` - Check if sampling can be modified

**New Accessors:**
- `sampling_status_label` - "Sampling Audit" or "Tidak Disampling"
- `sampling_badge` - CSS class for badge

### 3. RBAC Permissions

**Permissions:**
- `sampling.mark` - Mark/unmark asesmen for sampling
- `sampling.view` - View sampling status and notes

**Role Assignments:**

| Role | Mark | View |
|------|------|------|
| Super Admin | ✅ | ✅ |
| Komite Teknis | ✅ | ✅ |
| Admin | ❌ | ✅ |
| Asesor | ❌ | ❌ |
| Asesi | ❌ | ❌ |

### 4. SamplingController

**Path:** `app/Http/Controllers/AdminUI/SamplingController.php`

**Endpoints:**

| Method | Route | Description |
|--------|-------|-------------|
| POST | `/adminui/sampling/{id}/mark` | Mark for sampling |
| POST | `/adminui/sampling/{id}/unmark` | Unmark from sampling |
| POST | `/adminui/sampling/{id}/note` | Update sampling note |
| GET | `/adminui/sampling/{id}/status` | Get sampling status |

### 5. UI Components

**Sampling Section (Detail Asesmen):**
- Card with sampling status badge
- SOP narrative (ISO 17024 reference)
- Form to mark as sampling with optional note
- Update note functionality
- Cancel sampling button
- Lock indicator when keputusan is locked

**Index Page (Riwayat Asesmen):**
- Filter buttons: Semua | Sampling | Non-Sampling
- Sampling badge per row
- Column header for sampling status

### 6. Rules & Flow

**Sampling dapat dilakukan jika:**
1. Asesmen sudah `selesai` (STATUS_SELESAI)
2. Keputusan sertifikasi BELUM dikunci (not locked)

**Jika keputusan sudah LOCK:**
- Sampling = read-only
- Tidak bisa mark/unmark
- Tidak bisa update catatan

### 7. Audit Logging

**Action Constants:**
- `ACTION_SAMPLING_MARKED` - Asesmen marked for sampling
- `ACTION_SAMPLING_UNMARKED` - Sampling cancelled
- `ACTION_SAMPLING_NOTE_UPDATED` - Note updated

**Module:** `MODULE_SAMPLING`

**Logged Data:**
- asesmen_id, pendaftaran_id, nomor_pendaftaran
- asesor_id
- IP address, user agent
- Old/new values for updates

### 8. Audit Evidence PDF (Updated)

**New Section 4:** "Sampling Audit (Pengendalian Mutu)"

Tampilan:
- SOP narrative
- Status sampling (badge)
- Jika sampled:
  - Tanggal sampling
  - Ditandai oleh
  - Catatan sampling

## 📁 File Changes

### Created Files:
1. `database/migrations/2026_01_14_160000_add_sampling_fields_to_asesmen_table.php`
2. `database/migrations/2026_01_14_160100_add_sampling_permissions.php`
3. `app/Http/Controllers/AdminUI/SamplingController.php`
4. `resources/views/adminui/asesmen/partials/sampling-section.blade.php`

### Modified Files:
1. `app/Models/Asesmen.php` - Added sampling fields, relations, methods
2. `app/Models/AuditLog.php` - Added sampling action/module constants
3. `app/Http/Controllers/AdminUI/AsesmenController.php` - Eager load sampledByUser, sampling filter
4. `resources/views/adminui/asesmen/show.blade.php` - Included sampling section
5. `resources/views/adminui/asesmen/index.blade.php` - Added filter & badge for sampling
6. `resources/views/audit/pdf/asesmen-kompetensi.blade.php` - Added Section 4 Sampling Audit
7. `routes/web.php` - Added sampling routes

## 🔧 Testing

### Manual Testing Steps:

1. **Login sebagai Komite Teknis:**
   - Buka Riwayat Asesmen
   - Verify filter Sampling tersedia
   - Buka detail asesmen yang sudah selesai
   - Verify section "Sampling Audit" muncul
   - Tandai sebagai sampling dengan catatan
   - Verify badge "SAMPLING AUDIT" muncul
   - Update catatan sampling
   - Batalkan sampling

2. **Login sebagai Admin:**
   - Verify dapat lihat status sampling (view only)
   - Verify tidak dapat mark/unmark sampling

3. **Test Lock Mechanism:**
   - Set keputusan sertifikasi sebagai locked
   - Verify sampling menjadi read-only
   - Verify alert lock muncul

4. **Test Audit PDF:**
   - Download Audit Evidence PDF
   - Verify Section 4 "Sampling Audit" muncul
   - Verify status dan catatan sampling ditampilkan

5. **Test Audit Log:**
   - Mark/unmark sampling
   - Cek audit log untuk event asesmen_marked_sampling, dll

## 🔐 Compliance

- ✅ **ISO 17024:2012 Clause 4.3** - Impartiality (sampling shows independent review)
- ✅ **ISO 17024:2012 Clause 9.4** - Internal audits (documented QC mechanism)
- ✅ **BNSP Pedoman 201** - Quality control documentation
- ✅ **Audit Trail** - All sampling actions logged
- ✅ **Role-based Access** - Only Komite Teknis can mark sampling

## 📊 Audit Visibility

Auditor dapat melihat:
1. **Filter Sampling** di daftar asesmen
2. **Badge SAMPLING** pada asesmen yang dipilih
3. **Section Sampling Audit** di detail asesmen
4. **PDF Evidence** dengan section Sampling Audit
5. **Audit Log** untuk semua aktivitas sampling

---

**Completed:** 2026-01-14
**Migration Batch:** 29, 30

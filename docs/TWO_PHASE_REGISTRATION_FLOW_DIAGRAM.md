# Two-Phase Registration Flow - Visual Diagram

```
╔════════════════════════════════════════════════════════════════════════╗
║                    TWO-PHASE REGISTRATION FLOW                         ║
╚════════════════════════════════════════════════════════════════════════╝

┌─────────────────────────────────────────────────────────────────────────┐
│                         PHASE 1: DOCUMENT VERIFICATION                  │
│                         (Pra-Pendaftaran Module)                        │
└─────────────────────────────────────────────────────────────────────────┘

    👤 USER (Public)                          🔐 ADMIN (LSP Staff)
         │                                            │
         │  1. Submit Pra-Pendaftaran                │
         │     • Nama, Email, No HP                  │
         │     • NIK/NIM                             │
         │     • Upload KTP, Ijazah, CV              │
         ├──────────────────────────────────────────>│
         │                                            │
         │                                       2. Review Documents
         │                                            │
         │                                       ┌────┴────┐
         │                                       │ Decision│
         │                                       └────┬────┘
         │                                            │
         │                              ┌─────────────┴─────────────┐
         │                              │                           │
         │                         ✅ TERIMA                   ❌ TOLAK
         │                              │                           │
         │<────────────────────────────┤                           ├────>│
         │  📧 Email:                   │                           │  📧 Email:
         │  "Pra-Pendaftaran Diterima   │                           │  "Pra-Pendaftaran Ditolak
         │   Menunggu penetapan skema"  │                           │   Alasan: [...]"
         │                              │                           │
         │                      ✋ STOP HERE                    ✋ STOP HERE
         │                      No Auto-Create!                (End of process)
         │                              │
         └──────────────────────────────┼───────────────────────────────────┘
                                        │
                    Status: DITERIMA    │
                    No Pendaftaran yet  │
                                        ▼


┌─────────────────────────────────────────────────────────────────────────┐
│                    PHASE 2: SKEMA ASSIGNMENT (Explicit)                 │
│                    (Pendaftaran Sertifikasi Module)                     │
└─────────────────────────────────────────────────────────────────────────┘

                              🔐 ADMIN (LSP Staff)
                                        │
                                        │
                        3. Go to Pra-Pendaftaran Index
                                        │
                                        │
                        ┌───────────────┴───────────────┐
                        │  Pra-Pendaftaran List         │
                        │  ┌──────────────────────────┐ │
                        │  │ Nama    Status    Aksi   │ │
                        │  ├──────────────────────────┤ │
                        │  │ John    DITERIMA  👁️ ✅  │ │ <── Green button
                        │  │ Jane    BARU      👁️     │ │
                        │  └──────────────────────────┘ │
                        └───────────────┬───────────────┘
                                        │
                                        │ 4. Click ✅
                                        ▼
                        ┌───────────────────────────────┐
                        │  Modal: Tetapkan Skema        │
                        │  ┌─────────────────────────┐  │
                        │  │ Nama: John Doe          │  │
                        │  │ Email: john@example.com │  │
                        │  │                         │  │
                        │  │ Pilih Skema: *          │  │
                        │  │ ┌─────────────────────┐ │  │
                        │  │ │ KKNI-001 - Web Dev  │ │  │
                        │  │ └─────────────────────┘ │  │
                        │  │                         │  │
                        │  │ [Buat Pendaftaran]      │  │
                        │  └─────────────────────────┘  │
                        └───────────────┬───────────────┘
                                        │
                                        │ 5. Submit
                                        ▼
                        ┌───────────────────────────────┐
                        │  System Actions:              │
                        │  ✓ Create User (if not exists)│
                        │  ✓ Generate nomor pendaftaran │
                        │  ✓ Create Pendaftaran         │
                        │  ✓ Assign Skema               │
                        │  ✓ Set Status: SIAP_ASESMEN   │
                        │  ✓ Send Email                 │
                        └───────────────┬───────────────┘
                                        │
                                        ▼
                               👤 USER (Peserta)
                                        │
                      📧 Email: "Skema Ditetapkan - Siap Asesmen"
                                        │
                            • Nomor Pendaftaran: REG2024xxxx
                            • Skema: KKNI-001 - Junior Web Developer
                            • Status: SIAP ASESMEN
                            • Next: Menunggu penjadwalan asesmen
                                        │
                                        ▼
                              ✅ READY FOR ASESMEN


╔════════════════════════════════════════════════════════════════════════╗
║                          KEY IMPROVEMENTS                              ║
╚════════════════════════════════════════════════════════════════════════╝

❌ OLD FLOW:
   Approve → [AUTO] Create Pendaftaran (no skema) → Admin confused
   
✅ NEW FLOW:
   Approve → [STOP] → Admin explicitly creates with skema → Clear

┌────────────────────────────────────────────────────────────────────────┐
│                          COMPARISON TABLE                              │
├────────────────────────────────────────────────────────────────────────┤
│  Aspect         │  OLD (Confusing)       │  NEW (Clear)              │
├─────────────────┼────────────────────────┼───────────────────────────┤
│  Auto-Create    │  ✅ Yes (problematic)  │  ❌ No (explicit)         │
│  Skema Field    │  ❌ Missing at start   │  ✅ Required upfront      │
│  Admin Steps    │  3 separate actions    │  1 modal action           │
│  Confusion      │  ⚠️  High              │  ✅ Low                   │
│  Email Clarity  │  ⚠️  Unclear sequence  │  ✅ Clear sequence        │
│  Status Flow    │  DITERIMA → BARU →     │  DITERIMA → SIAP_ASESMEN  │
│                 │  SIAP_ASESMEN          │  (direct)                 │
└─────────────────┴────────────────────────┴───────────────────────────┘


╔════════════════════════════════════════════════════════════════════════╗
║                       STATUS TRANSITION DIAGRAM                        ║
╚════════════════════════════════════════════════════════════════════════╝

OLD FLOW (Auto-Create):
┌─────┐      ┌─────────┐      ┌──────┐      ┌──────────────┐
│ BARU│─────>│DITERIMA │─────>│ BARU │─────>│SIAP_ASESMEN  │
└─────┘      └─────────┘      └──────┘      └──────────────┘
              (Pra)        (Auto-Create)    (Assign Skema)
                              Pendaftaran
                           ❌ CONFUSING!

NEW FLOW (Explicit):
┌─────┐      ┌─────────┐                    ┌──────────────┐
│ BARU│─────>│DITERIMA │───────────────────>│SIAP_ASESMEN  │
└─────┘      └─────────┘                    └──────────────┘
              (Pra)                      (Create Pendaftaran
                                          + Assign Skema)
                                         ✅ CLEAR!


╔════════════════════════════════════════════════════════════════════════╗
║                          DATABASE RELATIONS                            ║
╚════════════════════════════════════════════════════════════════════════╝

┌──────────────────┐               ┌──────────────────────────┐
│ pra_pendaftaran  │               │ pendaftaran_sertifikasi  │
├──────────────────┤               ├──────────────────────────┤
│ id               │<─────────────>│ pra_pendaftaran_id       │
│ nama_lengkap     │      1:1      │ skema_sertifikasi_id     │
│ email            │               │ status                   │
│ status           │               │ nomor_pendaftaran        │
│ • baru           │               │ • baru                   │
│ • diterima       │               │ • siap_asesmen ✅        │
│ • ditolak        │               │ • dalam_proses           │
└──────────────────┘               └──────────────────────────┘
        │                                      │
        │                                      │
        └──────────────┬───────────────────────┘
                       │
                       ▼
               ┌───────────────┐
               │ users         │
               ├───────────────┤
               │ id            │
               │ email         │
               │ name          │
               │ role_id       │
               └───────────────┘

NEW BEHAVIOR:
  • Pra-Pendaftaran DITERIMA → Pendaftaran NOT AUTO-CREATED
  • Admin action required → Modal with skema selection
  • Pendaftaran created → status DIRECTLY to SIAP_ASESMEN
  • No intermediate "baru" status in Pendaftaran


╔════════════════════════════════════════════════════════════════════════╗
║                          UI COMPONENT FLOW                             ║
╚════════════════════════════════════════════════════════════════════════╝

1. Pra-Pendaftaran Index Page
   ┌──────────────────────────────────────────────────────────┐
   │  Daftar Pra-Pendaftaran                                  │
   │  ┌────────┬─────────────┬──────────┐                    │
   │  │ Nama   │ Status      │ Aksi     │                    │
   │  ├────────┼─────────────┼──────────┤                    │
   │  │ John   │ DITERIMA ✅ │ 👁️ ✅    │ <── NEW BUTTON     │
   │  │ Jane   │ BARU        │ 👁️       │                    │
   │  │ Bob    │ DITOLAK     │ 👁️       │                    │
   │  └────────┴─────────────┴──────────┘                    │
   └──────────────────────────────────────────────────────────┘

2. Click ✅ Button → Modal Opens
   ┌──────────────────────────────────────────────────────────┐
   │  Buat Pendaftaran & Tetapkan Skema          [X]          │
   ├──────────────────────────────────────────────────────────┤
   │  Nama:  John Doe                                         │
   │  Email: john@example.com                                 │
   │  Tipe:  Umum                                             │
   │  ───────────────────────────────────────────────────────│
   │  Pilih Skema Sertifikasi: *                              │
   │  ┌─────────────────────────────────────────────────────┐│
   │  │ -- Pilih Skema --                                   ││
   │  │ KKNI-001 - Junior Web Developer                     ││
   │  │ KKNI-002 - Web Designer                             ││
   │  │ KKNI-003 - Digital Marketing Specialist             ││
   │  └─────────────────────────────────────────────────────┘│
   │                                                          │
   │  ⓘ Skema ini akan langsung ditetapkan dan status        │
   │     pendaftaran menjadi SIAP ASESMEN                     │
   │                                                          │
   │  [Batal]              [Buat Pendaftaran & Tetapkan ✅]   │
   └──────────────────────────────────────────────────────────┘

3. After Submit → Redirect to Pendaftaran Detail
   ┌──────────────────────────────────────────────────────────┐
   │  ✅ Pendaftaran sertifikasi berhasil dibuat dengan       │
   │     nomor: REG2024xxxx. Skema 'Junior Web Developer'    │
   │     telah ditetapkan. Status: SIAP ASESMEN.             │
   │                                                          │
   │  Detail Pendaftaran Sertifikasi                          │
   │  ┌────────────────────────────────────────────────────┐ │
   │  │ Nomor Pendaftaran: REG2024xxxx                     │ │
   │  │ Nama: John Doe                                     │ │
   │  │ Skema: KKNI-001 - Junior Web Developer            │ │
   │  │ Status: SIAP ASESMEN ✅                            │ │
   │  │ Tanggal: 24 Januari 2026                          │ │
   │  └────────────────────────────────────────────────────┘ │
   └──────────────────────────────────────────────────────────┘


╔════════════════════════════════════════════════════════════════════════╗
║                          GUARD VALIDATIONS                             ║
╚════════════════════════════════════════════════════════════════════════╝

createFromPraPendaftaran() Method Guards:

1. ✋ Status Guard
   IF pra_pendaftaran.status != 'diterima'
   THEN Error: "Hanya pra-pendaftaran dengan status DITERIMA..."
   
2. ✋ Idempotent Guard
   IF pra_pendaftaran HAS pendaftaran_sertifikasi
   THEN Error: "Pendaftaran sertifikasi sudah ada..."
   
3. ✋ Skema Validation
   IF skema_sertifikasi_id IS NULL or NOT EXISTS
   THEN Error: "Skema sertifikasi WAJIB dipilih..."
   
4. ✋ Permission Check
   IF user NOT HAS permission 'pendaftaran_sertifikasi.create'
   THEN 403 Forbidden
   
5. ✋ Race Condition Safe
   Use lockForUpdate() for user creation
   Catch duplicate entry exception


╔════════════════════════════════════════════════════════════════════════╗
║                          EMAIL SEQUENCE                                ║
╚════════════════════════════════════════════════════════════════════════╝

Timeline View:

OLD FLOW:
  T1: Submit Pra-Pendaftaran
      └─> No email
  
  T2: Admin approves
      └─> 📧 Email: "Pra-Pendaftaran Diterima"
  
  T3: [AUTO] System creates Pendaftaran (no skema)
      └─> ❌ No email (confusing!)
  
  T4: Admin assigns skema
      └─> 📧 Email: "Skema Ditetapkan"
  
  ⚠️ Problem: T3 has no email, user doesn't know what's happening

NEW FLOW:
  T1: Submit Pra-Pendaftaran
      └─> No email
  
  T2: Admin approves
      └─> 📧 Email: "Pra-Pendaftaran Diterima (Menunggu penetapan skema)"
  
  T3: [WAIT] No auto-action
      └─> User waits, knows admin will assign skema
  
  T4: Admin creates Pendaftaran + assigns skema
      └─> 📧 Email: "Skema Ditetapkan - Siap Asesmen"
  
  ✅ Clear: Only 2 emails, clear sequence


╔════════════════════════════════════════════════════════════════════════╗
║                       DEPLOYMENT CHECKLIST                             ║
╚════════════════════════════════════════════════════════════════════════╝

Pre-Deployment:
  ☑ Backup database
  ☑ Backup files (auto by script)
  ☑ Verify local changes (no errors)
  ☑ Review documentation

Deployment:
  ☑ Upload PraPendaftaranAdminController.php
  ☑ Upload PendaftaranSertifikasiAdminController.php
  ☑ Upload web.php
  ☑ Upload index.blade.php
  ☑ Set permissions
  ☑ Clear caches
  ☑ Restart services

Post-Deployment:
  ☑ Test: Approve Pra-Pendaftaran → No auto-create
  ☑ Test: Green button appears
  ☑ Test: Modal works
  ☑ Test: Create with skema → SIAP_ASESMEN
  ☑ Test: Email sent
  ☑ Test: Idempotent behavior
  ☑ Check logs for errors
  ☑ Admin feedback

Monitoring:
  ☑ tail -f laravel.log
  ☑ Check database queries
  ☑ Monitor email delivery
  ☑ Watch for admin confusion (should be zero)


═══════════════════════════════════════════════════════════════════════════
                           END OF DIAGRAM
═══════════════════════════════════════════════════════════════════════════
```

# Two-Phase Registration Flow - Quick Reference Card

## 🎯 What Changed?

**Problem:** Auto-create Pendaftaran on Pra-Pendaftaran approval (confusing)
**Solution:** 2-phase flow with explicit skema assignment

---

## 📁 Modified Files (4 files)

✅ `app/Http/Controllers/AdminUI/PraPendaftaranAdminController.php`
✅ `app/Http/Controllers/AdminUI/PendaftaranSertifikasiAdminController.php`
✅ `routes/web.php`
✅ `resources/views/adminui/pra-pendaftaran/index.blade.php`

---

## 🚀 Deploy Command

```bash
./deploy-two-phase-flow.sh
```

---

## ✅ Testing Steps (1 minute)

1. **Approve Pra-Pendaftaran**
   - Go to: `/adminui/pra-pendaftaran`
   - Open record with status BARU
   - Click "Terima"
   - **Expected:** Message: "Silakan ke modul Pendaftaran Sertifikasi..."
   - **Expected:** NO Pendaftaran created

2. **Create Pendaftaran with Skema**
   - Go back to index
   - Find DITERIMA record
   - **Expected:** Green button ✅ visible
   - Click green button
   - **Expected:** Modal opens
   - Select skema → Submit
   - **Expected:** Pendaftaran created, status SIAP_ASESMEN

3. **Verify Idempotent**
   - Go back to index
   - **Expected:** Green button ✅ NOT visible on same record

---

## 🔄 New Workflow (Admin)

```
PHASE 1: Verify Documents
  1. Review Pra-Pendaftaran
  2. Click "Terima" or "Tolak"
  3. STOP (no auto-action)

PHASE 2: Assign Skema
  4. Go to Pra-Pendaftaran index
  5. Click green button ✅
  6. Select skema from modal
  7. Click "Buat Pendaftaran & Tetapkan Skema"
  8. Done! Status → SIAP_ASESMEN
```

---

## 📧 Email Flow

**Before:** 2 emails (unclear sequence)
**After:** 2 emails (clear sequence)

1. Pra-Pendaftaran DITERIMA → "Menunggu penetapan skema"
2. Pendaftaran Created + Skema → "Skema Ditetapkan - Siap Asesmen"

---

## 🔒 Guards

✅ Status must be DITERIMA
✅ No duplicate Pendaftaran
✅ Skema required
✅ Permission: `pendaftaran_sertifikasi.create`
✅ Race condition safe

---

## 🚨 Rollback (if needed)

```bash
ssh root@76.13.18.166
cd /var/www/lsp-ui.ibnuapps.cloud/current/backup/two-phase-flow-fix-[TIMESTAMP]
# Restore files from backup
# Clear caches
# Restart services
```

---

## 📊 Monitor

```bash
# Check logs
tail -f storage/logs/laravel.log | grep "PendaftaranAdmin"

# Check Pra-Pendaftaran without Pendaftaran
SELECT p.id, p.nomor_pra_pendaftaran, p.status
FROM pra_pendaftaran p
LEFT JOIN pendaftaran_sertifikasi ps ON ps.pra_pendaftaran_id = p.id
WHERE p.status = 'diterima' AND ps.id IS NULL;
```

---

## 🎓 Admin Training (30 seconds)

**OLD:** "When I approve, system creates Pendaftaran automatically but I still need to assign skema."
**NEW:** "Approve first, then I see green button to create Pendaftaran with skema in one step."

---

## 📖 Full Documentation

- **Summary:** `docs/TWO_PHASE_REGISTRATION_FLOW_SUMMARY.md`
- **Deployment:** `docs/TWO_PHASE_REGISTRATION_FLOW_DEPLOYMENT.md`
- **Diagram:** `docs/TWO_PHASE_REGISTRATION_FLOW_DIAGRAM.md`

---

## ✅ Success Criteria

- [ ] No auto-create on approval
- [ ] Green button ✅ appears
- [ ] Modal works
- [ ] Pendaftaran created with SIAP_ASESMEN status
- [ ] Email sent
- [ ] Idempotent
- [ ] No errors in log
- [ ] Admin says "Clear now!"

---

**Status:** Ready ✅
**Downtime:** 0 minutes
**Risk:** Low
**Rollback:** Easy

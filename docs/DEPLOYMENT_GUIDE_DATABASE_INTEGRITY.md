# 🚀 DEPLOYMENT GUIDE - Database Integrity Hardening

**System:** LSP CertiPro  
**Date:** January 22, 2026  
**Version:** Database Integrity v2.0  
**Environment:** Production (lsp-ui.ibnuapps.cloud)

---

## 📋 PRE-DEPLOYMENT CHECKLIST

### 1. Backup Database (MANDATORY!)

```bash
# SSH to production server
ssh root@76.13.18.166

# Create backup directory
mkdir -p /root/backups/$(date +%Y%m%d)

# Full database backup
mysqldump -uroot -p'nw3wGgZYE.dFX65' certipro_lsp \
  > /root/backups/$(date +%Y%m%d)/certipro_lsp_pre_integrity_$(date +%H%M%S).sql

# Verify backup size
ls -lh /root/backups/$(date +%Y%m%d)/

# Expected: >500KB (depending on data volume)
```

### 2. Test in Development First

```bash
# Local development
cd /Users/ibnuqosim/Documents/devlopmentibnu/certipro

# Detect orphaned records
php artisan certipro:detect-orphans --export=json

# Review results
cat storage/app/orphan-detection-*.json

# Dry-run cleanup
php artisan certipro:cleanup-orphans --dry-run

# If OK, clean up (with confirmation)
php artisan certipro:cleanup-orphans
```

### 3. Verify Current State

```bash
# Check constraint violations before migration
ssh root@76.13.18.166 << 'EOF'
cd /var/www/lsp-ui.ibnuapps.cloud/current

# Check NULL foreign keys
mysql -uroot -p'nw3wGgZYE.dFX65' certipro_lsp -e "
  SELECT COUNT(*) as null_user_count 
  FROM pendaftaran_sertifikasi 
  WHERE user_id IS NULL OR skema_sertifikasi_id IS NULL;
"

# Check orphaned asesmen
mysql -uroot -p'nw3wGgZYE.dFX65' certipro_lsp -e "
  SELECT COUNT(*) as orphaned_asesmen
  FROM asesmen a
  LEFT JOIN pendaftaran_sertifikasi p ON a.pendaftaran_id = p.id
  WHERE p.id IS NULL;
"

# Check certificates without keputusan
mysql -uroot -p'nw3wGgZYE.dFX65' certipro_lsp -e "
  SELECT COUNT(*) as certs_without_keputusan
  FROM sertifikat s
  LEFT JOIN keputusan_sertifikasi k ON s.pendaftaran_id = k.pendaftaran_id
  WHERE k.id IS NULL;
"
EOF
```

**STOP HERE if any count > 0!**  
→ Run cleanup commands first before proceeding.

---

## 🔧 DEPLOYMENT STEPS

### Step 1: Upload Files to Production

```bash
# From local machine
cd /Users/ibnuqosim/Documents/devlopmentibnu/certipro

# Upload migrations
scp database/migrations/2026_01_22_010000_fix_pendaftaran_nullable_fk.php \
    root@76.13.18.166:/var/www/lsp-ui.ibnuapps.cloud/current/database/migrations/

scp database/migrations/2026_01_22_020000_add_keputusan_id_to_sertifikat.php \
    root@76.13.18.166:/var/www/lsp-ui.ibnuapps.cloud/current/database/migrations/

scp database/migrations/2026_01_22_030000_change_fk_strategy_to_restrict.php \
    root@76.13.18.166:/var/www/lsp-ui.ibnuapps.cloud/current/database/migrations/

scp database/migrations/2026_01_22_040000_add_performance_indexes.php \
    root@76.13.18.166:/var/www/lsp-ui.ibnuapps.cloud/current/database/migrations/

# Upload console commands
scp app/Console/Commands/DetectOrphanedRecords.php \
    root@76.13.18.166:/var/www/lsp-ui.ibnuapps.cloud/current/app/Console/Commands/

scp app/Console/Commands/CleanupOrphanedRecords.php \
    root@76.13.18.166:/var/www/lsp-ui.ibnuapps.cloud/current/app/Console/Commands/
```

### Step 2: Run Orphan Detection (Production)

```bash
ssh root@76.13.18.166
cd /var/www/lsp-ui.ibnuapps.cloud/current

# Detect issues
php artisan certipro:detect-orphans --export=json

# Review results
cat storage/app/orphan-detection-*.json

# If issues found, dry-run cleanup
php artisan certipro:cleanup-orphans --dry-run

# Review output carefully, then cleanup
php artisan certipro:cleanup-orphans
```

### Step 3: Run Migrations (Production)

**⚠️ CAUTION: Run migrations ONE BY ONE**

```bash
ssh root@76.13.18.166
cd /var/www/lsp-ui.ibnuapps.cloud/current

# Enable maintenance mode
php artisan down --message="Database integrity hardening in progress"

# Migration 1: Fix pendaftaran nullable FKs
php artisan migrate --path=/database/migrations/2026_01_22_010000_fix_pendaftaran_nullable_fk.php

# VERIFY: Check if migration succeeded
php artisan migrate:status | grep fix_pendaftaran_nullable_fk

# Migration 2: Add keputusan_id to sertifikat
php artisan migrate --path=/database/migrations/2026_01_22_020000_add_keputusan_id_to_sertifikat.php

# VERIFY: Check column exists
mysql -uroot -p'nw3wGgZYE.dFX65' certipro_lsp -e "SHOW COLUMNS FROM sertifikat LIKE 'keputusan_sertifikasi_id';"

# Migration 3: Change FK strategies to RESTRICT
php artisan migrate --path=/database/migrations/2026_01_22_030000_change_fk_strategy_to_restrict.php

# VERIFY: Check FK constraints
mysql -uroot -p'nw3wGgZYE.dFX65' certipro_lsp -e "
  SELECT CONSTRAINT_NAME, DELETE_RULE 
  FROM information_schema.REFERENTIAL_CONSTRAINTS 
  WHERE CONSTRAINT_SCHEMA = 'certipro_lsp' 
    AND TABLE_NAME IN ('keputusan_sertifikasi', 'sertifikat');
"

# Migration 4: Add performance indexes
php artisan migrate --path=/database/migrations/2026_01_22_040000_add_performance_indexes.php

# VERIFY: Check indexes
mysql -uroot -p'nw3wGgZYE.dFX65' certipro_lsp -e "SHOW INDEX FROM sertifikat;"

# Clear all caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Disable maintenance mode
php artisan up
```

### Step 4: Verify Deployment

```bash
# Test certificate issuance flow
# 1. Check if keputusan exists
# 2. Try to issue certificate
# 3. Verify FK constraint works

# Check database constraints
mysql -uroot -p'nw3wGgZYE.dFX65' certipro_lsp -e "
  SELECT 
    TABLE_NAME,
    CONSTRAINT_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    DELETE_RULE
  FROM information_schema.KEY_COLUMN_USAGE
  JOIN information_schema.REFERENTIAL_CONSTRAINTS USING (CONSTRAINT_NAME, CONSTRAINT_SCHEMA)
  WHERE CONSTRAINT_SCHEMA = 'certipro_lsp'
    AND TABLE_NAME IN ('pendaftaran_sertifikasi', 'asesmen', 'keputusan_sertifikasi', 'sertifikat')
  ORDER BY TABLE_NAME, CONSTRAINT_NAME;
"

# Check performance indexes
mysql -uroot -p'nw3wGgZYE.dFX65' certipro_lsp -e "
  SELECT 
    TABLE_NAME,
    INDEX_NAME,
    COLUMN_NAME,
    SEQ_IN_INDEX
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = 'certipro_lsp'
    AND TABLE_NAME IN ('pendaftaran_sertifikasi', 'asesmen', 'keputusan_sertifikasi', 'sertifikat')
    AND INDEX_NAME LIKE 'idx_%'
  ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;
"
```

---

## 🧪 POST-DEPLOYMENT TESTING

### Test 1: Certificate Issuance Validation

```bash
# SSH to production
ssh root@76.13.18.166
cd /var/www/lsp-ui.ibnuapps.cloud/current

# Test: Try to issue certificate without keputusan (should fail)
php artisan tinker
```

```php
use App\Models\PendaftaranSertifikasi;
use App\Services\SertifikatService;

$service = app(SertifikatService::class);

// Find pendaftaran without keputusan
$pendaftaran = PendaftaranSertifikasi::whereDoesntHave('keputusan')->first();

if ($pendaftaran) {
    // This should fail with validation error
    $result = $service->terbitkan($pendaftaran);
    
    // Expected: $result['success'] = false
    // Expected: $result['error'] contains "keputusan tidak ditemukan"
    dump($result);
}
```

### Test 2: FK Constraint Protection

```bash
mysql -uroot -p'nw3wGgZYE.dFX65' certipro_lsp

-- Test: Try to delete user with keputusan (should fail)
DELETE FROM users WHERE id = (
  SELECT ditetapkan_oleh FROM keputusan_sertifikasi LIMIT 1
);
-- Expected: ERROR 1451 - Cannot delete or update a parent row

-- Test: Try to delete pendaftaran with certificate (should fail)
DELETE FROM pendaftaran_sertifikasi WHERE id = (
  SELECT pendaftaran_id FROM sertifikat LIMIT 1
);
-- Expected: ERROR 1451 - Cannot delete or update a parent row
```

### Test 3: Performance Improvement

```bash
# Before: Dashboard query without index
EXPLAIN SELECT * FROM pendaftaran_sertifikasi 
WHERE status = 'diajukan' 
ORDER BY tanggal_daftar DESC 
LIMIT 20;
-- Check: Should use idx_pendaftaran_dashboard

# Certificate search query
EXPLAIN SELECT * FROM sertifikat 
WHERE tanggal_berlaku_sampai >= CURDATE() 
ORDER BY tanggal_terbit DESC;
-- Check: Should use idx_sertifikat_validity
```

---

## 🔄 ROLLBACK PROCEDURE (If Needed)

### Emergency Rollback

```bash
ssh root@76.13.18.166
cd /var/www/lsp-ui.ibnuapps.cloud/current

# Enable maintenance mode
php artisan down

# Rollback migrations (REVERSE ORDER!)
php artisan migrate:rollback --step=1  # Rollback indexes
php artisan migrate:rollback --step=1  # Rollback FK strategies
php artisan migrate:rollback --step=1  # Rollback keputusan_id
php artisan migrate:rollback --step=1  # Rollback nullable fix

# Clear caches
php artisan optimize:clear

# Restore from backup if needed
mysql -uroot -p'nw3wGgZYE.dFX65' certipro_lsp < /root/backups/YYYYMMDD/certipro_lsp_pre_integrity_HHMMSS.sql

# Disable maintenance mode
php artisan up
```

---

## 📊 MONITORING & MAINTENANCE

### Daily Health Checks

```bash
# Add to crontab (run daily at 2 AM)
0 2 * * * cd /var/www/lsp-ui.ibnuapps.cloud/current && php artisan certipro:detect-orphans --export=json

# Check logs
cat storage/app/orphan-detection-*.json
```

### Weekly Cleanup (Optional)

```bash
# Add to crontab (run weekly on Sunday at 3 AM)
0 3 * * 0 cd /var/www/lsp-ui.ibnuapps.cloud/current && php artisan certipro:cleanup-orphans --dry-run > /var/log/certipro-cleanup.log
```

### Monthly Reports

```sql
-- FK constraint violations report
SELECT 
  'pendaftaran_null_fk' as issue,
  COUNT(*) as count
FROM pendaftaran_sertifikasi 
WHERE user_id IS NULL OR skema_sertifikasi_id IS NULL

UNION ALL

SELECT 
  'certs_without_keputusan',
  COUNT(*)
FROM sertifikat s
LEFT JOIN keputusan_sertifikasi k ON s.pendaftaran_id = k.pendaftaran_id
WHERE k.id IS NULL;
```

---

## ✅ SUCCESS CRITERIA

Deployment is successful if:

- ✅ All 4 migrations ran without errors
- ✅ `php artisan certipro:detect-orphans` shows 0 issues
- ✅ Certificate issuance validation works (rejects without keputusan)
- ✅ FK constraints prevent illegal deletions
- ✅ Dashboard loads 50%+ faster (check DevTools Network tab)
- ✅ No 500 errors in production logs for 24 hours

---

## 📞 SUPPORT & TROUBLESHOOTING

### Common Issues

**Issue 1: Migration fails with "Cannot delete or update a parent row"**
```bash
# Solution: Run cleanup first
php artisan certipro:cleanup-orphans
```

**Issue 2: "Duplicate entry" on unique constraint**
```bash
# Solution: Find duplicates
mysql -uroot -p certipro_lsp -e "
  SELECT pendaftaran_id, COUNT(*) 
  FROM sertifikat 
  GROUP BY pendaftaran_id 
  HAVING COUNT(*) > 1;
"

# Manually review and delete duplicates
```

**Issue 3: Performance regression after indexes**
```bash
# Solution: Analyze and optimize tables
mysql -uroot -p certipro_lsp -e "
  ANALYZE TABLE pendaftaran_sertifikasi, asesmen, keputusan_sertifikasi, sertifikat;
  OPTIMIZE TABLE pendaftaran_sertifikasi, asesmen, keputusan_sertifikasi, sertifikat;
"
```

---

## 📝 DEPLOYMENT LOG TEMPLATE

```
Date: 2026-01-22
Time: __:__ WIB
Engineer: _______________
Environment: Production (lsp-ui.ibnuapps.cloud)

Pre-Deployment:
[ ] Database backup created: /root/backups/YYYYMMDD/certipro_lsp_pre_integrity_HHMMSS.sql
[ ] Orphan detection completed: ___ issues found
[ ] Cleanup completed: ___ records deleted

Deployment:
[ ] Files uploaded to production
[ ] Migration 1 (fix_pendaftaran_nullable_fk): SUCCESS / FAILED
[ ] Migration 2 (add_keputusan_id_to_sertifikat): SUCCESS / FAILED
[ ] Migration 3 (change_fk_strategy_to_restrict): SUCCESS / FAILED
[ ] Migration 4 (add_performance_indexes): SUCCESS / FAILED
[ ] Caches cleared

Post-Deployment:
[ ] Certificate issuance validation: PASSED / FAILED
[ ] FK constraint protection: PASSED / FAILED
[ ] Performance improvement: ___% faster
[ ] No 500 errors in logs

Status: SUCCESS / ROLLBACK REQUIRED
Notes: _________________________________________________________________
```

---

**Version:** 1.0  
**Last Updated:** January 22, 2026  
**Maintained By:** DevOps Team

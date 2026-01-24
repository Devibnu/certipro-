# 🚀 STATE MACHINE IMPLEMENTATION - DEPLOYMENT GUIDE

## ✅ WHAT HAS BEEN CREATED

### 1. **ENUMS** (PHP 8.1+ Type-Safe Status)
- ✅ `app/Enums/PraPendaftaranStatus.php`
- ✅ `app/Enums/PendaftaranStatus.php`
- ✅ `app/Enums/AsesmenStatus.php`
- ✅ `app/Enums/KeputusanStatus.php`
- ✅ `app/Enums/SertifikatStatus.php`

**Features:**
- Type-safe status values
- Built-in transition validation (`canTransitionTo()`)
- UI helpers (badge(), icon(), label(), color())
- Terminal state detection
- Progress tracking

### 2. **EXCEPTION HANDLING**
- ✅ `app/Exceptions/StateTransitionException.php`

**Features:**
- Static factories for common errors:
  - `invalidTransition()`
  - `missingRequirement()`
  - `entityLocked()`
  - `duplicateEntity()`
- Auto-logging to monitoring system
- JSON/HTML response support
- User-friendly error messages

### 3. **SERVICE LAYER** (Business Logic)
- ✅ `app/Services/StateTransitionService.php`

**Features:**
- `transitionPraPendaftaran()` - Manage pra-pendaftaran status
- `transitionPendaftaran()` - Manage pendaftaran status
- `transitionAsesmen()` - Manage asesmen status
- `decideKeputusan()` - Manage keputusan (KOMPETEN/BELUM_KOMPETEN)
- `createAsesmen()` - Create asesmen from locked pendaftaran
- `createKeputusan()` - Create keputusan from completed asesmen
- `autoIssueSertifikat()` - Auto-issue sertifikat when KOMPETEN
- `revokeSertifikat()` - Admin revocation with optional unlock
- `lockDataAfterIssuance()` - Lock all related data
- `guardNotLocked()` - Prevent modification of locked data
- `generateNomorSertifikat()` - Thread-safe certificate number generation

**Guards (Validation):**
- `guardPendaftaranSubmit()` - Check completeness before submit
- `guardPendaftaranLock()` - Check jadwal before locking
- `guardAsesmenStart()` - Check schedule and asesor
- `guardAsesmenComplete()` - Check checklist and evidence

### 4. **DATABASE MIGRATION**
- ✅ `database/migrations/2026_01_23_150000_add_state_machine_constraints.php`

**Changes:**
- **pra_pendaftaran**: `is_processed`, `processed_at`, `catatan_admin`, indexes
- **pendaftaran_sertifikasi**: `is_locked`, `locked_at`, `locked_reason`, `status_updated_at`, `catatan_admin`, UNIQUE(pra_pendaftaran_id)
- **asesmen**: `is_locked`, `locked_at`, `started_at`, `completed_at`, UNIQUE(pendaftaran_sertifikasi_id)
- **keputusan_sertifikasi**: `is_locked`, `locked_at`, `decided_at`, `decided_by`, `catatan`, UNIQUE(asesmen_id)
- **sertifikat**: `revoked_at`, `revoked_by`, `revoked_reason`, UNIQUE(keputusan_sertifikasi_id)

**Data Integrity Check:**
- Detects orphaned records
- Detects duplicate relationships
- Warns before applying constraints
- Provides cleanup recommendations

### 5. **UI COMPONENTS** (Blade)
- ✅ `resources/views/components/state-guard-button.blade.php`
- ✅ `resources/views/components/status-badge.blade.php`
- ✅ `resources/views/errors/state-transition.blade.php`

**Features:**
- **state-guard-button**: 
  - Automatic disable when action not allowed
  - Tooltip showing reason why disabled
  - Confirmation dialog support
  - Icon support
  - TailwindCSS styled
  
- **status-badge**: 
  - Auto-detect enum type
  - Color-coded badges
  - Size variants (sm, md, lg)
  - Icon display
  
- **state-transition error page**:
  - User-friendly error display
  - Transition info (from → to)
  - Context details
  - Action recommendations
  - Debug mode for development

### 6. **EXAMPLES**
- ✅ `EXAMPLE_CONTROLLER_STATE_MACHINE.php`
- ✅ `EXAMPLE_VIEW_STATE_MACHINE.blade.php`

---

## 🔧 DEPLOYMENT CHECKLIST

### STEP 1: PRE-DEPLOYMENT CHECKS

```bash
# 1. Check PHP version (must be 8.1+)
php -v

# 2. Check Laravel version
php artisan --version

# 3. Verify composer dependencies
composer check-platform-reqs

# 4. Run existing tests
php artisan test
```

### STEP 2: DATA CLEANUP (CRITICAL!)

Before running migration, clean orphaned data:

```bash
# Check for orphaned pendaftaran
php artisan tinker
>>> DB::table('pendaftaran_sertifikasi')->whereNull('user_id')->whereNotIn('status', ['draft', 'ditolak'])->get();

# Option A: Set to draft (safe)
>>> DB::table('pendaftaran_sertifikasi')->whereIn('id', [1,2])->update(['status' => 'draft']);

# Option B: Delete if test data (destructive)
>>> DB::table('pendaftaran_sertifikasi')->whereIn('id', [1,2])->delete();
```

**Check for duplicates:**

```sql
-- Check duplicate pra_pendaftaran_id
SELECT pra_pendaftaran_id, COUNT(*) as count 
FROM pendaftaran_sertifikasi 
WHERE pra_pendaftaran_id IS NOT NULL 
GROUP BY pra_pendaftaran_id 
HAVING count > 1;

-- Check duplicate pendaftaran in asesmen
SELECT pendaftaran_sertifikasi_id, COUNT(*) as count 
FROM asesmen 
GROUP BY pendaftaran_sertifikasi_id 
HAVING count > 1;

-- Check duplicate asesmen in keputusan
SELECT asesmen_id, COUNT(*) as count 
FROM keputusan_sertifikasi 
GROUP BY asesmen_id 
HAVING count > 1;

-- Check duplicate keputusan in sertifikat
SELECT keputusan_sertifikasi_id, COUNT(*) as count 
FROM sertifikat 
WHERE keputusan_sertifikasi_id IS NOT NULL
GROUP BY keputusan_sertifikasi_id 
HAVING count > 1;
```

If duplicates found, decide which to keep and delete others.

### STEP 3: BACKUP DATABASE

```bash
# Production backup
mysqldump -u root -p certipro_lsp > backup_before_state_machine_$(date +%Y%m%d_%H%M%S).sql

# Or via artisan (if using backup package)
php artisan backup:run
```

### STEP 4: RUN MIGRATION

```bash
# Dry run (check for issues)
php artisan migrate --pretend

# Actual migration
php artisan migrate

# Check migration status
php artisan migrate:status
```

**Expected Output:**
```
✅ Data integrity check passed
✅ Indexes created
✅ UNIQUE constraints applied
✅ Lock columns added
✅ Migration completed
```

**If migration fails:**
```bash
# Rollback
php artisan migrate:rollback --step=1

# Fix the issue (clean data)
# Re-run migration
php artisan migrate
```

### STEP 5: UPDATE MODELS

Add to your Eloquent models:

```php
// app/Models/PendaftaranSertifikasi.php

use App\Enums\PendaftaranStatus;

class PendaftaranSertifikasi extends Model
{
    protected $table = 'pendaftaran_sertifikasi';
    
    protected $fillable = [
        // ... existing fields
        'is_locked',
        'locked_at',
        'locked_reason',
        'status_updated_at',
        'catatan_admin',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
        'status_updated_at' => 'datetime',
        'tanggal_lahir' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Accessor for enum
    public function getStatusEnumAttribute(): PendaftaranStatus
    {
        return PendaftaranStatus::from($this->status);
    }

    // Accessor for label
    public function getStatusLabelAttribute(): string
    {
        return $this->status_enum->label();
    }

    // Check if locked
    public function isLocked(): bool
    {
        return $this->is_locked ?? false;
    }
}
```

Do the same for:
- `PraPendaftaran` → `PraPendaftaranStatus`
- `Asesmen` → `AsesmenStatus`
- `KeputusanSertifikasi` → `KeputusanStatus`
- `Sertifikat` → `SertifikatStatus`

### STEP 6: UPDATE CONTROLLERS

Replace manual status changes with StateTransitionService:

**Before (❌ Unsafe):**
```php
$pendaftaran->update(['status' => 'diajukan']);
```

**After (✅ Safe):**
```php
use App\Services\StateTransitionService;
use App\Enums\PendaftaranStatus;

public function __construct(
    private StateTransitionService $stateService
) {}

public function submit(PendaftaranSertifikasi $pendaftaran)
{
    try {
        $this->stateService->transitionPendaftaran(
            $pendaftaran,
            PendaftaranStatus::DIAJUKAN
        );
        return redirect()->back()->with('success', 'Berhasil diajukan');
    } catch (StateTransitionException $e) {
        return back()->with('error', $e->getMessage());
    }
}
```

### STEP 7: UPDATE VIEWS

Replace hardcoded buttons with state-guard-button:

**Before (❌ No validation):**
```blade
<button type="submit">Ajukan</button>
```

**After (✅ State-aware):**
```blade
<x-state-guard-button
    :can-perform="$guards['can_submit']['can']"
    :reason="$guards['can_submit']['reason']"
    :route="route('pendaftaran.submit', $pendaftaran)"
    method="POST"
    confirm-message="Yakin mengajukan pendaftaran?"
    icon="📤"
>
    Ajukan Pendaftaran
</x-state-guard-button>
```

### STEP 8: REGISTER SERVICE PROVIDER (Optional)

If not using auto-discovery:

```php
// config/app.php

'providers' => [
    // ...
    App\Services\StateTransitionService::class,
],
```

Or bind in AppServiceProvider:

```php
// app/Providers/AppServiceProvider.php

use App\Services\StateTransitionService;

public function register()
{
    $this->app->singleton(StateTransitionService::class);
}
```

### STEP 9: CONFIGURE ACTIVITY LOG (Optional)

If using `spatie/laravel-activitylog`:

```bash
composer require spatie/laravel-activitylog
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
php artisan migrate
```

Update models:

```php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PendaftaranSertifikasi extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'is_locked', 'catatan_admin'])
            ->logOnlyDirty();
    }
}
```

### STEP 10: RUN TESTS

Create tests directory if not exists:

```bash
mkdir -p tests/Feature/StateTransition
```

Run example tests:

```bash
php artisan test --filter=StateTransitionTest
```

**Expected:** All tests pass ✅

### STEP 11: DEPLOY TO PRODUCTION

```bash
# 1. Pull latest code
git pull origin main

# 2. Install dependencies
composer install --optimize-autoloader --no-dev

# 3. Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# 4. Run migration
php artisan migrate --force

# 5. Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Restart workers (if using queue)
php artisan queue:restart

# 7. Restart PHP-FPM
sudo systemctl restart php8.3-fpm
```

### STEP 12: POST-DEPLOYMENT MONITORING

```bash
# Monitor logs for StateTransitionException
tail -f storage/logs/laravel.log | grep StateTransition

# Check activity log
php artisan tinker
>>> \Spatie\Activitylog\Models\Activity::latest()->limit(10)->get(['description', 'subject_type', 'created_at']);

# Monitor database locks (if issues)
SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCKS;
SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCK_WAITS;
```

**What to watch:**
- ✅ No StateTransitionException in production logs (first 24 hours)
- ✅ All transitions working smoothly
- ✅ No orphaned data created
- ✅ Performance acceptable (pessimistic locks not blocking too long)

---

## 🧪 TESTING GUIDE

### Manual Testing Checklist

#### Test 1: Happy Path (Full Flow)
1. ✅ Create Pra-Pendaftaran (status: diajukan)
2. ✅ Admin approve → status: diterima
3. ✅ User create Pendaftaran (status: draft)
4. ✅ User submit → status: diajukan
5. ✅ Admin verify → status: diverifikasi
6. ✅ Admin lock → status: dikunci + create Asesmen
7. ✅ Asesor start → status: dalam_proses
8. ✅ Asesor complete → status: selesai + create Keputusan
9. ✅ Komite decide KOMPETEN → auto-issue Sertifikat
10. ✅ Verify all data locked (pendaftaran, asesmen, keputusan)

#### Test 2: Rejection Path
1. ✅ Create Pendaftaran (draft)
2. ✅ User submit (diajukan)
3. ✅ Admin reject → status: ditolak
4. ✅ Verify cannot transition from ditolak

#### Test 3: Belum Kompeten Path
1. ✅ Complete asesmen
2. ✅ Komite decide BELUM_KOMPETEN
3. ✅ Verify NO sertifikat created
4. ✅ Verify keputusan locked

#### Test 4: Lock Mechanism
1. ✅ Try to edit locked pendaftaran → BLOCKED
2. ✅ Try to change status of locked asesmen → BLOCKED
3. ✅ Try to re-decide keputusan → BLOCKED

#### Test 5: Missing Requirements
1. ✅ Try to submit pendaftaran without skema → BLOCKED
2. ✅ Try to lock pendaftaran without jadwal → BLOCKED
3. ✅ Try to complete asesmen without checklist → BLOCKED

#### Test 6: Duplicate Prevention
1. ✅ Try to create 2nd pendaftaran from same pra_pendaftaran → BLOCKED (UNIQUE constraint)
2. ✅ Try to create 2nd asesmen from same pendaftaran → BLOCKED
3. ✅ Try to create 2nd keputusan from same asesmen → BLOCKED

#### Test 7: Revocation
1. ✅ Issue sertifikat (KOMPETEN)
2. ✅ Admin revoke with reason → status: dicabut
3. ✅ Optionally unlock data for correction

---

## 📊 PERFORMANCE CONSIDERATIONS

### Pessimistic Locks
StateTransitionService uses `lockForUpdate()` for:
- Sertifikat number generation (prevent race condition)

**Impact:**
- Slightly slower under heavy load
- Prevents duplicate certificate numbers

**Monitoring:**
```php
// Check for long-running locks
SELECT * FROM INFORMATION_SCHEMA.INNODB_LOCK_WAITS WHERE waiting_trx_started < NOW() - INTERVAL 10 SECOND;
```

### Database Indexes
Migration adds indexes on:
- `status` columns (all tables)
- `is_locked` columns
- `user_id`
- `tanggal_berlaku_sampai` (for cron jobs)

**Verify indexes:**
```sql
SHOW INDEX FROM pendaftaran_sertifikasi;
SHOW INDEX FROM asesmen;
SHOW INDEX FROM keputusan_sertifikasi;
SHOW INDEX FROM sertifikat;
```

---

## 🔐 SECURITY CONSIDERATIONS

### 1. Permission Checks
Always use Laravel Policies or Gates:

```php
$this->authorize('verify-pendaftaran');
$this->authorize('lock-pendaftaran');
$this->authorize('decide-keputusan');
$this->authorize('revoke-sertifikat');
```

### 2. Admin Revocation
Only specific admins should be able to revoke:

```php
// In Policy
public function revoke(User $user, Sertifikat $sertifikat): bool
{
    return $user->hasRole('super-admin') || 
           $user->hasPermission('revoke-certificate');
}
```

### 3. Audit Trail
All state transitions are logged via `spatie/laravel-activitylog`:

```php
activity()
    ->performedOn($pendaftaran)
    ->withProperties(['old' => $old, 'new' => $new])
    ->log('Status changed');
```

---

## 📚 DOCUMENTATION

### For Developers
- Read: `STATE_MACHINE_ARCHITECTURE.md` (comprehensive flow diagram + rules)
- Read: `EXAMPLE_CONTROLLER_STATE_MACHINE.php`
- Read: `EXAMPLE_VIEW_STATE_MACHINE.blade.php`

### For Users
- Create user guide explaining status meanings
- Create flowchart poster for office
- Train staff on new locked data policy

---

## ⚠️ ROLLBACK PLAN

If something goes wrong:

```bash
# 1. Rollback migration
php artisan migrate:rollback --step=1

# 2. Restore database from backup
mysql -u root -p certipro_lsp < backup_before_state_machine_YYYYMMDD_HHMMSS.sql

# 3. Revert code
git revert <commit-hash>

# 4. Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 5. Restart PHP-FPM
sudo systemctl restart php8.3-fpm
```

---

## ✅ SUCCESS CRITERIA

After 1 week in production:

- ✅ Zero StateTransitionException in production logs
- ✅ No orphaned data created
- ✅ No duplicate sertifikat numbers
- ✅ All transitions respect business rules
- ✅ User feedback: "System prevents mistakes"
- ✅ Admin can revoke if needed (with proper reason)
- ✅ Activity log shows complete audit trail

---

## 🎉 BENEFITS ACHIEVED

1. **Data Integrity**: UNIQUE constraints prevent duplicates
2. **Business Rule Enforcement**: Guards prevent invalid transitions
3. **Audit Compliance**: ISO 17024 / BNSP compliant
4. **User Experience**: Clear feedback, no confusion
5. **Security**: Locked data cannot be tampered
6. **Maintainability**: Centralized business logic in Service Layer
7. **Type Safety**: PHP 8.1+ Enums prevent typos
8. **Testability**: Easy to test state transitions

---

**End of Deployment Guide**  
*Generated: 2026-01-23*  
*Version: 1.0*

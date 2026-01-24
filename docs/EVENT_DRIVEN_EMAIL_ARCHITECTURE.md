# EVENT-DRIVEN EMAIL ARCHITECTURE - IMPLEMENTATION GUIDE

**Status:** ✅ Implemented & Ready for Deployment  
**Date:** 2024-01-XX  
**Compliance:** BNSP, ISO 17024, EMAIL_STATUS_MATRIX.md  

---

## 📋 OVERVIEW

Sistem email notification telah di-refactor dari **controller-based (sync)** menjadi **event-driven (async)** architecture dengan queue support.

### Keuntungan Architecture Baru:

✅ **Decoupling** - Controllers tidak perlu tahu tentang email  
✅ **Queue-based** - Email dikirim async (tidak block request)  
✅ **Retry Mechanism** - 3x retry dengan exponential backoff  
✅ **Idempotent** - Email tidak dikirim berkali-kali untuk status yang sama  
✅ **Audit Logging** - Setiap email dicatat untuk compliance  
✅ **Scalable** - Multiple queue workers dapat dijalankan  

---

## 🏗️ ARCHITECTURE DIAGRAM

```
Controller / Service
       │
       ▼
  Fire Event() ─────────────┐
       │                     │
       ▼                     │
   Event Class               │
       │                     │ (Sync dispatch)
       ▼                     │
EventServiceProvider         │
       │                     │
       ▼                     │
   Listener ←────────────────┘
       │
       │ (ShouldQueue = async)
       ▼
   Queue Job
       │
       ▼
EmailNotificationService
       │
       ├─→ Idempotent Check (status_email field)
       │
       ├─→ Mail::send()
       │
       └─→ Audit Log + Update status_email
```

---

## 📦 FILES CREATED/MODIFIED

### ✨ New Files (10 files)

**Events (5 files):**
```
app/Events/
├── PraPendaftaranDiterimaEvent.php
├── PraPendaftaranDitolakEvent.php
├── KeputusanKompetenEvent.php
├── KeputusanBelumKompetenEvent.php
└── SertifikatTerbitEvent.php (reserved for future)
```

**Listeners (5 files):**
```
app/Listeners/
├── SendPraPendaftaranDiterimaEmail.php
├── SendPraPendaftaranDitolakEmail.php
├── SendKeputusanKompetenEmail.php
├── SendKeputusanBelumKompetenEmail.php
└── SendSertifikatTerbitEmail.php (reserved for future)
```

### 🔧 Modified Files (4 files)

1. **app/Services/EmailNotificationService.php**
   - Added: `sendPraDiterimaFromEvent()`
   - Added: `sendPraDitolakFromEvent()`
   - Added: `sendKompetenFromEvent()`
   - Added: `sendBelumKompetenFromEvent()`

2. **app/Providers/EventServiceProvider.php**
   - Registered 5 Event → Listener mappings

3. **app/Services/PraPendaftaranNotificationService.php**
   - Refactored: `sendAcceptedNotification()` → Fire event
   - Refactored: `sendRejectedNotification()` → Fire event

4. **app/Http/Controllers/AdminUI/KeputusanSertifikasiController.php**
   - Refactored: `simpan()` → Fire event (Kompeten/BelumKompeten)

---

## 🔥 EVENT → LISTENER MAPPING

| Event | Listener | Triggered By |
|-------|----------|--------------|
| `PraPendaftaranDiterimaEvent` | `SendPraPendaftaranDiterimaEmail` | PraPendaftaran status → 'diterima' |
| `PraPendaftaranDitolakEvent` | `SendPraPendaftaranDitolakEmail` | PraPendaftaran status → 'ditolak' |
| `KeputusanKompetenEvent` | `SendKeputusanKompetenEmail` | Keputusan → 'kompeten' |
| `KeputusanBelumKompetenEvent` | `SendKeputusanBelumKompetenEmail` | Keputusan → 'belum_kompeten' |
| `SertifikatTerbitEvent` | `SendSertifikatTerbitEmail` | ⚠️ Reserved (belum diimplementasi) |

---

## 🛠️ DEPLOYMENT STEPS

### 1️⃣ Upload Files ke Production

```bash
# SSH ke server
ssh root@76.13.18.166

# Navigate to project
cd /var/www/lsp-ui.ibnuapps.cloud/current

# Pull latest changes (jika via Git)
git pull origin main

# Or upload manually via FTP/SCP:
# - app/Events/
# - app/Listeners/
# - app/Services/EmailNotificationService.php (updated)
# - app/Providers/EventServiceProvider.php (updated)
# - app/Services/PraPendaftaranNotificationService.php (updated)
# - app/Http/Controllers/AdminUI/KeputusanSertifikasiController.php (updated)
```

### 2️⃣ Clear Caches

```bash
php artisan event:cache
php artisan config:cache
php artisan route:cache
php artisan view:clear
```

### 3️⃣ Restart Queue Workers

```bash
php artisan queue:restart

# Or restart supervisor (if using supervisor)
sudo supervisorctl restart laravel-queue-worker:*
```

### 4️⃣ Test Event Dispatching

```bash
# SSH ke server
php artisan tinker

# Test PraDiterima Event
>>> $pra = App\Models\PraPendaftaran::first();
>>> event(new App\Events\PraPendaftaranDiterimaEvent($pra));

# Check queue (process 1 job)
>>> exit
php artisan queue:work --once

# Check logs
tail -f storage/logs/laravel.log | grep "Listener"
```

### 5️⃣ Monitor Queue (Production)

```bash
# Run queue worker in background (if not using supervisor)
nohup php artisan queue:work --tries=3 --timeout=60 &

# Check queue status
php artisan queue:monitor

# Check failed jobs
php artisan queue:failed
```

---

## ⚙️ QUEUE CONFIGURATION

### database/migrations/*_create_jobs_table.php

Queue menggunakan **database driver** (sudah configured).

### config/queue.php

```php
'default' => env('QUEUE_CONNECTION', 'database'),

'connections' => [
    'database' => [
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90, // Timeout untuk job yang stuck
    ],
],
```

### Listener Retry Policy

Setiap Listener implement:

```php
public $tries = 3;                      // Max 3 attempts
public $timeout = 60;                   // 60 seconds per attempt
public $backoff = [30, 60, 120];        // Backoff: 30s, 1min, 2min
```

---

## 🔒 IDEMPOTENT MECHANISM

Email **TIDAK akan dikirim berkali-kali** untuk status yang sama.

### Metode Idempotent:

**Option 1: Field `status_email` di table** (CURRENTLY USED)

```php
// Check if already sent
if ($praPendaftaran->status_email === 'sent') {
    Log::warning('Email already sent, skipping');
    return false;
}

// Send email
Mail::to($praPendaftaran->email)->send(new PraPendaftaranDiterimaMail());

// Mark as sent
$praPendaftaran->update(['status_email' => 'sent']);
```

**Option 2: Dedicated `email_notification_logs` table** (OPTIONAL)

```sql
CREATE TABLE email_notification_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    event_type VARCHAR(100),
    entity_id BIGINT,
    recipient VARCHAR(255),
    status ENUM('success', 'failed'),
    sent_at TIMESTAMP,
    UNIQUE KEY unique_notification (event_type, entity_id)
);
```

---

## 📊 MONITORING & LOGGING

### Log Format (Standard)

```log
[2024-01-XX 15:30:45] production.INFO: [Listener] Processing PraPendaftaranDiterimaEvent {"pra_id":123,"email":"user@example.com","attempt":1}
[2024-01-XX 15:30:46] production.INFO: [EmailService] PraDiterima email sent successfully {"pra_id":123,"email":"user@example.com"}
```

### Failed Job Logs

```log
[2024-01-XX 15:35:00] production.CRITICAL: [Listener] PraPendaftaranDiterimaEmail failed after max retries {"pra_id":123,"error":"Connection timeout"}
```

### Check Failed Jobs

```bash
# List all failed jobs
php artisan queue:failed

# Retry specific job
php artisan queue:retry <job-id>

# Retry all failed jobs
php artisan queue:retry all

# Flush all failed jobs
php artisan queue:flush
```

---

## 🚨 TROUBLESHOOTING

### Issue 1: Email Tidak Terkirim

**Symptoms:** Event fired, tapi email tidak sampai

**Check:**
```bash
# 1. Check queue jobs table
mysql> SELECT * FROM jobs ORDER BY id DESC LIMIT 10;

# 2. Check failed_jobs table
mysql> SELECT * FROM failed_jobs ORDER BY failed_at DESC LIMIT 10;

# 3. Check logs
tail -f storage/logs/laravel.log | grep -E "Listener|EmailService"

# 4. Process queue manually
php artisan queue:work --once -vvv
```

**Solution:**
- Pastikan queue worker running: `ps aux | grep "queue:work"`
- Restart queue: `php artisan queue:restart`
- Check SMTP credentials di `.env`

---

### Issue 2: Duplicate Email (Email Terkirim Berkali-kali)

**Symptoms:** User menerima email yang sama berulang kali

**Check:**
```bash
# Check idempotent field
mysql> SELECT id, email, status_email FROM pra_pendaftaran WHERE id = 123;

# Check email logs
tail -f storage/logs/laravel.log | grep "Email already sent"
```

**Solution:**
- Pastikan `status_email` field ada di table
- Verify idempotent logic di `EmailNotificationService`
- Add migration jika field tidak ada:

```php
Schema::table('pra_pendaftaran', function (Blueprint $table) {
    $table->string('status_email')->nullable()->after('status');
});
```

---

### Issue 3: Queue Worker Mati/Stuck

**Symptoms:** Jobs menumpuk di `jobs` table, tidak diproses

**Check:**
```bash
# Check if queue worker running
ps aux | grep "queue:work"

# Check supervisor status (if using supervisor)
sudo supervisorctl status
```

**Solution:**

**Option A: Manual Queue Worker**
```bash
nohup php artisan queue:work --tries=3 --timeout=60 > /dev/null 2>&1 &
```

**Option B: Supervisor (Recommended)**

Create `/etc/supervisor/conf.d/laravel-queue.conf`:

```ini
[program:laravel-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/lsp-ui.ibnuapps.cloud/current/artisan queue:work --tries=3 --timeout=60
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/lsp-ui.ibnuapps.cloud/current/storage/logs/queue-worker.log
stopwaitsecs=3600
```

Reload supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-queue-worker:*
```

---

## 📝 TESTING CHECKLIST

### Manual Testing

- [ ] **Test PraDiterima Email**
  ```bash
  php artisan tinker
  >>> $pra = \App\Models\PraPendaftaran::find(1);
  >>> $pra->update(['status' => 'diterima']);
  # Check inbox untuk email
  ```

- [ ] **Test PraDitolak Email**
  ```bash
  >>> $pra = \App\Models\PraPendaftaran::find(2);
  >>> $pra->update(['status' => 'ditolak', 'alasan_penolakan' => 'Test']);
  ```

- [ ] **Test Keputusan Kompeten**
  ```bash
  # Melalui AdminUI:
  # 1. Login sebagai admin
  # 2. Navigate ke Keputusan Sertifikasi
  # 3. Simpan keputusan "KOMPETEN"
  # 4. Check inbox pendaftar
  ```

- [ ] **Test Idempotent (Email Tidak Duplikat)**
  ```bash
  # Fire event 2x untuk entity yang sama
  >>> event(new \App\Events\PraPendaftaranDiterimaEvent($pra));
  >>> event(new \App\Events\PraPendaftaranDiterimaEvent($pra));
  # Hanya 1 email yang terkirim
  ```

- [ ] **Test Retry Mechanism**
  ```bash
  # Matikan SMTP di .env (simulate failure)
  MAIL_HOST=invalid-host
  
  # Fire event
  >>> event(new \App\Events\PraPendaftaranDiterimaEvent($pra));
  
  # Check failed_jobs table
  mysql> SELECT * FROM failed_jobs;
  
  # Fix SMTP config, retry
  php artisan queue:retry all
  ```

### Automated Testing (Future)

```php
// tests/Feature/EmailEventTest.php
public function test_pra_diterima_event_sends_email()
{
    Mail::fake();
    
    $pra = PraPendaftaran::factory()->create(['status' => 'diterima']);
    event(new PraPendaftaranDiterimaEvent($pra));
    
    Mail::assertSent(PraPendaftaranDiterimaMail::class);
}
```

---

## 🔗 RELATED DOCUMENTATION

- [EMAIL_STATUS_MATRIX.md](./EMAIL_STATUS_MATRIX.md) - Matrix status vs email decision
- [EMAIL_MATRIX_QUICK_REF.md](./EMAIL_MATRIX_QUICK_REF.md) - Quick reference guide
- [RBAC_SYSTEM.md](./RBAC_SYSTEM.md) - Role-based access control

---

## 📈 FUTURE IMPROVEMENTS

### Phase 2: Advanced Features

1. **Email Queue Priority**
   ```php
   event(new PraPendaftaranDiterimaEvent($pra))->onQueue('high-priority');
   ```

2. **Email Notification Dashboard**
   - Total emails sent per type
   - Failed email rate
   - Average delivery time
   - Retry statistics

3. **SertifikatTerbitEvent Implementation**
   - Create `SertifikatTerbitMail` Mailable
   - Create email template
   - Implement in SertifikatController

4. **WhatsApp Integration**
   - Queue-based WhatsApp notification
   - Parallel with email (fire 2 events)

5. **Email Rate Limiting**
   ```php
   RateLimiter::for('email', function ($job) {
       return Limit::perMinute(60); // Max 60 emails/min
   });
   ```

---

## ✅ DEPLOYMENT CHECKLIST

- [ ] All files uploaded to production
- [ ] Event cache cleared (`php artisan event:cache`)
- [ ] Config cache cleared (`php artisan config:cache`)
- [ ] Queue workers restarted
- [ ] Test 1 email dispatch manually
- [ ] Monitor logs for errors
- [ ] Check failed_jobs table (should be empty)
- [ ] Verify idempotent behavior (no duplicate emails)
- [ ] Document deployment in audit log

---

**Last Updated:** 2024-01-XX  
**Maintainer:** Development Team  
**Compliance:** BNSP, ISO 17024

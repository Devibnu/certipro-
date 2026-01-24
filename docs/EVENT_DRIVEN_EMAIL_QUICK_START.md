# 🚀 EVENT-DRIVEN EMAIL - QUICK START GUIDE

**Status:** ✅ Ready for Production  
**Last Updated:** 2024-01-XX

---

## 📦 WHAT CHANGED?

| Before (OLD) | After (NEW) |
|-------------|-------------|
| `Mail::to()->send()` in controller | `event(new MyEvent())` in controller |
| Synchronous (blocks request) | Asynchronous (queued) |
| No retry mechanism | 3x retry with backoff |
| Easy to send duplicate emails | Idempotent (no duplicates) |
| Tight coupling | Decoupled architecture |

---

## 🔥 HOW TO TRIGGER EMAIL NOW?

### ❌ OLD WAY (Don't do this anymore)

```php
// In Controller - OLD (direct email sending)
Mail::to($user->email)->send(new PraPendaftaranDiterimaMail($data));
```

### ✅ NEW WAY (Use events)

```php
// In Controller - NEW (fire event, let listener handle)
event(new PraPendaftaranDiterimaEvent($praPendaftaran));

// Or with explicit dispatch()
PraPendaftaranDiterimaEvent::dispatch($praPendaftaran);
```

---

## 📋 AVAILABLE EVENTS

| Event | Trigger When | Email Sent |
|-------|--------------|------------|
| `PraPendaftaranDiterimaEvent` | Pra-Pendaftaran status = 'diterima' | ✅ PraPendaftaranDiterimaMail |
| `PraPendaftaranDitolakEvent` | Pra-Pendaftaran status = 'ditolak' | ✅ PraPendaftaranDitolakMail |
| `KeputusanKompetenEvent` | Keputusan = 'kompeten' | ✅ KompetenMail |
| `KeputusanBelumKompetenEvent` | Keputusan = 'belum_kompeten' | ✅ BelumKompetenMail |
| `SertifikatTerbitEvent` | Sertifikat approved | ⏳ Reserved for future |

---

## 🛠️ DEPLOYMENT (1-COMMAND)

```bash
# SSH to production
ssh root@76.13.18.166

# Navigate to project
cd /var/www/lsp-ui.ibnuapps.cloud/current

# Run deployment script
bash deploy-event-driven-email.sh
```

**Script does:**
✅ Run migration (add `status_email` field)  
✅ Clear caches (event, config, route, view)  
✅ Restart queue workers  
✅ Verify event registration  

---

## ✅ POST-DEPLOYMENT CHECKS

### 1. Check Queue Worker Running

```bash
ps aux | grep "queue:work"

# If not running, start it:
nohup php artisan queue:work --tries=3 --timeout=60 &

# Or with supervisor:
sudo supervisorctl start laravel-queue-worker:*
```

### 2. Test Event Manually

```bash
php artisan tinker

>>> $pra = App\Models\PraPendaftaran::first();
>>> event(new App\Events\PraPendaftaranDiterimaEvent($pra));
>>> exit

# Process queue
php artisan queue:work --once

# Check logs
tail -f storage/logs/laravel.log | grep "Listener"
```

### 3. Monitor Queue Jobs

```bash
# Check pending jobs
mysql -e "SELECT * FROM jobs ORDER BY id DESC LIMIT 10;"

# Check failed jobs
php artisan queue:failed

# Retry all failed
php artisan queue:retry all
```

---

## 🔒 IDEMPOTENT CHECK (NO DUPLICATE EMAILS)

Email **WILL NOT be sent twice** untuk status yang sama.

### How it works:

```php
// In EmailNotificationService
if ($praPendaftaran->status_email === 'sent') {
    Log::warning('Email already sent, skipping');
    return false; // Skip sending
}

// Send email
Mail::to($praPendaftaran->email)->send(...);

// Mark as sent
$praPendaftaran->update(['status_email' => 'sent']);
```

### Database Fields:

- `status_email`: `NULL` / `pending` / `sent` / `failed`
- `email_sent_at`: Timestamp saat email berhasil terkirim

---

## 🚨 TROUBLESHOOTING

### Problem 1: Email Tidak Terkirim

**Symptoms:** Event fired tapi email tidak sampai

**Check:**
```bash
# 1. Check if job in queue
mysql -e "SELECT * FROM jobs;"

# 2. Check failed jobs
php artisan queue:failed

# 3. Process queue manually
php artisan queue:work --once -vvv

# 4. Check logs
tail -f storage/logs/laravel.log
```

**Solution:**
- Restart queue: `php artisan queue:restart`
- Check SMTP config in `.env`
- Verify queue worker running

---

### Problem 2: Duplicate Emails

**Symptoms:** User menerima email yang sama berkali-kali

**Check:**
```bash
mysql -e "SELECT id, email, status_email FROM pra_pendaftaran WHERE id = 123;"
```

**Solution:**
- Run migration: `php artisan migrate`
- Verify idempotent logic in `EmailNotificationService`

---

### Problem 3: Queue Worker Mati

**Symptoms:** Jobs menumpuk di `jobs` table

**Solution:**

**Option A: Manual**
```bash
nohup php artisan queue:work --tries=3 --timeout=60 > /dev/null 2>&1 &
```

**Option B: Supervisor (Recommended)**
```bash
sudo supervisorctl status
sudo supervisorctl restart laravel-queue-worker:*
```

---

## 📊 MONITORING COMMANDS

```bash
# Watch logs in real-time
tail -f storage/logs/laravel.log | grep -E "Listener|EmailService|Event"

# Count pending jobs
mysql -e "SELECT COUNT(*) as pending FROM jobs;"

# Count failed jobs
mysql -e "SELECT COUNT(*) as failed FROM failed_jobs;"

# Monitor queue (Laravel Horizon style)
watch -n 2 'php artisan queue:monitor'

# List all events
php artisan event:list
```

---

## 📖 DOCUMENTATION

- **Full Guide:** [EVENT_DRIVEN_EMAIL_ARCHITECTURE.md](./EVENT_DRIVEN_EMAIL_ARCHITECTURE.md)
- **Email Matrix:** [EMAIL_STATUS_MATRIX.md](./EMAIL_STATUS_MATRIX.md)
- **Quick Ref:** [EMAIL_MATRIX_QUICK_REF.md](./EMAIL_MATRIX_QUICK_REF.md)

---

## 💡 TIPS FOR DEVELOPERS

### Adding New Email Event

```bash
# 1. Create Event
php artisan make:event MyNewEvent

# 2. Create Listener
php artisan make:listener SendMyNewEmail --event=MyNewEvent

# 3. Implement ShouldQueue
class SendMyNewEmail implements ShouldQueue {
    public $tries = 3;
    public $timeout = 60;
    public $backoff = [30, 60, 120];
}

# 4. Register in EventServiceProvider
protected $listen = [
    MyNewEvent::class => [SendMyNewEmail::class],
];

# 5. Add method to EmailNotificationService
public function sendMyNewEmailFromEvent($entity) { ... }

# 6. Fire event in controller
event(new MyNewEvent($entity));

# 7. Clear cache
php artisan event:cache
```

---

## ⚙️ CONFIGURATION

### Queue Settings (.env)

```env
QUEUE_CONNECTION=database
QUEUE_FAILED_DRIVER=database
```

### Listener Retry Policy

```php
public $tries = 3;              // Max attempts
public $timeout = 60;            // Seconds per attempt
public $backoff = [30, 60, 120]; // Wait between retries
```

---

## ✅ TESTING CHECKLIST

- [ ] PraDiterima email sent successfully
- [ ] PraDitolak email sent successfully
- [ ] Keputusan Kompeten email sent successfully
- [ ] Keputusan Belum Kompeten email sent successfully
- [ ] Duplicate email prevented (idempotent check works)
- [ ] Failed job retries work (test with invalid SMTP)
- [ ] Queue worker auto-restarts (supervisor)
- [ ] Logs show correct event flow

---

**Need Help?**
- Check full documentation: `docs/EVENT_DRIVEN_EMAIL_ARCHITECTURE.md`
- Review logs: `tail -f storage/logs/laravel.log`
- Contact: Development Team

---

**Last Updated:** 2024-01-XX  
**Compliance:** BNSP, ISO 17024

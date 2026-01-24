# 🚀 QUICK START: Sistem Notifikasi Sertifikat

**5-Minute Setup Guide**

---

## ⚡ DEPLOYMENT (Production)

```bash
# 1. SSH to server
ssh root@76.13.18.166

# 2. Navigate to project
cd /var/www/lsp-ui.ibnuapps.cloud/current

# 3. Run migration
php artisan migrate --force
# ✅ Adds notification tracking columns

# 4. Setup queue table (if using database queue)
php artisan queue:table
php artisan migrate

# 5. Configure .env
nano .env
```

Add to `.env`:
```env
# Email (Gmail example)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@lsp-certipro.id
MAIL_FROM_NAME="LSP CertiPro"

# WhatsApp (Fonnte)
WHATSAPP_PROVIDER=fonnte
WHATSAPP_API_KEY=your-fonnte-api-key
WHATSAPP_API_URL=https://api.fonnte.com

# Queue
QUEUE_CONNECTION=database  # or redis for production
```

```bash
# 6. Setup Supervisor
sudo nano /etc/supervisor/conf.d/certipro-queue.conf
```

Paste:
```ini
[program:certipro-queue-notifications]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/lsp-ui.ibnuapps.cloud/current/artisan queue:work database --queue=notifications --tries=1 --timeout=120
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/lsp-ui.ibnuapps.cloud/current/storage/logs/queue.log
```

```bash
# 7. Start queue worker
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start certipro-queue-notifications:*

# 8. Verify running
sudo supervisorctl status

# 9. Test (issue certificate from admin panel)
# - Terbitkan sertifikat
# - Check email received
# - Check WhatsApp received

# 10. Monitor
tail -f storage/logs/laravel.log | grep notification
```

---

## 🧪 TESTING

### Test Email

```bash
php artisan tinker
>>> Mail::raw('Test email from LSP CertiPro', function($m) {
    $m->to('your-test-email@gmail.com')
      ->subject('Test Notification');
});
# ✅ Check your inbox
```

### Test WhatsApp

```bash
php artisan tinker
>>> $service = app(\App\Services\WhatsAppService::class);
>>> $service->sendMessage('628123456789', 'Test WhatsApp dari LSP CertiPro');
# ✅ Check your phone
```

### Test Complete Flow

```bash
# Start queue worker (terminal 1)
php artisan queue:work --queue=notifications -vvv

# Issue certificate (terminal 2 - via admin panel or tinker)
php artisan tinker
>>> $pendaftaran = \App\Models\PendaftaranSertifikasi::find(1);
>>> $service = app(\App\Services\SertifikatService::class);
>>> $result = $service->terbitkan($pendaftaran);
>>> exit

# Watch terminal 1 for job processing
# ✅ Should see: "Email sent successfully" & "WhatsApp sent successfully"
```

---

## 📊 MONITORING

### Check Notification Status

```bash
php artisan tinker
>>> use App\Models\Sertifikat;

# Today's stats
>>> Sertifikat::whereDate('created_at', today())
    ->selectRaw('
        COUNT(*) as total,
        SUM(email_sent_at IS NOT NULL) as email_sent,
        SUM(whatsapp_sent_at IS NOT NULL) as whatsapp_sent,
        SUM(email_failed_at IS NOT NULL) as email_failed,
        SUM(whatsapp_failed_at IS NOT NULL) as whatsapp_failed
    ')
    ->first();

# Recent failures
>>> Sertifikat::where('email_failed_at', '!=', null)
    ->orWhere('whatsapp_failed_at', '!=', null)
    ->latest()
    ->take(5)
    ->get(['id', 'nomor_sertifikat', 'email_error', 'whatsapp_error']);
```

### Check Queue Status

```bash
# Queue stats
php artisan queue:monitor notifications

# Failed jobs
php artisan queue:failed

# Retry all failed
php artisan queue:retry all
```

---

## 🔧 COMMON TASKS

### Retry Failed Notifications

```bash
# Retry all failed
php artisan certipro:retry-notifications --all

# Retry only email
php artisan certipro:retry-notifications --email

# Retry only WhatsApp
php artisan certipro:retry-notifications --whatsapp

# Dry run (preview)
php artisan certipro:retry-notifications --dry-run
```

### Restart Queue Worker

```bash
# Via supervisor (production)
sudo supervisorctl restart certipro-queue-notifications:*

# Via artisan (development)
php artisan queue:restart
```

### Clear Queue

```bash
# Clear all jobs
php artisan queue:flush

# Clear failed jobs
php artisan queue:forget <job-id>
```

---

## 🐛 TROUBLESHOOTING

### Notifications Not Sending

```bash
# 1. Check queue worker is running
ps aux | grep "queue:work"
sudo supervisorctl status

# 2. Check logs
tail -f storage/logs/laravel.log

# 3. Check failed jobs
php artisan queue:failed

# 4. Restart worker
sudo supervisorctl restart certipro-queue-notifications:*
```

### Email Not Working

```bash
# Test SMTP connection
php artisan tinker
>>> use Illuminate\Support\Facades\Mail;
>>> Mail::raw('Test', fn($m) => $m->to('test@gmail.com')->subject('Test'));

# If fails, check .env:
# - MAIL_USERNAME (full email)
# - MAIL_PASSWORD (app password, not account password)
# - Gmail: Enable 2FA + generate App Password
```

### WhatsApp Not Working

```bash
# Test API connection
php artisan tinker
>>> $service = app(\App\Services\WhatsAppService::class);
>>> $service->checkConnection();  # Should return true

# Test send
>>> $service->sendMessage('628123456789', 'Test');

# Check phone format: MUST start with country code (62 for Indonesia)
# Example: 628123456789 (NOT 08123456789)
```

---

## 📝 DAILY CHECKLIST

**Morning:**
- [ ] Check queue worker status: `sudo supervisorctl status`
- [ ] Check notification success rate (query above)
- [ ] Review failed jobs: `php artisan queue:failed`

**Evening:**
- [ ] Review logs for errors: `tail storage/logs/laravel.log`
- [ ] Retry failed notifications if any
- [ ] Monitor storage usage: `du -sh storage/app/public/sertifikat/`

---

## 🎯 KEY COMMANDS REFERENCE

```bash
# Queue Management
php artisan queue:work --queue=notifications  # Start worker
php artisan queue:restart                     # Restart workers
php artisan queue:failed                      # List failed jobs
php artisan queue:retry all                   # Retry all failed
php artisan queue:flush                       # Clear all jobs

# Notification Management
php artisan certipro:retry-notifications --all      # Retry failed
php artisan certipro:retry-notifications --dry-run  # Preview retry

# Monitoring
tail -f storage/logs/laravel.log | grep notification
sudo supervisorctl status
php artisan queue:monitor notifications

# Testing
php artisan tinker  # Then run test commands above
```

---

## 📞 SUPPORT

**Documentation:** `docs/NOTIFIKASI_SERTIFIKAT_SYSTEM.md` (full guide)

**Common Issues:**
- Queue not processing → Restart supervisor
- Email fails → Check .env credentials, test SMTP
- WhatsApp fails → Verify API key, check phone format
- PDF not attached → Check storage permissions, run `php artisan storage:link`

**Logs Location:**
- Application: `storage/logs/laravel.log`
- Queue: `storage/logs/queue.log` (if configured)
- Supervisor: Check supervisor stdout_logfile path

---

✅ **System Ready!** Notifications will automatically send when certificates are issued.

# 📚 NOTIFICATION SYSTEM - DEPLOYMENT GUIDE

## 🎯 OVERVIEW

Sistem notifikasi ASYNC, RELIABLE, dan PRODUCTION-READY untuk aplikasi LSP.
- Event-driven architecture
- Queue-based (tidak blocking)
- Auto-retry dengan progressive backoff
- Idempotent (tidak kirim ganda)
- Audit logging

---

## 📦 STRUKTUR FILE YANG DIBUAT

```
app/
├── Events/
│   ├── PraPendaftaranVerified.php      ✅ Event pra-pendaftaran approved/rejected
│   ├── PendaftaranDiajukan.php         ✅ Event pendaftaran submitted
│   ├── KeputusanDitetapkan.php         ✅ Event keputusan kompeten/belum
│   └── SertifikatDiterbitkan.php       ✅ Event sertifikat issued
│
├── Listeners/
│   └── SendNotificationListener.php    ✅ Central listener untuk semua event
│
├── Jobs/
│   ├── SendEmailNotificationJob.php    ✅ Queue job untuk email
│   └── SendWhatsAppNotificationJob.php ✅ Queue job untuk WhatsApp
│
├── Services/
│   ├── NotificationService.php         ✅ Orchestrator notification
│   ├── EmailService.php                ✅ Email sending abstraction
│   └── WhatsAppService.php             ✅ WhatsApp API integration
│
├── Models/
│   └── NotificationLog.php             ✅ Log semua notification
│
└── Providers/
    └── NotificationEventServiceProvider.php ✅ Event registration

database/migrations/
└── 2026_01_23_180000_create_notification_logs_table.php ✅
```

---

## ⚙️ STEP 1: KONFIGURASI

### 1.1 Environment Variables

Tambahkan di `.env`:

```env
# Queue Configuration
QUEUE_CONNECTION=database  # atau redis untuk production
QUEUE_FAILED_DRIVER=database

# WhatsApp API (opsional)
WHATSAPP_API_URL=https://api.whatsapp-provider.com/send
WHATSAPP_API_KEY=your-api-key-here
```

### 1.2 Queue Config

Edit `config/queue.php`:

```php
'connections' => [
    'database' => [
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
    ],

    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 90,
        'block_for' => null,
    ],
],
```

### 1.3 WhatsApp Config

Buat file `config/services.php` (atau tambahkan):

```php
'whatsapp' => [
    'enabled' => env('WHATSAPP_ENABLED', false),
    'api_url' => env('WHATSAPP_API_URL'),
    'api_key' => env('WHATSAPP_API_KEY'),
],
```

---

## 🗄️ STEP 2: DATABASE SETUP

### 2.1 Run Migrations

```bash
# Migration notification_logs
php artisan migrate

# Migration untuk queue (jika belum ada)
php artisan queue:table
php artisan queue:failed-table
php artisan migrate
```

### 2.2 Verify Tables Created

```bash
php artisan tinker
>>> DB::select('SHOW TABLES');
# Harus ada: notification_logs, jobs, failed_jobs
```

---

## 🔧 STEP 3: REGISTER PROVIDER

Edit `config/app.php`:

```php
'providers' => [
    // ... existing providers

    App\Providers\NotificationEventServiceProvider::class,
],
```

Atau di Laravel 11+, edit `bootstrap/providers.php`:

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\NotificationEventServiceProvider::class, // ADD THIS
];
```

### Verify Registration

```bash
php artisan event:list
# Harus muncul:
# - PraPendaftaranVerified -> SendNotificationListener
# - PendaftaranDiajukan -> SendNotificationListener
# - dll
```

---

## 🚀 STEP 4: START QUEUE WORKER

### 4.1 Development (Manual)

```bash
php artisan queue:work --queue=notifications,default --tries=5 --timeout=30
```

### 4.2 Production (Supervisor - RECOMMENDED)

Buat file `/etc/supervisor/conf.d/certipro-queue.conf`:

```ini
[program:certipro-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/lsp-ui.ibnuapps.cloud/current/artisan queue:work database --queue=notifications,default --sleep=3 --tries=5 --timeout=30 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=3
redirect_stderr=true
stdout_logfile=/var/www/lsp-ui.ibnuapps.cloud/current/storage/logs/queue-worker.log
stopwaitsecs=3600
```

Jalankan:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start certipro-queue-worker:*
sudo supervisorctl status
```

### 4.3 Alternative: Systemd Service

Buat file `/etc/systemd/system/certipro-queue.service`:

```ini
[Unit]
Description=CertiPro Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/lsp-ui.ibnuapps.cloud/current
ExecStart=/usr/bin/php artisan queue:work --queue=notifications,default --tries=5
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Aktifkan:

```bash
sudo systemctl daemon-reload
sudo systemctl enable certipro-queue
sudo systemctl start certipro-queue
sudo systemctl status certipro-queue
```

---

## 📝 STEP 5: USAGE DI CONTROLLER

### Contoh Real Implementation

Update file `app/Http/Controllers/AdminUI/PraPendaftaranAdminController.php`:

```php
use App\Events\PraPendaftaranVerified;

public function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:diterima,ditolak',
        'alasan_penolakan' => 'required_if:status,ditolak',
    ]);

    $praPendaftaran = PraPendaftaran::findOrFail($id);
    $newStatus = $request->status;
    
    $praPendaftaran->update([
        'status' => $newStatus,
        'status_updated_at' => now(),
        'status_updated_by' => auth()->id(),
        'alasan_penolakan' => $request->alasan_penolakan,
    ]);

    // 🔥 TRIGGER EVENT - Notification otomatis via Queue
    event(new PraPendaftaranVerified($praPendaftaran, $newStatus));

    return redirect()->back()->with('success', "Status diupdate. Notifikasi sedang dikirim.");
}
```

**PENTING:** Event akan otomatis trigger Listener → Job → Service → Email/WhatsApp

---

## 🧪 STEP 6: TESTING

### Test 1: Trigger Event Manual

```bash
php artisan tinker
>>> $pra = App\Models\PraPendaftaran::first();
>>> event(new App\Events\PraPendaftaranVerified($pra, 'diterima'));
>>> exit

# Cek queue jobs table
php artisan tinker
>>> DB::table('jobs')->get();
```

### Test 2: Monitor Queue

Terminal 1 (Queue Worker):
```bash
php artisan queue:work --verbose
```

Terminal 2 (Trigger Event):
```bash
php artisan tinker
>>> event(new App\Events\PraPendaftaranVerified(App\Models\PraPendaftaran::first(), 'diterima'));
```

### Test 3: Check Notification Logs

```bash
php artisan tinker
>>> App\Models\NotificationLog::latest()->get();
>>> App\Models\NotificationLog::where('status', 'sent')->count();
>>> App\Models\NotificationLog::where('status', 'failed')->get();
```

---

## 📊 STEP 7: MONITORING & MAINTENANCE

### 7.1 Monitor Failed Jobs

```bash
php artisan queue:failed
```

### 7.2 Retry Failed Jobs

```bash
# Retry all failed
php artisan queue:retry all

# Retry specific job
php artisan queue:retry <job-id>
```

### 7.3 Artisan Command untuk Retry Notification

Buat file `app/Console/Commands/RetryFailedNotifications.php`:

```php
<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class RetryFailedNotifications extends Command
{
    protected $signature = 'notifications:retry {--max-retries=5}';
    protected $description = 'Retry failed notifications';

    public function handle(NotificationService $service)
    {
        $maxRetries = (int) $this->option('max-retries');
        $retried = $service->retryFailed($maxRetries);
        
        $this->info("Retried {$retried} failed notifications.");
    }
}
```

Register di `app/Console/Kernel.php`:

```php
protected $commands = [
    Commands\RetryFailedNotifications::class,
];

protected function schedule(Schedule $schedule)
{
    // Auto-retry failed notifications setiap 1 jam
    $schedule->command('notifications:retry')->hourly();
}
```

### 7.4 Dashboard Monitoring (Optional)

Install Laravel Horizon (for Redis queue):

```bash
composer require laravel/horizon
php artisan horizon:install
php artisan migrate
```

Akses: `https://lsp-ui.ibnuapps.cloud/horizon`

---

## ⚠️ BEST PRACTICES PRODUCTION

### 1. Always Use Queue Worker

❌ JANGAN:
```php
Mail::to($user)->send(new SomeMail()); // BLOCKING!
```

✅ LAKUKAN:
```php
event(new SomeEvent($user)); // NON-BLOCKING via Queue
```

### 2. Set Proper Timeouts

```php
// Job class
public $timeout = 30; // seconds
public $tries = 5;
public $backoff = [30, 60, 120, 300, 600];
```

### 3. Log Everything

```php
Log::info("Notification sent", [
    'notification_log_id' => $log->id,
    'event_type' => $eventType,
]);
```

### 4. Monitor Queue Length

```bash
# Check queue size
php artisan queue:monitor notifications,default --max=100
```

### 5. Graceful Shutdown

```bash
# Stop queue worker gracefully
php artisan queue:restart
```

### 6. Database Cleanup

Schedule cleanup old logs (setelah 90 hari):

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        NotificationLog::where('created_at', '<', now()->subDays(90))->delete();
    })->weekly();
}
```

---

## 🔒 SECURITY CHECKLIST

- ✅ Validate semua input sebelum trigger event
- ✅ Rate limit notification (prevent spam)
- ✅ Encrypt sensitive data di payload
- ✅ Validate phone numbers before WhatsApp send
- ✅ Use HTTPS untuk WhatsApp API
- ✅ Store WhatsApp API key di .env, JANGAN hardcode

---

## 📈 PERFORMANCE OPTIMIZATION

### 1. Use Redis Queue (Faster)

```env
QUEUE_CONNECTION=redis
```

### 2. Multiple Queue Workers

```bash
# Supervisor: numprocs=5
```

### 3. Separate Queues

```php
SendEmailNotificationJob::dispatch($log)->onQueue('email');
SendWhatsAppNotificationJob::dispatch($log)->onQueue('whatsapp');
```

Run workers:
```bash
php artisan queue:work --queue=email &
php artisan queue:work --queue=whatsapp &
```

### 4. Database Indexing

Already handled in migration:
- Index on: channel, event_type, status, unique_key

---

## ❓ TROUBLESHOOTING

### Problem 1: Email tidak terkirim

**Check:**
```bash
# Queue worker running?
ps aux | grep "queue:work"

# Jobs di queue?
php artisan tinker
>>> DB::table('jobs')->count();

# Failed jobs?
php artisan queue:failed
```

### Problem 2: Notification duplicate

**Check unique_key:**
```bash
php artisan tinker
>>> NotificationLog::select('unique_key', DB::raw('count(*) as total'))
    ->groupBy('unique_key')
    ->having('total', '>', 1)
    ->get();
```

### Problem 3: Job timeout

**Increase timeout:**
```php
public $timeout = 60; // increase to 60s
```

---

## ✅ DEPLOYMENT CHECKLIST

- [ ] Migration executed (`notification_logs`, `jobs`, `failed_jobs`)
- [ ] Provider registered (`NotificationEventServiceProvider`)
- [ ] Queue worker running (Supervisor/Systemd)
- [ ] `.env` configured (Queue, WhatsApp)
- [ ] Test notification sent successfully
- [ ] Monitor logs: `storage/logs/laravel.log`
- [ ] Monitor queue: `php artisan queue:monitor`
- [ ] Failed jobs: `php artisan queue:failed` (should be 0)

---

## 🎓 SUMMARY

**Flow lengkap:**
1. Controller trigger `event(new PraPendaftaranVerified(...))`
2. Event captured by `SendNotificationListener`
3. Listener call `NotificationService::send()`
4. Service create `NotificationLog` (pending)
5. Service dispatch `SendEmailNotificationJob` ke queue
6. Queue worker execute job
7. Job call `EmailService::send()`
8. Email terkirim
9. NotificationLog updated (status = sent)
10. ✅ Done!

**Keuntungan:**
- Non-blocking (user tidak tunggu email terkirim)
- Auto-retry jika gagal
- Audit trail lengkap
- Scalable (tambah worker = faster)
- Production-ready

---

🎉 **SISTEM SIAP PRODUCTION!**

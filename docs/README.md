# 📚 CERTIPRO - TECHNICAL DOCUMENTATION

**LSP Certification Management System**  
**Compliance:** BNSP, ISO 17024  
**Last Updated:** 2024-01-XX

---

## 📖 DOCUMENTATION INDEX

### 🚀 Getting Started

1. **[EVENT_DRIVEN_EMAIL_QUICK_START.md](./EVENT_DRIVEN_EMAIL_QUICK_START.md)** ⭐ **START HERE**
   - Quick guide for event-driven email system
   - 1-command deployment script
   - Troubleshooting tips

### 🏗️ Architecture & Implementation

2. **[EVENT_DRIVEN_EMAIL_ARCHITECTURE.md](./EVENT_DRIVEN_EMAIL_ARCHITECTURE.md)**
   - Complete technical architecture
   - Event → Listener flow diagram
   - Deployment steps
   - Monitoring & logging
   - Future improvements

3. **[EMAIL_STATUS_MATRIX.md](./EMAIL_STATUS_MATRIX.md)**
   - Matrix: 12 statuses vs email decisions
   - 7 statuses that SEND email
   - 3 statuses FORBIDDEN from sending email
   - Anti-patterns & compliance rules

4. **[EMAIL_MATRIX_QUICK_REF.md](./EMAIL_MATRIX_QUICK_REF.md)**
   - Quick reference cheat sheet
   - Code templates for each email type
   - One-page reference guide

### 🔐 Security & Access Control

5. **[RBAC_SYSTEM.md](./RBAC_SYSTEM.md)**
   - Role-Based Access Control documentation
   - Permission matrix by role
   - Route protection strategies

6. **[SOP_AKSES_SISTEM_CERTIPRO.txt](./SOP_AKSES_SISTEM_CERTIPRO.txt)**
   - Standard Operating Procedures
   - User access management
   - Workflow guidelines

7. **[LAMPIRAN_MATRIKS_HAK_AKSES.txt](./LAMPIRAN_MATRIKS_HAK_AKSES.txt)**
   - Detailed access rights matrix
   - Module-level permissions

### 📋 Process Documentation

8. **[STEP-3-EVIDENCE-UPLOAD-PER-KUK.md](./STEP-3-EVIDENCE-UPLOAD-PER-KUK.md)**
   - Evidence upload workflow
   - Per-KUK validation process

9. **[STEP-4-SAMPLING-AUDIT-KOMITE-TEKNIS.md](./STEP-4-SAMPLING-AUDIT-KOMITE-TEKNIS.md)**
   - Sampling & audit procedures
   - Technical committee review process

### 🛠️ Development Resources

10. **[rbac_route_examples.php](./rbac_route_examples.php)**
    - Code examples for route protection
    - RBAC implementation patterns

---

## 🎯 QUICK NAVIGATION BY TOPIC

### Email Notifications

- **Just want to deploy?** → [EVENT_DRIVEN_EMAIL_QUICK_START.md](./EVENT_DRIVEN_EMAIL_QUICK_START.md)
- **Need technical details?** → [EVENT_DRIVEN_EMAIL_ARCHITECTURE.md](./EVENT_DRIVEN_EMAIL_ARCHITECTURE.md)
- **Which status sends email?** → [EMAIL_STATUS_MATRIX.md](./EMAIL_STATUS_MATRIX.md)
- **Code templates?** → [EMAIL_MATRIX_QUICK_REF.md](./EMAIL_MATRIX_QUICK_REF.md)

### Access Control

- **Role permissions?** → [RBAC_SYSTEM.md](./RBAC_SYSTEM.md)
- **SOPs?** → [SOP_AKSES_SISTEM_CERTIPRO.txt](./SOP_AKSES_SISTEM_CERTIPRO.txt)
- **Code examples?** → [rbac_route_examples.php](./rbac_route_examples.php)

### Workflow Processes

- **Evidence upload?** → [STEP-3-EVIDENCE-UPLOAD-PER-KUK.md](./STEP-3-EVIDENCE-UPLOAD-PER-KUK.md)
- **Audit process?** → [STEP-4-SAMPLING-AUDIT-KOMITE-TEKNIS.md](./STEP-4-SAMPLING-AUDIT-KOMITE-TEKNIS.md)

---

## 🔥 WHAT'S NEW?

### ✅ Latest Updates (2024-01-XX)

**🚀 Event-Driven Email Architecture (NEW)**

- ✅ Migrated from synchronous to asynchronous email sending
- ✅ Queue-based with 3x retry mechanism
- ✅ Idempotent (prevents duplicate emails)
- ✅ Full audit logging for compliance
- ✅ Decoupled architecture (controllers → events → listeners)

**Files Added:**
- 5 Event classes
- 5 Listener classes (queue-based)
- EmailNotificationService refactored
- EventServiceProvider updated
- 1 migration for idempotent tracking
- 3 documentation files

**Breaking Changes:** None - backward compatible

---

## 📊 SYSTEM OVERVIEW

### Tech Stack

- **Framework:** Laravel 11
- **PHP Version:** 8.3-FPM
- **Database:** MySQL
- **Queue:** Database driver
- **Server:** 76.13.18.166 (lsp-ui.ibnuapps.cloud)
- **Architecture:** Event-Driven, Queue-based

### Key Features

✅ **Idempotent Registration Flow**
- 1 Pra-Pendaftaran = 1 Pendaftaran Sertifikasi
- Signed URLs for secure access (30-day expiry)
- Double-check pattern with DB locks

✅ **Event-Driven Email Notifications**
- Async email sending (queue-based)
- Retry mechanism (3x with backoff)
- No duplicate emails (idempotent)
- Comprehensive audit logging

✅ **Role-Based Access Control (RBAC)**
- 5 roles: Admin, Asesor, Komite Teknis, Asesi, Public
- Fine-grained permissions per module
- Route-level protection

✅ **Compliance**
- BNSP certification standards
- ISO 17024 compliance
- Full audit trail
- Secure data handling

---

## 🚀 DEPLOYMENT GUIDES

### Production Deployment

```bash
# SSH to production server
ssh root@76.13.18.166

# Navigate to project directory
cd /var/www/lsp-ui.ibnuapps.cloud/current

# Deploy event-driven email system
bash deploy-event-driven-email.sh
```

### Manual Steps (if deployment script fails)

```bash
# Run migration
php artisan migrate --force

# Clear caches
php artisan event:cache
php artisan config:cache
php artisan route:cache

# Restart queue
php artisan queue:restart

# Start queue worker (if not using supervisor)
nohup php artisan queue:work --tries=3 --timeout=60 &
```

---

## 🔍 TROUBLESHOOTING

### Email Issues

**Email not sent?**
- Check queue: `php artisan queue:work --once -vvv`
- Check failed jobs: `php artisan queue:failed`
- Check logs: `tail -f storage/logs/laravel.log`

**Duplicate emails?**
- Verify migration ran: `SHOW COLUMNS FROM pra_pendaftaran LIKE 'status_email';`
- Check idempotent logic in EmailNotificationService

**Queue not processing?**
- Check if queue worker running: `ps aux | grep queue:work`
- Restart: `php artisan queue:restart`
- Use supervisor for auto-restart

### Access Issues

**Permission denied errors?**
- Check user role: `SELECT * FROM users WHERE id = <user_id>;`
- Verify RBAC middleware applied to route
- Check [RBAC_SYSTEM.md](./RBAC_SYSTEM.md) for correct permissions

---

## 📝 CONTRIBUTING

### Adding New Documentation

1. Create new `.md` file in `docs/` directory
2. Follow existing formatting conventions
3. Add entry to this README.md
4. Update relevant quick-start guides
5. Commit with descriptive message

### Updating Documentation

1. Update relevant `.md` file
2. Update "Last Updated" date
3. Add entry to "What's New" section
4. Notify team of changes

---

## 📞 SUPPORT

**Development Team:**
- Email: dev@certipro.com
- Slack: #certipro-dev

**Production Issues:**
- Server: 76.13.18.166
- SSH: `root@76.13.18.166`
- Logs: `/var/www/lsp-ui.ibnuapps.cloud/current/storage/logs/`

**Emergency Contacts:**
- System Admin: [Contact Info]
- Database Admin: [Contact Info]

---

## 📜 LICENSE

Proprietary - All rights reserved  
LSP Certification System  
BNSP & ISO 17024 Compliant

---

**Last Updated:** 2024-01-XX  
**Maintained by:** Development Team  
**Version:** 2.0.0 (Event-Driven Architecture)

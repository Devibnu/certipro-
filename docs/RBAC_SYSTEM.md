# CertiPro RBAC System Documentation
## ISO 17024 & BNSP Compliant Role-Based Access Control

**Version:** 1.0.0  
**Last Updated:** January 13, 2026  
**Compliance:** ISO 17024:2012, BNSP Pedoman 201

---

## 1. System Overview

CertiPro implements a comprehensive Role-Based Access Control (RBAC) system that ensures:

1. **Separation of Duties** - Each role has specific, non-overlapping responsibilities
2. **Least Privilege** - Users receive only the permissions necessary for their tasks
3. **Audit Trail** - All access attempts (granted and denied) are logged
4. **Backend as Source of Truth** - Middleware enforces access control; UI visibility follows backend logic

---

## 2. Role Definitions

### 2.1 Super Admin (`super_admin`)
**ISO 17024 Reference:** Management Representative (Clause 5.1)

| Attribute | Value |
|-----------|-------|
| Access Level | Full system access (wildcard) |
| Responsibilities | System oversight, audit compliance, user management |
| Restrictions | None |

### 2.2 Admin (`admin`)
**ISO 17024 Reference:** Certification Body Personnel (Clause 6.1)

| Attribute | Value |
|-----------|-------|
| Access Level | Operational management |
| Responsibilities | Pra-pendaftaran processing, pendaftaran verification, master data management |
| Restrictions | Cannot make certification decisions, cannot issue certificates |

### 2.3 Asesor (`asesor`)
**ISO 17024 Reference:** Assessor (Clause 9.2)

| Attribute | Value |
|-----------|-------|
| Access Level | Assessment operations only |
| Responsibilities | Conduct assessments, input results per KUK |
| Restrictions | Cannot verify pendaftaran, cannot make certification decisions |

### 2.4 Komite Teknis (`komite_teknis`)
**ISO 17024 Reference:** Certification Decision Maker (Clause 9.5)

| Attribute | Value |
|-----------|-------|
| Access Level | Decision and certificate operations |
| Responsibilities | Review assessments, make certification decisions, issue certificates |
| Restrictions | Cannot modify assessment results, cannot manage users |

---

## 3. Permission Matrix

### 3.1 Permission Naming Convention
```
{module}.{action}
```
Examples:
- `pendaftaran.view` - View pendaftaran records
- `asesmen.submit` - Submit assessment results
- `sertifikat.generate` - Generate certificates

### 3.2 Complete Permission Map

| Permission | Super Admin | Admin | Asesor | Komite Teknis |
|------------|:-----------:|:-----:|:------:|:-------------:|
| **Dashboard** |
| dashboard.view | ✅ | ✅ | ✅ | ✅ |
| **Pra-Pendaftaran** |
| pra_pendaftaran.view | ✅ | ✅ | ❌ | ❌ |
| pra_pendaftaran.manage | ✅ | ✅ | ❌ | ❌ |
| pra_pendaftaran.create | ✅ | ✅ | ❌ | ❌ |
| **Pendaftaran** |
| pendaftaran.view | ✅ | ✅ | ✅ | ✅ |
| pendaftaran.verify | ✅ | ✅ | ❌ | ❌ |
| pendaftaran.assign | ✅ | ✅ | ❌ | ❌ |
| pendaftaran.reject | ✅ | ✅ | ❌ | ❌ |
| **Asesmen** |
| asesmen.view | ✅ | ✅ | ✅ | ✅ |
| asesmen.submit | ✅ | ❌ | ✅ | ❌ |
| asesmen.lock | ✅ | ❌ | ✅ | ❌ |
| **Keputusan** |
| keputusan.view | ✅ | ❌ | ❌ | ✅ |
| keputusan.approve | ✅ | ❌ | ❌ | ✅ |
| **Sertifikat** |
| sertifikat.view | ✅ | ❌ | ❌ | ✅ |
| sertifikat.generate | ✅ | ❌ | ❌ | ✅ |
| sertifikat.download | ✅ | ❌ | ❌ | ✅ |
| sertifikat.revoke | ✅ | ❌ | ❌ | ❌ |
| **Master Data** |
| skema.view | ✅ | ✅ | ✅ | ✅ |
| skema.manage | ✅ | ✅ | ❌ | ❌ |
| unit_kompetensi.view | ✅ | ✅ | ✅ | ❌ |
| unit_kompetensi.manage | ✅ | ✅ | ❌ | ❌ |
| kuk.view | ✅ | ✅ | ✅ | ❌ |
| kuk.manage | ✅ | ✅ | ❌ | ❌ |
| **User Management** |
| users.view | ✅ | ✅ | ❌ | ❌ |
| users.manage | ✅ | ✅ | ❌ | ❌ |
| users.reset_password | ✅ | ✅ | ❌ | ❌ |
| **Audit** |
| audit.view | ✅ | ✅ | ❌ | ❌ |
| audit.export | ✅ | ❌ | ❌ | ❌ |
| audit.statistics | ✅ | ❌ | ❌ | ❌ |
| **Settings** |
| settings.view | ✅ | ❌ | ❌ | ❌ |
| settings.manage | ✅ | ❌ | ❌ | ❌ |
| **CMS** |
| cms.view | ✅ | ✅ | ❌ | ❌ |
| cms.manage | ✅ | ✅ | ❌ | ❌ |

---

## 4. Middleware Implementation

### 4.1 CheckPermission Middleware
**Location:** `app/Http/Middleware/CheckPermission.php`

```php
// Single permission
Route::get('/asesmen', [AsesmenController::class, 'index'])
    ->middleware('permission:asesmen.view');

// Multiple permissions (OR logic)
Route::get('/pendaftaran/{id}', [PendaftaranController::class, 'show'])
    ->middleware('permission:pendaftaran.view|pendaftaran.verify');
```

### 4.2 CheckRole Middleware
**Location:** `app/Http/Middleware/CheckRole.php`

```php
// Single role
Route::get('/keputusan', [KeputusanController::class, 'index'])
    ->middleware('role:komite_teknis');

// Multiple roles (OR logic)
Route::get('/asesmen', [AsesmenController::class, 'index'])
    ->middleware('role:asesor|admin');
```

---

## 5. Audit Trail

### 5.1 Logged Events

| Event | Action Code | Description |
|-------|-------------|-------------|
| Access Denied | `access_denied` | User attempted to access resource without permission |
| Role Assigned | `role_assigned` | Role assigned to user |
| Role Removed | `role_removed` | Role removed from user |
| Roles Synced | `roles_synced` | User roles updated |
| Permission Granted | `permission_granted` | Permission granted to role |
| Permission Revoked | `permission_revoked` | Permission revoked from role |

### 5.2 Audit Log Structure

```json
{
  "user_id": 123,
  "user_name": "John Doe",
  "user_role": "asesor",
  "action": "access_denied",
  "module": "access_control",
  "description": "Akses ditolak ke adminui/keputusan. Izin diperlukan: keputusan.view",
  "metadata": {
    "required_permissions": ["keputusan.view"],
    "user_permissions": {
      "rbac": ["dashboard.view", "asesmen.view", "asesmen.submit"],
      "legacy": []
    },
    "user_roles": ["asesor"],
    "route_name": "adminui.keputusan.index",
    "request_id": "REQ-A1B2C3D4E5F6"
  },
  "ip_address": "192.168.1.100",
  "created_at": "2026-01-13T10:30:00+07:00"
}
```

---

## 6. ISO 17024 Compliance Mapping

| ISO 17024 Clause | Requirement | CertiPro Implementation |
|------------------|-------------|-------------------------|
| 5.1.3 | Personnel competence | Role-based access ensures users only access functions they're qualified for |
| 5.2 | Confidentiality | All access is controlled and logged; denied access triggers audit entry |
| 9.2.2 | Assessor independence | Asesor role separated from Admin and Komite Teknis |
| 9.5 | Certification decision | Komite Teknis role exclusively handles decisions; Asesor cannot approve |
| 10 | Records | Complete audit trail of all access attempts and permission changes |

---

## 7. BNSP Compliance

| BNSP Requirement | Implementation |
|------------------|----------------|
| Pedoman 201 - Keamanan Data | Backend middleware enforces all access control |
| Pedoman 201 - Pemisahan Tugas | Roles are decoupled with non-overlapping critical responsibilities |
| Pedoman 201 - Jejak Audit | All access attempts logged with timestamp, IP, and user context |

---

## 8. Usage Examples

### 8.1 Checking Permission in Controller
```php
use App\Services\PermissionService;

class SertifikatController extends Controller
{
    public function generate(Request $request, int $id)
    {
        $permissionService = app(PermissionService::class);
        
        if (!$permissionService->hasPermission(auth()->user(), 'sertifikat.generate')) {
            abort(403, 'Anda tidak memiliki izin untuk menerbitkan sertifikat.');
        }
        
        // ... generate certificate logic
    }
}
```

### 8.2 Checking Permission in Blade View
```blade
@if(auth()->user()->hasPermission('users.manage'))
    <a href="{{ route('adminui.users.index') }}">Kelola User</a>
@endif
```

### 8.3 Assigning Role to User
```php
use App\Services\PermissionService;

$permissionService = app(PermissionService::class);
$permissionService->assignRole($user, 'asesor', auth()->user());
```

---

## 9. Database Schema

### 9.1 Tables

- `roles` - Role definitions
- `permissions` - Permission definitions
- `role_permission` - Many-to-many pivot
- `user_role` - Many-to-many pivot

### 9.2 Running Migrations & Seeder

```bash
# Run migration
php artisan migrate

# Seed roles and permissions
php artisan db:seed --class=RbacPermissionSeeder
```

---

## 10. Best Practices

1. **Always use middleware** - Never rely on UI-only access control
2. **Check most specific permission** - Use `asesmen.submit` not just `asesmen.view`
3. **Log all critical actions** - Extend AuditLog usage for compliance
4. **Review permissions regularly** - Audit role-permission mappings periodically
5. **Use PermissionService** - Centralized logic for consistency

---

## 11. Troubleshooting

### Common Issues

| Issue | Solution |
|-------|----------|
| User can't access page despite having role | Clear permission cache: `Cache::forget('user_permissions_' . $userId)` |
| Legacy permissions not working | Check `mapLegacyPermission()` in PermissionService |
| 403 errors not showing custom page | Ensure `adminui.errors.403` view exists |

---

**Document Control:**
- Created: January 13, 2026
- Author: System Architect
- Approved: ISO 17024 Management Representative

<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ============================================================================
 * User Model - ISO 17024 & BNSP Compliant RBAC
 * ============================================================================
 * 
 * RBAC DESIGN:
 * - One User = One Role (via role_id FK)
 * - One Role = Many Permissions (via role_permission pivot)
 * - Super Admin bypass for full access
 * 
 * BACKWARD COMPATIBILITY:
 * - Legacy 'role' column supported
 * - Legacy 'permissions' array supported
 * - Old user_role pivot table still works
 * 
 * @see ISO 17024:2012 Clause 5.1.3 (Personnel competence)
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',       // NEW: FK to roles table (primary)
        'role',          // Legacy field - kept for backward compatibility
        'permissions',   // Legacy field - kept for backward compatibility
        'photo',
        'phone',
        'location',
        'about_me',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'permissions' => 'array',
        'role_id' => 'integer',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Get the user's role (ONE-TO-ONE via role_id FK)
     * PRIMARY relationship for RBAC
     */
    public function userRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * The roles that belong to the user (many-to-many via pivot)
     * DEPRECATED: Kept for backward compatibility, use userRole() instead
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role')
            ->withTimestamps();
    }

    // =========================================================================
    // ROLE CHECKING METHODS
    // =========================================================================

    /**
     * Check if user has a specific role
     * Priority: role_id FK > user_role pivot > legacy role column
     */
    public function hasRole(string $roleName): bool
    {
        // 1. PRIMARY: Check role_id FK (new system)
        if ($this->role_id && $this->userRole) {
            if ($this->userRole->name === $roleName) {
                return true;
            }
        }
        
        // 2. FALLBACK: Check user_role pivot (legacy RBAC)
        if ($this->roles()->where('name', $roleName)->exists()) {
            return true;
        }
        
        // 3. LEGACY: Check role column
        return $this->checkLegacyRole($roleName);
    }

    /**
     * Check legacy role column for backward compatibility
     */
    protected function checkLegacyRole(string $roleName): bool
    {
        $legacyRole = strtolower($this->role ?? '');
        $checkRole = strtolower($roleName);
        
        $legacyMappings = [
            'super admin' => Role::SUPER_ADMIN,
            'super_admin' => Role::SUPER_ADMIN,
            'superadmin' => Role::SUPER_ADMIN,
            'admin' => Role::ADMIN,
            'staff' => Role::ASESOR,
            'asesor' => Role::ASESOR,
            'komite_teknis' => Role::KOMITE_TEKNIS,
            'komite teknis' => Role::KOMITE_TEKNIS,
        ];
        
        $mappedLegacy = $legacyMappings[$legacyRole] ?? $legacyRole;
        return $mappedLegacy === $checkRole;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roleNames): bool
    {
        foreach ($roleNames as $roleName) {
            if ($this->hasRole($roleName)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user is Super Admin (bypass all permissions)
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole(Role::SUPER_ADMIN);
    }

    /**
     * Check if user is Admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ADMIN);
    }

    /**
     * Check if user is Asesor
     */
    public function isAsesor(): bool
    {
        return $this->hasRole(Role::ASESOR);
    }

    /**
     * Check if user is Staff (alias for isAsesor - backward compatibility)
     */
    public function isStaff(): bool
    {
        return $this->isAsesor();
    }

    /**
     * Check if user is Komite Teknis
     */
    public function isKomiteTeknis(): bool
    {
        return $this->hasRole(Role::KOMITE_TEKNIS);
    }

    // =========================================================================
    // PERMISSION CHECKING METHODS
    // =========================================================================

    /**
     * Get user's current role model
     */
    public function getCurrentRole(): ?Role
    {
        // Priority: role_id FK > user_role pivot
        if ($this->role_id && $this->userRole) {
            return $this->userRole;
        }
        
        return $this->roles()->first();
    }

    /**
     * Get all permissions for this user through their role
     */
    public function getAllPermissions(): \Illuminate\Support\Collection
    {
        $role = $this->getCurrentRole();
        
        if ($role) {
            return $role->permissions;
        }
        
        return collect();
    }

    /**
     * Check if user has a specific permission
     * 
     * @param string $permissionCode Permission code (e.g., 'asesmen.view')
     * @return bool
     */
    public function hasPermission(string $permissionCode): bool
    {
        // Super Admin bypass - full access
        if ($this->isSuperAdmin()) {
            return true;
        }
        
        // 1. PRIMARY: Check via role_id FK
        if ($this->role_id && $this->userRole) {
            $hasPermission = $this->userRole->permissions()
                ->where('name', $permissionCode)
                ->exists();
            
            if ($hasPermission) {
                return true;
            }
        }
        
        // 2. FALLBACK: Check via user_role pivot
        $hasPermissionViaPivot = $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionCode) {
                $query->where('name', $permissionCode);
            })
            ->exists();
        
        if ($hasPermissionViaPivot) {
            return true;
        }
        
        // 3. LEGACY: Check legacy permissions array
        return $this->checkLegacyPermission($permissionCode);
    }

    /**
     * Check legacy permissions array for backward compatibility
     */
    protected function checkLegacyPermission(string $permissionCode): bool
    {
        if (!is_array($this->permissions)) {
            return false;
        }
        
        // Direct match
        if (in_array($permissionCode, $this->permissions)) {
            return true;
        }
        
        // Match module name (e.g., 'asesmen.view' matches 'Asesmen')
        $parts = explode('.', $permissionCode);
        if (count($parts) >= 1) {
            $module = ucwords(str_replace('_', ' ', $parts[0]));
            if (in_array($module, $this->permissions)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if user can access a module (any permission on that module)
     */
    public function canAccessModule(string $moduleName): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        
        $role = $this->getCurrentRole();
        if ($role) {
            return $role->permissions()
                ->where('module', $moduleName)
                ->exists();
        }
        
        // Legacy fallback
        if (is_array($this->permissions)) {
            $legacyModule = ucwords(str_replace('_', ' ', $moduleName));
            return in_array($legacyModule, $this->permissions);
        }
        
        return false;
    }

    // =========================================================================
    // ROLE MANAGEMENT METHODS
    // =========================================================================

    /**
     * Set user's role (via role_id FK - NEW SYSTEM)
     * 
     * @param string|Role $role Role name or Role model
     */
    public function setRole(string|Role $role): void
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }
        
        $this->role_id = $role->id;
        $this->save();
    }

    /**
     * Assign a role to user (via pivot - LEGACY)
     * @deprecated Use setRole() instead
     */
    public function assignRole(string|Role $role): void
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }
        
        // Also set role_id for new system
        $this->role_id = $role->id;
        $this->save();
        
        // Attach to pivot for backward compatibility
        if (!$this->roles()->where('role_id', $role->id)->exists()) {
            $this->roles()->attach($role->id);
        }
    }

    /**
     * Remove role from user
     * @deprecated Use setRole(null) instead
     */
    public function removeRole(string|Role $role): void
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->first();
            if (!$role) return;
        }
        
        // Clear role_id if matches
        if ($this->role_id === $role->id) {
            $this->role_id = null;
            $this->save();
        }
        
        // Remove from pivot
        $this->roles()->detach($role->id);
    }

    /**
     * Sync user roles (sets single role via role_id)
     */
    public function syncRoles(array $roleNames): void
    {
        // Take first role for new system (one-to-one)
        if (!empty($roleNames)) {
            $role = Role::where('name', $roleNames[0])->first();
            if ($role) {
                $this->role_id = $role->id;
                $this->save();
            }
        }
        
        // Sync pivot for backward compatibility
        $roleIds = Role::whereIn('name', $roleNames)->pluck('id');
        $this->roles()->sync($roleIds);
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    /**
     * Get primary role display name
     */
    public function getPrimaryRoleAttribute(): string
    {
        $role = $this->getCurrentRole();
        if ($role) {
            return $role->display_name;
        }
        
        // Fallback to legacy role
        return ucwords(str_replace('_', ' ', $this->role ?? 'User'));
    }

    /**
     * Get role name (for display/API)
     */
    public function getRoleNameAttribute(): string
    {
        $role = $this->getCurrentRole();
        return $role ? $role->name : ($this->role ?? 'user');
    }

    // =========================================================================
    // BUSINESS RELATIONSHIPS
    // =========================================================================

    /**
     * Get the pendaftaran sertifikasi for the user (asesi).
     */
    public function pendaftaranSertifikasi(): HasMany
    {
        return $this->hasMany(PendaftaranSertifikasi::class, 'user_id');
    }

    /**
     * Get the asesmen where this user is the asesor.
     */
    public function asesmenAsAsesor(): HasMany
    {
        return $this->hasMany(Asesmen::class, 'asesor_id');
    }
}

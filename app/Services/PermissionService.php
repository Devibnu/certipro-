<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\AuditLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * ============================================================================
 * PermissionService - ISO 17024 & BNSP Compliant RBAC Service
 * ============================================================================
 * 
 * Centralized service for all permission-related operations.
 * This service provides a consistent API for checking and managing permissions
 * throughout the application.
 * 
 * FEATURES:
 * - Permission checking with caching
 * - Role-based permission inheritance
 * - Legacy permission fallback
 * - Audit trail for permission changes
 * - Menu visibility generation
 * 
 * @see ISO 17024:2012 Clause 5.1.3 (Personnel competence)
 * @see BNSP Pedoman 201 (Ketentuan Umum LSP)
 */
class PermissionService
{
    /**
     * Cache duration in seconds (5 minutes)
     */
    const CACHE_TTL = 300;

    /**
     * Cache key prefix
     */
    const CACHE_PREFIX = 'user_permissions_';

    /**
     * Check if a user has a specific permission
     * 
     * @param User $user
     * @param string $permission
     * @return bool
     */
    public function hasPermission(User $user, string $permission): bool
    {
        // Super Admin bypass
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Get cached permissions
        $permissions = $this->getUserPermissions($user);
        
        return in_array($permission, $permissions);
    }

    /**
     * Check if user has any of the given permissions (OR logic)
     * 
     * @param User $user
     * @param array $permissions
     * @return bool
     */
    public function hasAnyPermission(User $user, array $permissions): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        $userPermissions = $this->getUserPermissions($user);
        
        foreach ($permissions as $permission) {
            if (in_array($permission, $userPermissions)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all of the given permissions (AND logic)
     * 
     * @param User $user
     * @param array $permissions
     * @return bool
     */
    public function hasAllPermissions(User $user, array $permissions): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        $userPermissions = $this->getUserPermissions($user);
        
        foreach ($permissions as $permission) {
            if (!in_array($permission, $userPermissions)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all permissions for a user (cached)
     * 
     * @param User $user
     * @return array
     */
    public function getUserPermissions(User $user): array
    {
        $cacheKey = self::CACHE_PREFIX . $user->id;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            $permissions = [];

            // Get permissions from userRole (primary RBAC via role_id FK)
            if ($user->role_id && $user->userRole) {
                $rolePermissions = $user->userRole->permissions()
                    ->pluck('name')
                    ->toArray();
                $permissions = array_merge($permissions, $rolePermissions);
            }

            // Fallback: Get permissions from roles pivot (legacy RBAC)
            if (empty($permissions)) {
                $rbacPermissions = $user->roles()
                    ->with('permissions')
                    ->get()
                    ->pluck('permissions')
                    ->flatten()
                    ->pluck('name')
                    ->unique()
                    ->toArray();
                
                $permissions = array_merge($permissions, $rbacPermissions);
            }

            return array_unique($permissions);
        });
    }

    /**
     * Map legacy permission name to new format
     * 
     * @param string $legacyPermission
     * @return string|null
     */
    protected function mapLegacyPermission(string $legacyPermission): ?string
    {
        $mappings = [
            'Dashboard' => 'dashboard.view',
            'Users' => 'users.manage',
            'Pra Pendaftaran' => 'pra_pendaftaran.manage',
            'Pendaftaran Sertifikasi' => 'pendaftaran.view',
            'Skema Sertifikasi' => 'skema.manage',
            'Unit Kompetensi' => 'unit_kompetensi.manage',
            'KUK' => 'kuk.manage',
            'Asesmen' => 'asesmen.view',
            'Keputusan Sertifikasi' => 'keputusan.view',
            'Sertifikat' => 'sertifikat.view',
            'Audit Log' => 'audit.view',
            'CMS Landing Page' => 'cms.manage',
            'Profile' => 'dashboard.view', // Everyone can access their profile
            'Services' => 'cms.manage',
            'Contact Page' => 'cms.manage',
            'Blog' => 'cms.manage',
            'Projects' => 'cms.manage',
            'About Page' => 'cms.manage',
        ];

        return $mappings[$legacyPermission] ?? null;
    }

    /**
     * Clear permission cache for a user
     * 
     * @param User $user
     */
    public function clearUserCache(User $user): void
    {
        Cache::forget(self::CACHE_PREFIX . $user->id);
    }

    /**
     * Clear permission cache for all users
     */
    public function clearAllCache(): void
    {
        // In production, use Cache::tags() or a more efficient method
        // For now, we'll let the cache expire naturally
        Cache::flush();
    }

    /**
     * Check if user is Super Admin
     * 
     * @param User $user
     * @return bool
     */
    public function isSuperAdmin(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Get menu visibility map for a user
     * Used by Blade views to show/hide menu items
     * 
     * @param User $user
     * @return array
     */
    public function getMenuVisibility(User $user): array
    {
        $isSuperAdmin = $this->isSuperAdmin($user);
        $permissions = $this->getUserPermissions($user);

        return [
            'dashboard' => $isSuperAdmin || in_array('dashboard.view', $permissions),
            'users' => $isSuperAdmin || in_array('users.manage', $permissions),
            'pra_pendaftaran' => $isSuperAdmin || $this->hasAnyPermission($user, ['pra_pendaftaran.view', 'pra_pendaftaran.manage']),
            'pendaftaran' => $isSuperAdmin || $this->hasAnyPermission($user, ['pendaftaran.view', 'pendaftaran.verify']),
            'skema' => $isSuperAdmin || $this->hasAnyPermission($user, ['skema.view', 'skema.manage']),
            'unit_kompetensi' => $isSuperAdmin || $this->hasAnyPermission($user, ['unit_kompetensi.view', 'unit_kompetensi.manage']),
            'kuk' => $isSuperAdmin || $this->hasAnyPermission($user, ['kuk.view', 'kuk.manage']),
            'asesmen' => $isSuperAdmin || $this->hasAnyPermission($user, ['asesmen.view', 'asesmen.submit']),
            'keputusan' => $isSuperAdmin || $this->hasAnyPermission($user, ['keputusan.view', 'keputusan.approve']),
            'sertifikat' => $isSuperAdmin || $this->hasAnyPermission($user, ['sertifikat.view', 'sertifikat.generate']),
            'audit' => $isSuperAdmin || $this->hasAnyPermission($user, ['audit.view', 'audit.export']),
            'cms' => $isSuperAdmin || $this->hasAnyPermission($user, ['cms.view', 'cms.manage']),
            'settings' => $isSuperAdmin || $this->hasAnyPermission($user, ['settings.view', 'settings.manage']),
        ];
    }

    /**
     * Assign a role to a user
     * 
     * @param User $user
     * @param string $roleName
     * @param User|null $assignedBy
     * @return bool
     */
    public function assignRole(User $user, string $roleName, ?User $assignedBy = null): bool
    {
        $role = Role::where('name', $roleName)->first();
        
        if (!$role) {
            return false;
        }

        // Check if already has the role
        if ($user->roles()->where('role_id', $role->id)->exists()) {
            return true;
        }

        $user->roles()->attach($role->id);
        $this->clearUserCache($user);

        // Audit log
        $this->logPermissionChange(
            $user,
            'role_assigned',
            "Role '{$role->display_name}' assigned to user",
            $assignedBy,
            ['role' => $roleName]
        );

        return true;
    }

    /**
     * Remove a role from a user
     * 
     * @param User $user
     * @param string $roleName
     * @param User|null $removedBy
     * @return bool
     */
    public function removeRole(User $user, string $roleName, ?User $removedBy = null): bool
    {
        $role = Role::where('name', $roleName)->first();
        
        if (!$role) {
            return false;
        }

        $user->roles()->detach($role->id);
        $this->clearUserCache($user);

        // Audit log
        $this->logPermissionChange(
            $user,
            'role_removed',
            "Role '{$role->display_name}' removed from user",
            $removedBy,
            ['role' => $roleName]
        );

        return true;
    }

    /**
     * Sync roles for a user (replace all roles)
     * 
     * @param User $user
     * @param array $roleNames
     * @param User|null $changedBy
     * @return bool
     */
    public function syncRoles(User $user, array $roleNames, ?User $changedBy = null): bool
    {
        $oldRoles = $user->roles->pluck('name')->toArray();
        
        $roleIds = Role::whereIn('name', $roleNames)->pluck('id');
        $user->roles()->sync($roleIds);
        $this->clearUserCache($user);

        // Audit log
        $this->logPermissionChange(
            $user,
            'roles_synced',
            'User roles updated',
            $changedBy,
            [
                'old_roles' => $oldRoles,
                'new_roles' => $roleNames,
            ]
        );

        return true;
    }

    /**
     * Log permission change for audit trail
     * 
     * @param User $targetUser
     * @param string $action
     * @param string $description
     * @param User|null $changedBy
     * @param array $metadata
     */
    protected function logPermissionChange(
        User $targetUser,
        string $action,
        string $description,
        ?User $changedBy,
        array $metadata = []
    ): void {
        AuditLog::create([
            'user_id' => $changedBy?->id,
            'user_name' => $changedBy?->name ?? 'System',
            'user_role' => $changedBy?->userRole?->name ?? 'system',
            'action' => $action,
            'module' => 'rbac',
            'description' => $description,
            'model_type' => User::class,
            'model_id' => $targetUser->id,
            'metadata' => array_merge($metadata, [
                'target_user_id' => $targetUser->id,
                'target_user_email' => $targetUser->email,
            ]),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'created_at' => now(),
        ]);
    }

    /**
     * Get all available permissions grouped by module
     * 
     * @return Collection
     */
    public function getAllPermissionsGrouped(): Collection
    {
        return Permission::all()->groupBy('module');
    }

    /**
     * Get all available roles with their permissions
     * 
     * @return Collection
     */
    public function getAllRolesWithPermissions(): Collection
    {
        return Role::with('permissions')->get();
    }
}

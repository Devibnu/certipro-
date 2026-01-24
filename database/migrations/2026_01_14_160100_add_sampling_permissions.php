<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

/**
 * ============================================================================
 * Migration: Add Sampling Permissions
 * ============================================================================
 * 
 * Adds RBAC permissions for sampling audit functionality.
 * 
 * Permissions:
 * - sampling.mark: Mark asesmen for sampling audit
 * - sampling.view: View sampling status and notes
 * 
 * Role Assignments:
 * - super_admin: all permissions
 * - komite_teknis: mark & view (primary QC role)
 * - admin: view only
 * - asesor: no access
 * - asesi: no access
 * 
 * @see ISO 17024:2012 Clause 4.3 (Impartiality)
 * ============================================================================
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create permissions
        $permissions = [
            [
                'name' => 'sampling.mark',
                'display_name' => 'Tandai Sampling Audit',
                'description' => 'Dapat menandai asesmen untuk sampling audit',
                'module' => 'sampling',
                'action' => 'mark',
            ],
            [
                'name' => 'sampling.view',
                'display_name' => 'Lihat Sampling Audit',
                'description' => 'Dapat melihat status sampling audit',
                'module' => 'sampling',
                'action' => 'view',
            ],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm['name']],
                $perm
            );
        }

        // Role assignments
        $rolePermissions = [
            'super_admin' => ['sampling.mark', 'sampling.view'],
            'komite_teknis' => ['sampling.mark', 'sampling.view'], // Primary QC role
            'admin' => ['sampling.view'], // View only
            // asesor: no access
            // asesi: no access
        ];

        foreach ($rolePermissions as $roleName => $permissionNames) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $permissionIds = Permission::whereIn('name', $permissionNames)->pluck('id');
                // Sync without detaching existing permissions
                $role->permissions()->syncWithoutDetaching($permissionIds);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove permission assignments from roles
        $permissions = Permission::whereIn('name', ['sampling.mark', 'sampling.view'])->get();
        
        foreach ($permissions as $permission) {
            $permission->roles()->detach();
        }
        
        // Delete permissions
        Permission::whereIn('name', ['sampling.mark', 'sampling.view'])->delete();
    }
};

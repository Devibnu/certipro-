<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * ============================================================================
 * Migration: Add Evidence Permissions for RBAC
 * ============================================================================
 * 
 * Adds permissions for evidence upload/view/delete functionality.
 * Assigns permissions to appropriate roles:
 * - Asesor: upload, view, delete (own evidence)
 * - Komite Teknis: view only
 * - Admin LSP: view only
 * - Asesi: no access
 * 
 * @see ISO 17024:2012 Clause 7.4
 * ============================================================================
 */
return new class extends Migration
{
    /**
     * Evidence permissions to add
     */
    private array $permissions = [
        ['name' => 'evidence.upload', 'display_name' => 'Upload Evidence', 'module' => 'evidence', 'action' => 'upload', 'description' => 'Upload bukti/evidence per KUK'],
        ['name' => 'evidence.view', 'display_name' => 'Lihat Evidence', 'module' => 'evidence', 'action' => 'view', 'description' => 'Melihat bukti/evidence per KUK'],
        ['name' => 'evidence.delete', 'display_name' => 'Hapus Evidence', 'module' => 'evidence', 'action' => 'delete', 'description' => 'Menghapus bukti/evidence per KUK'],
    ];

    /**
     * Role-permission mapping
     */
    private array $rolePermissions = [
        'super_admin' => ['evidence.upload', 'evidence.view', 'evidence.delete'],
        'admin' => ['evidence.view'],
        'asesor' => ['evidence.upload', 'evidence.view', 'evidence.delete'],
        'komite_teknis' => ['evidence.view'],
        // asesi: no evidence permissions
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert permissions
        foreach ($this->permissions as $permission) {
            DB::table('permissions')->insertOrIgnore([
                'name' => $permission['name'],
                'display_name' => $permission['display_name'],
                'module' => $permission['module'],
                'action' => $permission['action'],
                'description' => $permission['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Assign permissions to roles
        foreach ($this->rolePermissions as $roleName => $permissionNames) {
            $role = DB::table('roles')->where('name', $roleName)->first();
            if (!$role) {
                continue;
            }

            foreach ($permissionNames as $permissionName) {
                $permission = DB::table('permissions')->where('name', $permissionName)->first();
                if (!$permission) {
                    continue;
                }

                // Insert role-permission mapping if not exists
                DB::table('role_permission')->insertOrIgnore([
                    'role_id' => $role->id,
                    'permission_id' => $permission->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissionNames = array_column($this->permissions, 'name');
        
        // Delete role-permission mappings
        $permissionIds = DB::table('permissions')
            ->whereIn('name', $permissionNames)
            ->pluck('id');
        
        DB::table('role_permission')->whereIn('permission_id', $permissionIds)->delete();
        
        // Delete permissions
        DB::table('permissions')->whereIn('name', $permissionNames)->delete();
    }
};

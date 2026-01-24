<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Permission;
use App\Models\Role;

/**
 * Normalize CMS Permissions for RBAC Compliance
 * 
 * RBAC Principle: Permission harus spesifik dan konsisten (module.action)
 * 
 * Old format: 'Users', 'Services', 'Profile', 'About Page', 'Dashboard'
 * New format: 'cms.about', 'cms.services', 'cms.home', 'cms.contact', 'profile.view'
 * 
 * @see ISO 17024:2012 - Access control for certification systems
 * @see BNSP REG.3.02 - Audit trail requirements
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tambah permission CMS spesifik yang belum ada
        // Format: name = module.action (sesuai konvensi RBAC)
        $cmsPermissions = [
            // CMS Module Permissions
            ['name' => 'cms.about', 'display_name' => 'CMS About', 'module' => 'cms', 'action' => 'about', 'description' => 'Manage About page content'],
            ['name' => 'cms.services', 'display_name' => 'CMS Services', 'module' => 'cms', 'action' => 'services', 'description' => 'Manage Services page content'],
            ['name' => 'cms.home', 'display_name' => 'CMS Home', 'module' => 'cms', 'action' => 'home', 'description' => 'Manage Home page content and hero sliders'],
            ['name' => 'cms.contact', 'display_name' => 'CMS Contact', 'module' => 'cms', 'action' => 'contact', 'description' => 'Manage Contact page and messages'],
            ['name' => 'cms.footer', 'display_name' => 'CMS Footer', 'module' => 'cms', 'action' => 'footer', 'description' => 'Manage Footer settings'],
            
            // Profile - semua user login bisa akses
            ['name' => 'profile.view', 'display_name' => 'Profile View', 'module' => 'profile', 'action' => 'view', 'description' => 'View own profile'],
            ['name' => 'profile.update', 'display_name' => 'Profile Update', 'module' => 'profile', 'action' => 'update', 'description' => 'Update own profile'],
        ];
        
        foreach ($cmsPermissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm['name']],
                [
                    'display_name' => $perm['display_name'],
                    'module' => $perm['module'],
                    'action' => $perm['action'],
                    'description' => $perm['description'], 
                    'created_at' => now(), 
                    'updated_at' => now()
                ]
            );
        }
        
        // Assign CMS permissions ke admin (super_admin sudah bypass semua)
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $cmsPermIds = Permission::whereIn('name', [
                'cms.about', 'cms.services', 'cms.home', 'cms.contact', 'cms.footer'
            ])->pluck('id')->toArray();
            
            $admin->permissions()->syncWithoutDetaching($cmsPermIds);
        }
        
        // Semua role dapat profile permissions (kecuali super_admin yang bypass)
        $allRoles = Role::whereNotIn('name', ['super_admin'])->get();
        $profilePermIds = Permission::whereIn('name', ['profile.view', 'profile.update'])->pluck('id')->toArray();
        
        foreach ($allRoles as $role) {
            $role->permissions()->syncWithoutDetaching($profilePermIds);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus permission CMS baru
        Permission::whereIn('name', [
            'cms.about', 'cms.services', 'cms.home', 'cms.contact', 'cms.footer',
            'profile.view', 'profile.update'
        ])->delete();
    }
};

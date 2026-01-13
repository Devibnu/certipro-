<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;

class RbacSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates roles and permissions for CertiPro LSP RBAC system
     */
    public function run(): void
    {
        // ===========================================
        // 1. CREATE ROLES
        // ===========================================
        $roles = [
            [
                'name' => Role::SUPER_ADMIN,
                'display_name' => 'Super Admin',
                'description' => 'Full access - setup, master data, semua fungsi sistem',
            ],
            [
                'name' => Role::ADMIN,
                'display_name' => 'Admin',
                'description' => 'Operasional administrasi LSP - kelola pendaftaran, verifikasi',
            ],
            [
                'name' => Role::ASESOR,
                'display_name' => 'Asesor',
                'description' => 'Pelaksana asesmen - input nilai, lihat data asesi yang diassign',
            ],
            [
                'name' => Role::KOMITE_TEKNIS,
                'display_name' => 'Komite Teknis',
                'description' => 'Penetap keputusan akhir - review hasil asesmen, terbitkan sertifikat',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }

        $this->command->info('✓ Roles created');

        // ===========================================
        // 2. CREATE PERMISSIONS
        // ===========================================
        $permissions = [
            // Dashboard
            ['name' => 'dashboard.view', 'display_name' => 'Lihat Dashboard', 'module' => 'dashboard', 'action' => 'view'],
            
            // Users
            ['name' => 'users.view', 'display_name' => 'Lihat Users', 'module' => 'users', 'action' => 'view'],
            ['name' => 'users.create', 'display_name' => 'Tambah Users', 'module' => 'users', 'action' => 'create'],
            ['name' => 'users.update', 'display_name' => 'Edit Users', 'module' => 'users', 'action' => 'update'],
            ['name' => 'users.delete', 'display_name' => 'Hapus Users', 'module' => 'users', 'action' => 'delete'],
            
            // CMS
            ['name' => 'cms.view', 'display_name' => 'Lihat CMS', 'module' => 'cms', 'action' => 'view'],
            ['name' => 'cms.manage', 'display_name' => 'Kelola CMS', 'module' => 'cms', 'action' => 'manage'],
            
            // Pra-Pendaftaran
            ['name' => 'pra_pendaftaran.view', 'display_name' => 'Lihat Pra-Pendaftaran', 'module' => 'pra_pendaftaran', 'action' => 'view'],
            ['name' => 'pra_pendaftaran.process', 'display_name' => 'Proses Pra-Pendaftaran', 'module' => 'pra_pendaftaran', 'action' => 'process'],
            
            // Pendaftaran Sertifikasi
            ['name' => 'pendaftaran_sertifikasi.view', 'display_name' => 'Lihat Pendaftaran', 'module' => 'pendaftaran_sertifikasi', 'action' => 'view'],
            ['name' => 'pendaftaran_sertifikasi.verify', 'display_name' => 'Verifikasi Pendaftaran', 'module' => 'pendaftaran_sertifikasi', 'action' => 'verify'],
            ['name' => 'pendaftaran_sertifikasi.assign_skema', 'display_name' => 'Assign Skema', 'module' => 'pendaftaran_sertifikasi', 'action' => 'assign'],
            
            // Skema Sertifikasi (Master Data)
            ['name' => 'skema_sertifikasi.view', 'display_name' => 'Lihat Skema', 'module' => 'skema_sertifikasi', 'action' => 'view'],
            ['name' => 'skema_sertifikasi.manage', 'display_name' => 'Kelola Skema', 'module' => 'skema_sertifikasi', 'action' => 'manage'],
            
            // Unit Kompetensi (Master Data)
            ['name' => 'unit_kompetensi.view', 'display_name' => 'Lihat Unit Kompetensi', 'module' => 'unit_kompetensi', 'action' => 'view'],
            ['name' => 'unit_kompetensi.manage', 'display_name' => 'Kelola Unit Kompetensi', 'module' => 'unit_kompetensi', 'action' => 'manage'],
            
            // KUK (Master Data)
            ['name' => 'kuk.view', 'display_name' => 'Lihat KUK', 'module' => 'kuk', 'action' => 'view'],
            ['name' => 'kuk.manage', 'display_name' => 'Kelola KUK', 'module' => 'kuk', 'action' => 'manage'],
            
            // Asesmen
            ['name' => 'asesmen.view', 'display_name' => 'Lihat Asesmen', 'module' => 'asesmen', 'action' => 'view'],
            ['name' => 'asesmen.input', 'display_name' => 'Input Nilai Asesmen', 'module' => 'asesmen', 'action' => 'input'],
            
            // Keputusan Sertifikasi
            ['name' => 'keputusan_sertifikasi.view', 'display_name' => 'Lihat Keputusan', 'module' => 'keputusan_sertifikasi', 'action' => 'view'],
            ['name' => 'keputusan_sertifikasi.decide', 'display_name' => 'Buat Keputusan', 'module' => 'keputusan_sertifikasi', 'action' => 'decide'],
            
            // Sertifikat
            ['name' => 'sertifikat.view', 'display_name' => 'Lihat Sertifikat', 'module' => 'sertifikat', 'action' => 'view'],
            ['name' => 'sertifikat.issue', 'display_name' => 'Terbitkan Sertifikat', 'module' => 'sertifikat', 'action' => 'issue'],
            
            // Audit Log
            ['name' => 'audit_log.view', 'display_name' => 'Lihat Audit Log', 'module' => 'audit_log', 'action' => 'view'],
            ['name' => 'audit_log.export', 'display_name' => 'Export Audit Log', 'module' => 'audit_log', 'action' => 'export'],
            
            // Settings
            ['name' => 'settings.view', 'display_name' => 'Lihat Settings', 'module' => 'settings', 'action' => 'view'],
            ['name' => 'settings.manage', 'display_name' => 'Kelola Settings', 'module' => 'settings', 'action' => 'manage'],
        ];

        foreach ($permissions as $permData) {
            Permission::firstOrCreate(
                ['name' => $permData['name']],
                $permData
            );
        }

        $this->command->info('✓ Permissions created');

        // ===========================================
        // 3. ASSIGN PERMISSIONS TO ROLES
        // ===========================================
        
        // Super Admin - gets ALL permissions (handled in code, but we add anyway)
        $superAdmin = Role::where('name', Role::SUPER_ADMIN)->first();
        $superAdmin->permissions()->sync(Permission::all()->pluck('id'));

        // Admin - operational permissions
        $admin = Role::where('name', Role::ADMIN)->first();
        $adminPermissions = [
            'dashboard.view',
            'users.view',
            'users.create',
            'users.update',
            'cms.view',
            'cms.manage',
            'pra_pendaftaran.view',
            'pra_pendaftaran.process',
            'pendaftaran_sertifikasi.view',
            'pendaftaran_sertifikasi.verify',
            'pendaftaran_sertifikasi.assign_skema',
            'skema_sertifikasi.view',
            'skema_sertifikasi.manage',
            'unit_kompetensi.view',
            'unit_kompetensi.manage',
            'kuk.view',
            'kuk.manage',
            'audit_log.view',
        ];
        $admin->syncPermissions($adminPermissions);

        // Asesor - limited permissions (view + input asesmen)
        $asesor = Role::where('name', Role::ASESOR)->first();
        $asesorPermissions = [
            'dashboard.view',
            'pendaftaran_sertifikasi.view', // Can view assigned pendaftaran
            'asesmen.view',
            'asesmen.input',
        ];
        $asesor->syncPermissions($asesorPermissions);

        // Komite Teknis - decision permissions
        $komiteTeknis = Role::where('name', Role::KOMITE_TEKNIS)->first();
        $komitePermissions = [
            'dashboard.view',
            'pendaftaran_sertifikasi.view',
            'asesmen.view', // Read only
            'keputusan_sertifikasi.view',
            'keputusan_sertifikasi.decide',
            'sertifikat.view',
            'sertifikat.issue',
        ];
        $komiteTeknis->syncPermissions($komitePermissions);

        $this->command->info('✓ Permissions assigned to roles');

        // ===========================================
        // 4. MIGRATE EXISTING USERS TO NEW RBAC
        // ===========================================
        $this->migrateExistingUsers();

        $this->command->info('✓ Existing users migrated to new RBAC');
        $this->command->info('');
        $this->command->info('=== RBAC SEEDING COMPLETED ===');
    }

    /**
     * Migrate existing users from legacy role field to new RBAC
     */
    protected function migrateExistingUsers(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            // Skip if user already has roles
            if ($user->roles()->count() > 0) {
                continue;
            }

            $legacyRole = strtolower($user->role ?? '');
            $newRoleName = null;

            // Map legacy role to new role
            switch ($legacyRole) {
                case 'super admin':
                case 'super_admin':
                case 'superadmin':
                    $newRoleName = Role::SUPER_ADMIN;
                    break;
                case 'admin':
                    $newRoleName = Role::ADMIN;
                    break;
                case 'staff':
                case 'asesor':
                    $newRoleName = Role::ASESOR;
                    break;
                case 'komite_teknis':
                case 'komite teknis':
                    $newRoleName = Role::KOMITE_TEKNIS;
                    break;
            }

            if ($newRoleName) {
                $role = Role::where('name', $newRoleName)->first();
                if ($role) {
                    $user->roles()->attach($role->id);
                    $this->command->line("  Migrated user {$user->email} to role: {$newRoleName}");
                }
            }
        }
    }
}

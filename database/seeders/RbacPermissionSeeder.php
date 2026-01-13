<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

/**
 * ============================================================================
 * RBAC Permission Seeder - ISO 17024 & BNSP Compliant
 * ============================================================================
 * 
 * This seeder creates all roles and permissions required for LSP certification
 * platform compliance with BNSP regulations and ISO 17024 standards.
 * 
 * KEY PRINCIPLES:
 * 1. Separation of Duties - Each role has specific, non-overlapping responsibilities
 * 2. Least Privilege - Users get only permissions they need
 * 3. Audit Trail - All permission assignments are logged
 * 4. Role ≠ Permission - Decoupled for flexibility
 * 
 * ROLES:
 * - super_admin: Full system access, audit oversight
 * - admin: Operational management, pra-pendaftaran, pendaftaran verification
 * - asesor: Assessment input and submission
 * - komite_teknis: Final decision, certificate issuance
 * 
 * @see ISO 17024:2012 Clause 5.1.3 (Personnel competence)
 * @see BNSP Pedoman 201 (Ketentuan Umum LSP)
 */
class RbacPermissionSeeder extends Seeder
{
    /**
     * Permission definitions grouped by module
     * Format: 'permission.name' => ['display_name', 'description']
     */
    protected array $permissions = [
        // ==========================================
        // DASHBOARD MODULE
        // ==========================================
        'dashboard.view' => [
            'Lihat Dashboard',
            'Akses ke halaman dashboard utama'
        ],
        
        // ==========================================
        // PRA-PENDAFTARAN MODULE
        // ==========================================
        'pra_pendaftaran.view' => [
            'Lihat Pra-Pendaftaran',
            'Melihat daftar dan detail pra-pendaftaran'
        ],
        'pra_pendaftaran.manage' => [
            'Kelola Pra-Pendaftaran',
            'Memproses, menyetujui, atau menolak pra-pendaftaran'
        ],
        'pra_pendaftaran.create' => [
            'Buat Pra-Pendaftaran',
            'Membuat pra-pendaftaran manual untuk asesi'
        ],
        
        // ==========================================
        // PENDAFTARAN SERTIFIKASI MODULE
        // ==========================================
        'pendaftaran.view' => [
            'Lihat Pendaftaran Sertifikasi',
            'Melihat daftar dan detail pendaftaran sertifikasi'
        ],
        'pendaftaran.verify' => [
            'Verifikasi Pendaftaran',
            'Melakukan verifikasi dokumen dan kelengkapan pendaftaran'
        ],
        'pendaftaran.assign' => [
            'Assign Skema & Asesor',
            'Menugaskan skema sertifikasi dan asesor ke pendaftaran'
        ],
        'pendaftaran.reject' => [
            'Tolak Pendaftaran',
            'Menolak pendaftaran yang tidak memenuhi syarat'
        ],
        
        // ==========================================
        // ASESMEN MODULE (Asesor Domain)
        // ==========================================
        'asesmen.view' => [
            'Lihat Asesmen',
            'Melihat daftar asesmen yang ditugaskan'
        ],
        'asesmen.submit' => [
            'Input Hasil Asesmen',
            'Menginput dan menyimpan hasil asesmen per KUK'
        ],
        'asesmen.lock' => [
            'Kunci Hasil Asesmen',
            'Mengunci hasil asesmen (finalisasi, tidak dapat diubah)'
        ],
        
        // ==========================================
        // KEPUTUSAN SERTIFIKASI MODULE (Komite Teknis Domain)
        // ==========================================
        'keputusan.view' => [
            'Lihat Keputusan',
            'Melihat daftar pendaftaran yang menunggu keputusan'
        ],
        'keputusan.approve' => [
            'Approve/Reject Keputusan',
            'Memberikan keputusan akhir: Kompeten atau Belum Kompeten'
        ],
        
        // ==========================================
        // SERTIFIKAT MODULE
        // ==========================================
        'sertifikat.view' => [
            'Lihat Sertifikat',
            'Melihat daftar sertifikat yang diterbitkan'
        ],
        'sertifikat.generate' => [
            'Terbitkan Sertifikat',
            'Menerbitkan sertifikat untuk asesi yang kompeten'
        ],
        'sertifikat.download' => [
            'Download Sertifikat',
            'Mengunduh file sertifikat PDF'
        ],
        'sertifikat.revoke' => [
            'Cabut Sertifikat',
            'Mencabut sertifikat yang telah diterbitkan (dengan alasan)'
        ],
        
        // ==========================================
        // MASTER DATA MODULE
        // ==========================================
        'skema.view' => [
            'Lihat Skema Sertifikasi',
            'Melihat daftar skema sertifikasi'
        ],
        'skema.manage' => [
            'Kelola Skema Sertifikasi',
            'Membuat, mengubah, atau menghapus skema sertifikasi'
        ],
        'unit_kompetensi.view' => [
            'Lihat Unit Kompetensi',
            'Melihat daftar unit kompetensi'
        ],
        'unit_kompetensi.manage' => [
            'Kelola Unit Kompetensi',
            'Membuat, mengubah, atau menghapus unit kompetensi'
        ],
        'kuk.view' => [
            'Lihat KUK',
            'Melihat daftar Kriteria Unjuk Kerja'
        ],
        'kuk.manage' => [
            'Kelola KUK',
            'Membuat, mengubah, atau menghapus KUK'
        ],
        
        // ==========================================
        // USER MANAGEMENT MODULE
        // ==========================================
        'users.view' => [
            'Lihat Pengguna',
            'Melihat daftar pengguna sistem'
        ],
        'users.manage' => [
            'Kelola Pengguna',
            'Membuat, mengubah, atau menghapus pengguna'
        ],
        'users.reset_password' => [
            'Reset Password Pengguna',
            'Mengirimkan reset password untuk pengguna lain'
        ],
        
        // ==========================================
        // AUDIT & COMPLIANCE MODULE
        // ==========================================
        'audit.view' => [
            'Lihat Audit Log',
            'Melihat riwayat audit trail sistem'
        ],
        'audit.export' => [
            'Export Audit Log',
            'Mengekspor audit log untuk keperluan audit BNSP'
        ],
        'audit.statistics' => [
            'Lihat Statistik Audit',
            'Melihat statistik dan analisis audit'
        ],
        
        // ==========================================
        // SYSTEM SETTINGS MODULE
        // ==========================================
        'settings.view' => [
            'Lihat Pengaturan',
            'Melihat pengaturan sistem'
        ],
        'settings.manage' => [
            'Kelola Pengaturan',
            'Mengubah pengaturan sistem (email, branding, dll)'
        ],
        
        // ==========================================
        // CMS MODULE
        // ==========================================
        'cms.view' => [
            'Lihat CMS',
            'Melihat halaman dan konten CMS'
        ],
        'cms.manage' => [
            'Kelola CMS',
            'Membuat, mengubah, atau menghapus konten CMS'
        ],
    ];

    /**
     * Role-Permission mapping
     * super_admin gets "*" (all permissions) - handled in middleware
     */
    protected array $rolePermissions = [
        Role::SUPER_ADMIN => '*', // Wildcard - full access
        
        Role::ADMIN => [
            'dashboard.view',
            // Pra-Pendaftaran
            'pra_pendaftaran.view',
            'pra_pendaftaran.manage',
            'pra_pendaftaran.create',
            // Pendaftaran
            'pendaftaran.view',
            'pendaftaran.verify',
            'pendaftaran.assign',
            'pendaftaran.reject',
            // Master Data
            'skema.view',
            'skema.manage',
            'unit_kompetensi.view',
            'unit_kompetensi.manage',
            'kuk.view',
            'kuk.manage',
            // Users
            'users.view',
            'users.manage',
            'users.reset_password',
            // Audit (view only)
            'audit.view',
            // CMS
            'cms.view',
            'cms.manage',
        ],
        
        Role::ASESOR => [
            'dashboard.view',
            // Asesmen (their domain)
            'asesmen.view',
            'asesmen.submit',
            // Read-only for reference
            'pendaftaran.view',
            'skema.view',
            'unit_kompetensi.view',
            'kuk.view',
        ],
        
        Role::KOMITE_TEKNIS => [
            'dashboard.view',
            // Keputusan (their domain)
            'keputusan.view',
            'keputusan.approve',
            // Sertifikat (their domain)
            'sertifikat.view',
            'sertifikat.generate',
            'sertifikat.download',
            // Read-only for reference
            'pendaftaran.view',
            'asesmen.view',
            'skema.view',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->createRoles();
            $this->createPermissions();
            $this->assignPermissionsToRoles();
            $this->logSeederExecution();
        });
        
        $this->command->info('✅ RBAC Permissions seeded successfully!');
        $this->command->info('   Roles: ' . Role::count());
        $this->command->info('   Permissions: ' . Permission::count());
    }

    /**
     * Create all roles
     */
    protected function createRoles(): void
    {
        $roles = [
            [
                'name' => Role::SUPER_ADMIN,
                'display_name' => 'Super Admin',
                'description' => 'Full system access. Oversees all operations and audit compliance. ISO 17024 Management Representative.',
            ],
            [
                'name' => Role::ADMIN,
                'display_name' => 'Admin',
                'description' => 'Operational administrator. Manages pra-pendaftaran, pendaftaran verification, master data, and users.',
            ],
            [
                'name' => Role::ASESOR,
                'display_name' => 'Asesor',
                'description' => 'Certified assessor. Conducts assessments and inputs results per KUK. BNSP-registered.',
            ],
            [
                'name' => Role::KOMITE_TEKNIS,
                'display_name' => 'Komite Teknis',
                'description' => 'Technical committee member. Makes final certification decisions and issues certificates.',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }

        $this->command->info('   Created/Updated ' . count($roles) . ' roles');
    }

    /**
     * Create all permissions
     */
    protected function createPermissions(): void
    {
        foreach ($this->permissions as $name => $data) {
            $parts = explode('.', $name);
            $module = $parts[0] ?? 'general';
            $action = $parts[1] ?? 'access';

            Permission::updateOrCreate(
                ['name' => $name],
                [
                    'display_name' => $data[0],
                    'module' => $module,
                    'action' => $action,
                    'description' => $data[1] ?? null,
                ]
            );
        }

        $this->command->info('   Created/Updated ' . count($this->permissions) . ' permissions');
    }

    /**
     * Assign permissions to roles
     */
    protected function assignPermissionsToRoles(): void
    {
        foreach ($this->rolePermissions as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();
            
            if (!$role) {
                $this->command->warn("   Role not found: {$roleName}");
                continue;
            }

            // Super Admin gets all permissions
            if ($permissions === '*') {
                $allPermissionIds = Permission::pluck('id')->toArray();
                $role->permissions()->sync($allPermissionIds);
                $this->command->info("   Assigned ALL permissions to {$roleName}");
                continue;
            }

            // Other roles get specific permissions
            $permissionIds = Permission::whereIn('name', $permissions)->pluck('id')->toArray();
            $role->permissions()->sync($permissionIds);
            $this->command->info("   Assigned " . count($permissionIds) . " permissions to {$roleName}");
        }
    }

    /**
     * Log seeder execution for audit trail
     */
    protected function logSeederExecution(): void
    {
        AuditLog::create([
            'user_id' => null,
            'user_name' => 'System',
            'user_role' => 'system',
            'action' => 'seeder',
            'module' => 'rbac',
            'description' => 'RBAC Permissions seeded. Roles: ' . Role::count() . ', Permissions: ' . Permission::count(),
            'metadata' => [
                'roles' => Role::pluck('name')->toArray(),
                'total_permissions' => Permission::count(),
                'iso_17024_compliance' => true,
            ],
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);
    }
}

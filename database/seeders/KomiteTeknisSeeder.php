<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class KomiteTeknisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates a Komite Teknis user for CertiPro LSP System
     * 
     * Role: komite_teknis
     * Responsibilities:
     * - Menetapkan keputusan sertifikasi (KOMPETEN / BELUM KOMPETEN)
     * - Menerbitkan sertifikat
     * - TIDAK boleh mengedit asesmen, skema, unit, atau KUK
     * 
     * Audit BNSP Compliant: Terpisah dari Admin & Asesor
     */
    public function run(): void
    {
        // Permission khusus untuk Komite Teknis
        $komiteTeknisPermissions = [
            'Dashboard',
            'Keputusan Sertifikasi',
            'Sertifikat',
            'Profile',
        ];

        DB::table('users')->updateOrInsert(
            ['email' => 'komite@certipro.id'],
            [
                'name' => 'Komite Teknis',
                'email' => 'komite@certipro.id',
                'password' => Hash::make('komite123'),
                'role' => 'komite_teknis',
                'permissions' => json_encode($komiteTeknisPermissions),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->command->info('✅ Komite Teknis user created successfully!');
        $this->command->info('   Email: komite@certipro.id');
        $this->command->info('   Password: komite123');
        $this->command->info('   Role: komite_teknis');
    }
}

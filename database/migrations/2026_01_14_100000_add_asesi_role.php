<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan role ASESI dengan permissions yang sesuai
     * untuk peserta sertifikasi
     */
    public function up(): void
    {
        // 1. Buat role asesi
        $asesiId = DB::table('roles')->insertGetId([
            'name' => 'asesi',
            'display_name' => 'Asesi',
            'description' => 'Peserta sertifikasi yang mendaftar dan mengikuti asesmen',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Dapatkan permission IDs yang diperlukan asesi
        $permissionNames = [
            'dashboard.view',           // akses dashboard
            'pra_pendaftaran.create',   // bisa mendaftar
            'pendaftaran.view',         // lihat status pendaftaran sendiri
            'asesmen.view',             // lihat hasil asesmen sendiri
            'sertifikat.view',          // lihat sertifikat sendiri
            'sertifikat.download',      // download sertifikat sendiri
            'skema.view',               // lihat skema yang tersedia
        ];

        $permissions = DB::table('permissions')
            ->whereIn('name', $permissionNames)
            ->pluck('id');

        // 3. Attach permissions ke role asesi
        foreach ($permissions as $permId) {
            DB::table('role_permission')->insert([
                'role_id' => $asesiId,
                'permission_id' => $permId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $role = DB::table('roles')->where('name', 'asesi')->first();
        if ($role) {
            DB::table('role_permission')->where('role_id', $role->id)->delete();
            DB::table('roles')->where('id', $role->id)->delete();
        }
    }
};

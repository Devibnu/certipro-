<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * ============================================================================
 * RBAC Schema Simplification - ISO 17024 & BNSP Compliant
 * ============================================================================
 * 
 * This migration simplifies the RBAC structure:
 * - One User = One Role (via role_id FK on users table)
 * - user_role pivot table deprecated
 * - Migrates existing user_role data to new role_id column
 * 
 * DESIGN PRINCIPLE:
 * Dalam LSP, setiap personel memiliki SATU peran yang jelas:
 * - Admin (Sekretariat)
 * - Asesor
 * - Komite Teknis
 * - Super Admin (IT/Management)
 * 
 * @see ISO 17024:2012 Clause 5.1.3 (Personnel competence)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add role_id column to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')
                ->nullable()
                ->after('id')
                ->constrained('roles')
                ->nullOnDelete();
        });

        // Migrate existing user_role data to new role_id column
        $this->migrateUserRoles();
    }

    /**
     * Migrate existing user_role pivot data to role_id on users
     */
    protected function migrateUserRoles(): void
    {
        // Get all users with roles from pivot table
        $userRoles = DB::table('user_role')
            ->select('user_id', 'role_id')
            ->orderBy('created_at', 'desc') // Take most recent if multiple
            ->get()
            ->unique('user_id'); // One role per user

        foreach ($userRoles as $userRole) {
            DB::table('users')
                ->where('id', $userRole->user_id)
                ->update(['role_id' => $userRole->role_id]);
        }

        // Also migrate from legacy 'role' column if role_id is still null
        $this->migrateLegacyRoles();
    }

    /**
     * Migrate legacy role column values to role_id
     */
    protected function migrateLegacyRoles(): void
    {
        $legacyMappings = [
            'super admin' => 'super_admin',
            'super_admin' => 'super_admin',
            'superadmin' => 'super_admin',
            'admin' => 'admin',
            'staff' => 'asesor',
            'asesor' => 'asesor',
            'komite_teknis' => 'komite_teknis',
            'komite teknis' => 'komite_teknis',
        ];

        $users = DB::table('users')
            ->whereNull('role_id')
            ->whereNotNull('role')
            ->get();

        foreach ($users as $user) {
            $legacyRole = strtolower($user->role ?? '');
            $mappedRole = $legacyMappings[$legacyRole] ?? null;

            if ($mappedRole) {
                $role = DB::table('roles')->where('name', $mappedRole)->first();
                if ($role) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['role_id' => $role->id]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
    }
};

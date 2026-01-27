<?php
/**
 * ============================================================================
 * CLEANUP SCRIPT: Hapus Data Peserta Spesifik
 * ============================================================================
 * 
 * Target: ibnuqosim022@gmail.com
 * Scope: PRODUCTION Database
 * Safety: Transaction with Rollback
 * 
 * ============================================================================
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "\n";
echo "================================================================================\n";
echo "CLEANUP DATA PESERTA: ibnuqosim022@gmail.com\n";
echo "================================================================================\n";
echo "Environment: " . app()->environment() . "\n";
echo "Database: " . config('database.connections.mysql.database') . "\n";
echo "Timestamp: " . now()->format('Y-m-d H:i:s') . "\n";
echo "================================================================================\n\n";

// Target email
$targetEmail = 'ibnuqosim022@gmail.com';

// Confirmation
echo "⚠️  WARNING: This will DELETE all data for email: {$targetEmail}\n";
echo "Press ENTER to continue or Ctrl+C to cancel...";
// fgets(STDIN); // Uncomment untuk konfirmasi manual

echo "\n🔍 STEP 1: Finding user and related data...\n\n";

try {
    // ========================================================================
    // STEP 1: Find User
    // ========================================================================
    $user = DB::table('users')->where('email', $targetEmail)->first();
    
    if (!$user) {
        echo "❌ User with email {$targetEmail} NOT FOUND.\n";
        echo "Nothing to delete. Exiting.\n\n";
        exit(0);
    }
    
    echo "✓ Found User:\n";
    echo "  - ID: {$user->id}\n";
    echo "  - Name: {$user->name}\n";
    echo "  - Email: {$user->email}\n\n";
    
    $userId = $user->id;
    
    // ========================================================================
    // STEP 2: Count Related Data
    // ========================================================================
    echo "📊 Counting related data...\n\n";
    
    $praPendaftaranCount = DB::table('pra_pendaftaran')->where('user_id', $userId)->count();
    echo "  - Pra Pendaftaran: {$praPendaftaranCount}\n";
    
    $pendaftaranCount = DB::table('pendaftaran_sertifikasi')->where('user_id', $userId)->count();
    echo "  - Pendaftaran Sertifikasi: {$pendaftaranCount}\n";
    
    $asesmenCount = DB::table('asesmen')
        ->whereIn('pendaftaran_id', function($query) use ($userId) {
            $query->select('id')
                ->from('pendaftaran_sertifikasi')
                ->where('user_id', $userId);
        })->count();
    echo "  - Asesmen: {$asesmenCount}\n";
    
    $keputusanCount = DB::table('keputusan_sertifikasi')
        ->whereIn('pendaftaran_id', function($query) use ($userId) {
            $query->select('id')
                ->from('pendaftaran_sertifikasi')
                ->where('user_id', $userId);
        })->count();
    echo "  - Keputusan Sertifikasi: {$keputusanCount}\n";
    
    $sertifikatCount = DB::table('sertifikat')
        ->whereIn('pendaftaran_id', function($query) use ($userId) {
            $query->select('id')
                ->from('pendaftaran_sertifikasi')
                ->where('user_id', $userId);
        })->count();
    echo "  - Sertifikat: {$sertifikatCount}\n\n";
    
    if ($praPendaftaranCount === 0 && $pendaftaranCount === 0) {
        echo "ℹ️  No registration data found for this user.\n";
        echo "Only user account will be deleted.\n\n";
    }
    
    // ========================================================================
    // STEP 3: START TRANSACTION & DELETE
    // ========================================================================
    echo "🗑️  STEP 2: Starting deletion process...\n\n";
    
    DB::beginTransaction();
    
    try {
        $deletedCount = [];
        
        // Get pendaftaran IDs for reference
        $pendaftaranIds = DB::table('pendaftaran_sertifikasi')
            ->where('user_id', $userId)
            ->pluck('id')
            ->toArray();
        
        // Get pra_pendaftaran IDs for reference
        $praPendaftaranIds = DB::table('pra_pendaftaran')
            ->where('user_id', $userId)
            ->pluck('id')
            ->toArray();
        
        // Get asesmen IDs
        $asesmenIds = [];
        if (!empty($pendaftaranIds)) {
            $asesmenIds = DB::table('asesmen')
                ->whereIn('pendaftaran_id', $pendaftaranIds)
                ->pluck('id')
                ->toArray();
        }
        
        echo "📋 IDs to be deleted:\n";
        echo "  - Pendaftaran IDs: " . implode(', ', $pendaftaranIds) . "\n";
        echo "  - Pra-Pendaftaran IDs: " . implode(', ', $praPendaftaranIds) . "\n";
        echo "  - Asesmen IDs: " . implode(', ', $asesmenIds) . "\n\n";
        
        // ====================================================================
        // DELETE 1: Sertifikat (paling turunan)
        // ====================================================================
        if (!empty($pendaftaranIds)) {
            $deleted = DB::table('sertifikat')
                ->whereIn('pendaftaran_id', $pendaftaranIds)
                ->delete();
            $deletedCount['sertifikat'] = $deleted;
            echo "✓ Deleted Sertifikat: {$deleted}\n";
        }
        
        // ====================================================================
        // DELETE 2: Keputusan Sertifikasi
        // ====================================================================
        if (!empty($pendaftaranIds)) {
            $deleted = DB::table('keputusan_sertifikasi')
                ->whereIn('pendaftaran_id', $pendaftaranIds)
                ->delete();
            $deletedCount['keputusan_sertifikasi'] = $deleted;
            echo "✓ Deleted Keputusan Sertifikasi: {$deleted}\n";
        }
        
        // ====================================================================
        // DELETE 3: Evidence KUK
        // ====================================================================
        if (!empty($asesmenIds)) {
            $deleted = DB::table('evidence_kuk')
                ->whereIn('asesmen_id', $asesmenIds)
                ->delete();
            $deletedCount['evidence_kuk'] = $deleted;
            echo "✓ Deleted Evidence KUK: {$deleted}\n";
        }
        
        // ====================================================================
        // DELETE 4: Asesmen Detail
        // ====================================================================
        if (!empty($asesmenIds)) {
            $deleted = DB::table('asesmen_detail')
                ->whereIn('asesmen_id', $asesmenIds)
                ->delete();
            $deletedCount['asesmen_detail'] = $deleted;
            echo "✓ Deleted Asesmen Detail: {$deleted}\n";
        }
        
        // ====================================================================
        // DELETE 5: Asesmen
        // ====================================================================
        if (!empty($pendaftaranIds)) {
            $deleted = DB::table('asesmen')
                ->whereIn('pendaftaran_id', $pendaftaranIds)
                ->delete();
            $deletedCount['asesmen'] = $deleted;
            echo "✓ Deleted Asesmen: {$deleted}\n";
        }
        
        // ====================================================================
        // DELETE 6: Pendaftaran Sertifikasi
        // ====================================================================
        $deleted = DB::table('pendaftaran_sertifikasi')
            ->where('user_id', $userId)
            ->delete();
        $deletedCount['pendaftaran_sertifikasi'] = $deleted;
        echo "✓ Deleted Pendaftaran Sertifikasi: {$deleted}\n";
        
        // ====================================================================
        // DELETE 7: Pra-Pendaftaran
        // ====================================================================
        $deleted = DB::table('pra_pendaftaran')
            ->where('user_id', $userId)
            ->delete();
        $deletedCount['pra_pendaftaran'] = $deleted;
        echo "✓ Deleted Pra-Pendaftaran: {$deleted}\n";
        
        // ====================================================================
        // DELETE 8: Notification Logs (if exists)
        // ====================================================================
        if (Schema::hasTable('notification_logs')) {
            $deleted = DB::table('notification_logs')
                ->where('user_id', $userId)
                ->delete();
            $deletedCount['notification_logs'] = $deleted;
            echo "✓ Deleted Notification Logs: {$deleted}\n";
        }
        
        // ====================================================================
        // DELETE 9: Audit Logs (optional - terkait user)
        // ====================================================================
        if (Schema::hasTable('audit_logs')) {
            $deleted = DB::table('audit_logs')
                ->where('user_id', $userId)
                ->delete();
            $deletedCount['audit_logs'] = $deleted;
            echo "✓ Deleted Audit Logs: {$deleted}\n";
        }
        
        // ====================================================================
        // DELETE 10: User (terakhir)
        // ====================================================================
        $deleted = DB::table('users')
            ->where('id', $userId)
            ->where('email', $targetEmail) // Double check
            ->delete();
        $deletedCount['users'] = $deleted;
        echo "✓ Deleted User: {$deleted}\n\n";
        
        // ====================================================================
        // COMMIT TRANSACTION
        // ====================================================================
        DB::commit();
        
        echo "✅ TRANSACTION COMMITTED SUCCESSFULLY!\n\n";
        
        // ====================================================================
        // STEP 4: VALIDATION
        // ====================================================================
        echo "🔍 STEP 3: Validating deletion...\n\n";
        
        $remainingUser = DB::table('users')->where('email', $targetEmail)->count();
        $remainingPra = DB::table('pra_pendaftaran')->where('user_id', $userId)->count();
        $remainingPendaftaran = DB::table('pendaftaran_sertifikasi')->where('user_id', $userId)->count();
        
        echo "Validation Results:\n";
        echo "  - User with email {$targetEmail}: {$remainingUser} (should be 0)\n";
        echo "  - Pra-Pendaftaran: {$remainingPra} (should be 0)\n";
        echo "  - Pendaftaran: {$remainingPendaftaran} (should be 0)\n\n";
        
        if ($remainingUser === 0 && $remainingPra === 0 && $remainingPendaftaran === 0) {
            echo "✅ VALIDATION PASSED: All data successfully deleted!\n\n";
        } else {
            echo "⚠️  WARNING: Some data still remains!\n\n";
        }
        
        // ====================================================================
        // SUMMARY
        // ====================================================================
        echo "================================================================================\n";
        echo "DELETION SUMMARY\n";
        echo "================================================================================\n";
        echo "Target Email: {$targetEmail}\n";
        echo "User ID: {$userId}\n";
        echo "Timestamp: " . now()->format('Y-m-d H:i:s') . "\n\n";
        
        echo "Records Deleted:\n";
        foreach ($deletedCount as $table => $count) {
            echo "  - " . str_pad($table, 30) . ": {$count}\n";
        }
        
        $totalDeleted = array_sum($deletedCount);
        echo "\nTotal Records Deleted: {$totalDeleted}\n";
        echo "================================================================================\n\n";
        
        // Log to Laravel log
        Log::info('[CLEANUP] Data deleted for user', [
            'email' => $targetEmail,
            'user_id' => $userId,
            'deleted_count' => $deletedCount,
            'total' => $totalDeleted,
            'timestamp' => now()->toDateTimeString(),
        ]);
        
        echo "✅ CLEANUP COMPLETED SUCCESSFULLY!\n";
        echo "System ready for fresh test.\n\n";
        
    } catch (\Exception $e) {
        // ====================================================================
        // ROLLBACK ON ERROR
        // ====================================================================
        DB::rollBack();
        
        echo "\n❌ ERROR OCCURRED - TRANSACTION ROLLED BACK!\n\n";
        echo "Error: {$e->getMessage()}\n";
        echo "File: {$e->getFile()}:{$e->getLine()}\n\n";
        
        Log::error('[CLEANUP] Failed to delete data', [
            'email' => $targetEmail,
            'user_id' => $userId ?? null,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        
        echo "All changes have been ROLLED BACK.\n";
        echo "No data was deleted.\n\n";
        
        exit(1);
    }
    
} catch (\Exception $e) {
    echo "\n❌ FATAL ERROR!\n\n";
    echo "Error: {$e->getMessage()}\n";
    echo "File: {$e->getFile()}:{$e->getLine()}\n\n";
    
    exit(1);
}

echo "Script completed.\n\n";
exit(0);

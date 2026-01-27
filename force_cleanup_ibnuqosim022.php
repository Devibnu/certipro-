<?php
/**
 * ============================================================================
 * FORCE CLEANUP: Hapus SEMUA data pendaftaran by EMAIL
 * ============================================================================
 * 
 * Target: ibnuqosim022@gmail.com
 * Entry Point: pra_pendaftaran (bukan users)
 * Database: PRODUCTION
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
echo "FORCE CLEANUP - DATA PENDAFTARAN BY EMAIL\n";
echo "================================================================================\n";
echo "Target Email: ibnuqosim022@gmail.com\n";
echo "Database: " . config('database.connections.mysql.database') . "\n";
echo "Environment: " . app()->environment() . "\n";
echo "Timestamp: " . now()->format('Y-m-d H:i:s') . "\n";
echo "================================================================================\n\n";

$targetEmail = 'ibnuqosim022@gmail.com';

try {
    // ========================================================================
    // STEP 1: Find PRA-PENDAFTARAN (ENTRY POINT)
    // ========================================================================
    echo "🔍 STEP 1: Checking pra_pendaftaran...\n\n";
    
    $praPendaftarans = DB::table('pra_pendaftaran')
        ->where('email', $targetEmail)
        ->get();
    
    if ($praPendaftarans->isEmpty()) {
        echo "✓ No pra_pendaftaran found with email: {$targetEmail}\n\n";
    } else {
        echo "Found {$praPendaftarans->count()} pra_pendaftaran records:\n";
        foreach ($praPendaftarans as $pra) {
            echo "  - ID: {$pra->id}, Status: {$pra->status}, Created: {$pra->created_at}\n";
        }
        echo "\n";
    }
    
    $praPendaftaranIds = $praPendaftarans->pluck('id')->toArray();
    
    // ========================================================================
    // STEP 2: Find PENDAFTARAN SERTIFIKASI
    // ========================================================================
    echo "🔍 STEP 2: Checking pendaftaran_sertifikasi...\n\n";
    
    $pendaftarans = collect();
    if (!empty($praPendaftaranIds)) {
        $pendaftarans = DB::table('pendaftaran_sertifikasi')
            ->whereIn('pra_pendaftaran_id', $praPendaftaranIds)
            ->get();
    }
    
    // Also check by email directly (backup check)
    $pendaftaransByEmail = DB::table('pendaftaran_sertifikasi')
        ->where('email', $targetEmail)
        ->get();
    
    $allPendaftarans = $pendaftarans->merge($pendaftaransByEmail)->unique('id');
    
    if ($allPendaftarans->isEmpty()) {
        echo "✓ No pendaftaran_sertifikasi found\n\n";
    } else {
        echo "Found {$allPendaftarans->count()} pendaftaran_sertifikasi records:\n";
        foreach ($allPendaftarans as $p) {
            echo "  - ID: {$p->id}, Nomor: {$p->nomor_pendaftaran}, Status: {$p->status}\n";
        }
        echo "\n";
    }
    
    $pendaftaranIds = $allPendaftarans->pluck('id')->toArray();
    
    // ========================================================================
    // STEP 3: Count Related Data
    // ========================================================================
    echo "📊 STEP 3: Counting related data...\n\n";
    
    $counts = [
        'pra_pendaftaran' => count($praPendaftaranIds),
        'pendaftaran_sertifikasi' => count($pendaftaranIds),
    ];
    
    if (!empty($pendaftaranIds)) {
        $counts['sertifikat'] = DB::table('sertifikat')->whereIn('pendaftaran_id', $pendaftaranIds)->count();
        $counts['keputusan_sertifikasi'] = DB::table('keputusan_sertifikasi')->whereIn('pendaftaran_id', $pendaftaranIds)->count();
        $counts['asesmen'] = DB::table('asesmen')->whereIn('pendaftaran_id', $pendaftaranIds)->count();
        
        // Get asesmen IDs for detail cleanup
        $asesmenIds = DB::table('asesmen')->whereIn('pendaftaran_id', $pendaftaranIds)->pluck('id')->toArray();
        if (!empty($asesmenIds)) {
            $counts['asesmen_detail'] = DB::table('asesmen_detail')->whereIn('asesmen_id', $asesmenIds)->count();
            $counts['evidence_kuk'] = DB::table('evidence_kuk')->whereIn('asesmen_id', $asesmenIds)->count();
        }
    }
    
    // Check notifications
    if (Schema::hasTable('notifications')) {
        $counts['notifications'] = DB::table('notifications')
            ->where('data', 'like', "%{$targetEmail}%")
            ->count();
    }
    
    // Check notification_logs if exists
    if (Schema::hasTable('notification_logs')) {
        $counts['notification_logs'] = DB::table('notification_logs')
            ->where('recipient_email', $targetEmail)
            ->count();
    }
    
    echo "Data Summary:\n";
    foreach ($counts as $table => $count) {
        echo "  - " . str_pad($table, 30) . ": {$count}\n";
    }
    echo "\n";
    
    $totalRecords = array_sum($counts);
    
    if ($totalRecords === 0) {
        echo "✅ DATABASE IS CLEAN!\n";
        echo "No data found for email: {$targetEmail}\n\n";
        echo "System ready for fresh registration.\n\n";
        exit(0);
    }
    
    echo "⚠️  Total records to delete: {$totalRecords}\n\n";
    
    // ========================================================================
    // STEP 4: BEGIN TRANSACTION & DELETE
    // ========================================================================
    echo "🗑️  STEP 4: Starting deletion (with transaction)...\n\n";
    
    DB::beginTransaction();
    
    try {
        $deleted = [];
        
        // ====================================================================
        // DELETE HIERARCHY (dari turunan ke induk)
        // ====================================================================
        
        // 1. Evidence KUK
        if (!empty($asesmenIds)) {
            $count = DB::table('evidence_kuk')
                ->whereIn('asesmen_id', $asesmenIds)
                ->delete();
            $deleted['evidence_kuk'] = $count;
            echo "✓ Deleted evidence_kuk: {$count}\n";
        }
        
        // 2. Asesmen Detail
        if (!empty($asesmenIds)) {
            $count = DB::table('asesmen_detail')
                ->whereIn('asesmen_id', $asesmenIds)
                ->delete();
            $deleted['asesmen_detail'] = $count;
            echo "✓ Deleted asesmen_detail: {$count}\n";
        }
        
        // 3. Sertifikat
        if (!empty($pendaftaranIds)) {
            $count = DB::table('sertifikat')
                ->whereIn('pendaftaran_id', $pendaftaranIds)
                ->delete();
            $deleted['sertifikat'] = $count;
            echo "✓ Deleted sertifikat: {$count}\n";
        }
        
        // 4. Keputusan Sertifikasi
        if (!empty($pendaftaranIds)) {
            $count = DB::table('keputusan_sertifikasi')
                ->whereIn('pendaftaran_id', $pendaftaranIds)
                ->delete();
            $deleted['keputusan_sertifikasi'] = $count;
            echo "✓ Deleted keputusan_sertifikasi: {$count}\n";
        }
        
        // 5. Asesmen
        if (!empty($pendaftaranIds)) {
            $count = DB::table('asesmen')
                ->whereIn('pendaftaran_id', $pendaftaranIds)
                ->delete();
            $deleted['asesmen'] = $count;
            echo "✓ Deleted asesmen: {$count}\n";
        }
        
        // 6. Pendaftaran Sertifikasi
        if (!empty($praPendaftaranIds)) {
            $count = DB::table('pendaftaran_sertifikasi')
                ->whereIn('pra_pendaftaran_id', $praPendaftaranIds)
                ->delete();
            $deleted['pendaftaran_sertifikasi_by_pra'] = $count;
            echo "✓ Deleted pendaftaran_sertifikasi (by pra_id): {$count}\n";
        }
        
        // Also delete by email (backup)
        $count = DB::table('pendaftaran_sertifikasi')
            ->where('email', $targetEmail)
            ->delete();
        if ($count > 0) {
            $deleted['pendaftaran_sertifikasi_by_email'] = $count;
            echo "✓ Deleted pendaftaran_sertifikasi (by email): {$count}\n";
        }
        
        // 7. Notifications
        if (Schema::hasTable('notifications')) {
            $count = DB::table('notifications')
                ->where('data', 'like', "%{$targetEmail}%")
                ->delete();
            $deleted['notifications'] = $count;
            echo "✓ Deleted notifications: {$count}\n";
        }
        
        // 8. Notification Logs
        if (Schema::hasTable('notification_logs')) {
            $count = DB::table('notification_logs')
                ->where('recipient_email', $targetEmail)
                ->delete();
            $deleted['notification_logs'] = $count;
            echo "✓ Deleted notification_logs: {$count}\n";
        }
        
        // 9. Pra-Pendaftaran (INDUK - HAPUS TERAKHIR)
        $count = DB::table('pra_pendaftaran')
            ->where('email', $targetEmail)
            ->delete();
        $deleted['pra_pendaftaran'] = $count;
        echo "✓ Deleted pra_pendaftaran: {$count}\n";
        
        echo "\n";
        
        // ====================================================================
        // COMMIT
        // ====================================================================
        DB::commit();
        
        echo "✅ TRANSACTION COMMITTED!\n\n";
        
        // ====================================================================
        // VALIDATION
        // ====================================================================
        echo "🔍 STEP 5: Validation...\n\n";
        
        $remainingPra = DB::table('pra_pendaftaran')->where('email', $targetEmail)->count();
        $remainingPendaftaran = DB::table('pendaftaran_sertifikasi')->where('email', $targetEmail)->count();
        
        echo "Validation Results:\n";
        echo "  - Pra Pendaftaran: {$remainingPra} (should be 0)\n";
        echo "  - Pendaftaran: {$remainingPendaftaran} (should be 0)\n\n";
        
        if ($remainingPra === 0 && $remainingPendaftaran === 0) {
            echo "✅ VALIDATION PASSED!\n\n";
        } else {
            echo "⚠️  WARNING: Some data still exists!\n\n";
        }
        
        // ====================================================================
        // SUMMARY
        // ====================================================================
        echo "================================================================================\n";
        echo "DELETION SUMMARY\n";
        echo "================================================================================\n";
        echo "Email: {$targetEmail}\n";
        echo "Timestamp: " . now()->format('Y-m-d H:i:s') . "\n\n";
        
        echo "Records Deleted:\n";
        $totalDeleted = 0;
        foreach ($deleted as $table => $count) {
            echo "  - " . str_pad($table, 40) . ": {$count}\n";
            $totalDeleted += $count;
        }
        
        echo "\nTotal: {$totalDeleted} records\n";
        echo "================================================================================\n\n";
        
        // Log
        Log::info('[FORCE CLEANUP] Data deleted', [
            'email' => $targetEmail,
            'deleted' => $deleted,
            'total' => $totalDeleted,
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        
        echo "\n❌ ERROR - TRANSACTION ROLLED BACK!\n\n";
        echo "Error: {$e->getMessage()}\n";
        echo "File: {$e->getFile()}:{$e->getLine()}\n\n";
        
        Log::error('[FORCE CLEANUP] Failed', [
            'email' => $targetEmail,
            'error' => $e->getMessage(),
        ]);
        
        exit(1);
    }
    
} catch (\Exception $e) {
    echo "\n❌ FATAL ERROR!\n\n";
    echo "Error: {$e->getMessage()}\n\n";
    exit(1);
}

// ============================================================================
// CLEAR CACHE
// ============================================================================
echo "🧹 STEP 6: Clearing cache...\n\n";

Artisan::call('cache:clear');
echo "✓ Cache cleared\n";

Artisan::call('config:clear');
echo "✓ Config cleared\n";

Artisan::call('view:clear');
echo "✓ View cleared\n";

Artisan::call('route:clear');
echo "✓ Route cleared\n";

echo "\n✅ CLEANUP COMPLETED SUCCESSFULLY!\n";
echo "System ready for fresh registration.\n\n";

exit(0);

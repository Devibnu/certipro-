<?php

/**
 * Script untuk hapus SEMUA data pendaftaran (TESTING ONLY!)
 * WARNING: Ini akan menghapus semua data pra-pendaftaran dan pendaftaran sertifikasi
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "========================================\n";
echo "🗑️  HAPUS SEMUA DATA PENDAFTARAN (DANGER!)\n";
echo "========================================\n\n";

try {
    // Count data
    $countPraPendaftaran = DB::table('pra_pendaftaran')->count();
    $countPendaftaran = DB::table('pendaftaran_sertifikasi')->count();
    
    echo "📊 Data yang akan dihapus:\n";
    echo "   Pra-Pendaftaran: {$countPraPendaftaran} record(s)\n";
    echo "   Pendaftaran Sertifikasi: {$countPendaftaran} record(s)\n\n";
    
    if ($countPraPendaftaran == 0 && $countPendaftaran == 0) {
        echo "✅ Tidak ada data untuk dihapus.\n";
        exit(0);
    }
    
    echo "⚠️  WARNING: Ini akan menghapus SEMUA data!\n";
    echo "🗑️  Menghapus data...\n\n";
    
    DB::beginTransaction();
    
    // 1. Hapus pendaftaran_sertifikasi (akan cascade delete relasi lain)
    $deletedPendaftaran = DB::table('pendaftaran_sertifikasi')->delete();
    echo "   ✅ Pendaftaran Sertifikasi: {$deletedPendaftaran} deleted\n";
    
    // 2. Hapus pra_pendaftaran
    $deletedPraPendaftaran = DB::table('pra_pendaftaran')->delete();
    echo "   ✅ Pra-Pendaftaran: {$deletedPraPendaftaran} deleted\n";
    
    // 3. Hapus audit logs (optional)
    $deletedAuditLogs = DB::table('audit_logs')
        ->whereIn('module', ['pra_pendaftaran', 'pendaftaran'])
        ->delete();
    echo "   ✅ Audit Logs: {$deletedAuditLogs} deleted\n";
    
    DB::commit();
    
    echo "\n========================================\n";
    echo "✅ SEMUA DATA BERHASIL DIHAPUS\n";
    echo "========================================\n";
    echo "Database sudah bersih dan siap untuk testing fresh.\n\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

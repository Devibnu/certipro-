<?php

/**
 * Script untuk hapus data testing: ibnuqosim022@gmail.com
 * NON-INTERACTIVE VERSION (langsung hapus tanpa konfirmasi)
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$email = 'ibnuqosim022@gmail.com';

echo "========================================\n";
echo "🗑️  HAPUS DATA TESTING (AUTO)\n";
echo "========================================\n";
echo "Email: {$email}\n\n";

try {
    DB::beginTransaction();
    
    // 1. Cari pra_pendaftaran
    $praPendaftaran = DB::table('pra_pendaftaran')
        ->where('email', $email)
        ->get();
    
    echo "📋 Data ditemukan:\n";
    echo "   Pra-Pendaftaran: " . $praPendaftaran->count() . " record(s)\n";
    
    if ($praPendaftaran->isEmpty()) {
        echo "\n✅ Tidak ada data untuk dihapus.\n";
        DB::rollBack();
        exit(0);
    }
    
    // Ambil ID pra_pendaftaran
    $praPendaftaranIds = $praPendaftaran->pluck('id')->toArray();
    
    // 2. Cari pendaftaran_sertifikasi terkait
    $pendaftaran = DB::table('pendaftaran_sertifikasi')
        ->where('email', $email)
        ->orWhereIn('pra_pendaftaran_id', $praPendaftaranIds)
        ->get();
    
    echo "   Pendaftaran Sertifikasi: " . $pendaftaran->count() . " record(s)\n";
    
    // 3. Detail data yang akan dihapus
    echo "\n📝 Detail yang akan dihapus:\n";
    foreach ($praPendaftaran as $item) {
        echo "   - Pra-Pendaftaran: {$item->nomor_pra_pendaftaran} (Status: {$item->status})\n";
    }
    foreach ($pendaftaran as $item) {
        echo "   - Pendaftaran: {$item->nomor_pendaftaran} (Status: {$item->status})\n";
    }
    
    echo "\n🗑️  Menghapus data...\n";
    
    // 4. Hapus pendaftaran_sertifikasi (cascade akan hapus relasi lain)
    $deletedPendaftaran = DB::table('pendaftaran_sertifikasi')
        ->where('email', $email)
        ->orWhereIn('pra_pendaftaran_id', $praPendaftaranIds)
        ->delete();
    echo "   ✅ Pendaftaran Sertifikasi: {$deletedPendaftaran} deleted\n";
    
    // 5. Hapus pra_pendaftaran
    $deletedPraPendaftaran = DB::table('pra_pendaftaran')
        ->where('email', $email)
        ->delete();
    echo "   ✅ Pra-Pendaftaran: {$deletedPraPendaftaran} deleted\n";
    
    // 6. Hapus audit logs (optional, untuk clean up)
    $deletedAuditLogs = DB::table('audit_logs')
        ->where('metadata->email', $email)
        ->delete();
    echo "   ✅ Audit Logs: {$deletedAuditLogs} deleted\n";
    
    // 7. Hapus notification logs (skip jika table tidak ada)
    try {
        $deletedNotifications = DB::table('notification_logs')
            ->where('recipient', 'LIKE', "%{$email}%")
            ->delete();
        echo "   ✅ Notification Logs: {$deletedNotifications} deleted\n";
    } catch (\Exception $e) {
        echo "   ⏭️  Notification Logs: skipped (table not exists)\n";
    }
    
    DB::commit();
    
    echo "\n========================================\n";
    echo "✅ DATA BERHASIL DIHAPUS\n";
    echo "========================================\n";
    echo "Email {$email} sudah bersih dan siap untuk testing ulang.\n\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

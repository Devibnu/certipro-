<?php
/**
 * Script untuk hapus user berdasarkan email di PRODUCTION
 * 
 * USAGE:
 * ssh root@76.13.18.166
 * cd /var/www/lsp-ui.ibnuapps.cloud/current
 * php delete-user-production.php
 */

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\PraPendaftaran;
use App\Models\PendaftaranSertifikasi;
use App\Models\Asesmen;
use App\Models\KeputusanSertifikasi;
use App\Models\Sertifikat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

$email = 'ibnuqosim022@gmail.com';

echo "════════════════════════════════════════════════════════════════\n";
echo "  DELETE USER DATA FROM PRODUCTION\n";
echo "  Email: {$email}\n";
echo "════════════════════════════════════════════════════════════════\n\n";

// Auto-confirm for non-interactive mode
echo "⚠️  WARNING: This will DELETE ALL DATA for {$email}\n";
echo "Auto-confirming deletion (non-interactive mode)...\n";

echo "\n🔍 Checking existing data...\n\n";

// Cek data yang ada
$user = User::where('email', $email)->first();
$praPendaftaran = PraPendaftaran::where('email', $email)->get();
$pendaftaran = PendaftaranSertifikasi::where('email', $email)->get();

echo "Found:\n";
echo "  - Users: " . ($user ? 1 : 0) . "\n";
echo "  - PraPendaftaran: " . $praPendaftaran->count() . "\n";
echo "  - PendaftaranSertifikasi: " . $pendaftaran->count() . "\n\n";

if (!$user && $praPendaftaran->isEmpty() && $pendaftaran->isEmpty()) {
    echo "✅ No data found for {$email}. Nothing to delete.\n";
    exit(0);
}

echo "🗑️  Starting deletion...\n\n";

DB::beginTransaction();

try {
    $deletedCount = [
        'sertifikat' => 0,
        'keputusan' => 0,
        'asesmen' => 0,
        'pendaftaran' => 0,
        'pra_pendaftaran' => 0,
        'user' => 0,
    ];

    // 1. Delete Sertifikat (jika ada)
    if ($pendaftaran->isNotEmpty()) {
        foreach ($pendaftaran as $p) {
            $sertifikat = Sertifikat::where('pendaftaran_id', $p->id)->get();
            foreach ($sertifikat as $s) {
                echo "   Deleting Sertifikat ID: {$s->id} ({$s->nomor_sertifikat})\n";
                $s->delete();
                $deletedCount['sertifikat']++;
            }
        }
    }

    // 2. Delete KeputusanSertifikasi (jika ada)
    if ($pendaftaran->isNotEmpty()) {
        foreach ($pendaftaran as $p) {
            if ($p->asesmen) {
                $keputusan = KeputusanSertifikasi::where('asesmen_id', $p->asesmen->id)->get();
                foreach ($keputusan as $k) {
                    echo "   Deleting KeputusanSertifikasi ID: {$k->id}\n";
                    $k->delete();
                    $deletedCount['keputusan']++;
                }
            }
        }
    }

    // 3. Delete Asesmen (jika ada)
    if ($pendaftaran->isNotEmpty()) {
        foreach ($pendaftaran as $p) {
            if ($p->asesmen) {
                echo "   Deleting Asesmen ID: {$p->asesmen->id}\n";
                $p->asesmen->delete();
                $deletedCount['asesmen']++;
            }
        }
    }

    // 4. Delete PendaftaranSertifikasi
    foreach ($pendaftaran as $p) {
        echo "   Deleting PendaftaranSertifikasi ID: {$p->id} ({$p->nomor_pendaftaran})\n";
        $p->delete();
        $deletedCount['pendaftaran']++;
    }

    // 5. Delete PraPendaftaran
    foreach ($praPendaftaran as $pra) {
        echo "   Deleting PraPendaftaran ID: {$pra->id} ({$pra->nomor_pra_pendaftaran})\n";
        $pra->delete();
        $deletedCount['pra_pendaftaran']++;
    }

    // 6. Delete User
    if ($user) {
        echo "   Deleting User ID: {$user->id} ({$user->name})\n";
        $user->delete();
        $deletedCount['user']++;
    }

    DB::commit();

    echo "\n✅ Deletion completed successfully!\n\n";
    echo "Summary:\n";
    echo "  - Sertifikat deleted: {$deletedCount['sertifikat']}\n";
    echo "  - KeputusanSertifikasi deleted: {$deletedCount['keputusan']}\n";
    echo "  - Asesmen deleted: {$deletedCount['asesmen']}\n";
    echo "  - PendaftaranSertifikasi deleted: {$deletedCount['pendaftaran']}\n";
    echo "  - PraPendaftaran deleted: {$deletedCount['pra_pendaftaran']}\n";
    echo "  - User deleted: {$deletedCount['user']}\n";

    // Log ke audit log
    Log::info('User data deleted via script', [
        'email' => $email,
        'deleted_counts' => $deletedCount,
        'executed_by' => 'CLI Script',
        'timestamp' => now(),
    ]);

    echo "\n📝 Deletion logged to storage/logs/laravel.log\n";

} catch (\Exception $e) {
    DB::rollBack();
    
    echo "\n❌ ERROR during deletion:\n";
    echo "   " . $e->getMessage() . "\n\n";
    echo "   Transaction rolled back. No data was deleted.\n";
    
    Log::error('Failed to delete user data', [
        'email' => $email,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
    
    exit(1);
}

echo "\n════════════════════════════════════════════════════════════════\n";
echo "  Done!\n";
echo "════════════════════════════════════════════════════════════════\n";

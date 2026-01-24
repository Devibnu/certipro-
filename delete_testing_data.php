<?php
/**
 * DELETE TESTING DATA - Run via Browser
 * Upload file ini ke /var/www/certipro/public/
 * Akses: https://lsp-ui.ibnuapps.cloud/delete_testing_data.php
 * 
 * PENTING: Hapus file ini setelah selesai!
 */

// Security: Simple password protection
$password = 'hapusdata123'; // Ganti password ini!

if (!isset($_POST['password']) || $_POST['password'] !== $password) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Delete Testing Data</title>
        <style>
            body { font-family: Arial; max-width: 500px; margin: 50px auto; padding: 20px; }
            input[type="password"], button { padding: 10px; font-size: 16px; width: 100%; margin: 10px 0; }
            button { background: #dc2626; color: white; border: none; cursor: pointer; }
            button:hover { background: #b91c1c; }
        </style>
    </head>
    <body>
        <h2>🗑️ Delete Testing Data</h2>
        <p>Masukkan password untuk menghapus data testing:</p>
        <form method="POST">
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Hapus Data Testing</button>
        </form>
        <hr>
        <p><small>Target Email:<br>
        - ibnuqosim022@gmail.com<br>
        - asroasrobantani@gmail.com</small></p>
    </body>
    </html>
    <?php
    exit;
}

// Bootstrap Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Deleting Data...</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .log { background: #1e293b; color: #10b981; padding: 20px; border-radius: 8px; font-family: monospace; white-space: pre-wrap; }
        .success { color: #10b981; font-weight: bold; }
        .error { color: #ef4444; font-weight: bold; }
        .warning { color: #f59e0b; }
    </style>
</head>
<body>
    <h2>🗑️ Menghapus Data Testing</h2>
    <div class="log">
<?php

try {
    DB::transaction(function() {
        $emails = ['ibnuqosim022@gmail.com', 'asroasrobantani@gmail.com'];
        
        echo "=== MENGHAPUS DATA TESTING ===\n\n";
        echo "Target Emails:\n";
        foreach ($emails as $email) {
            echo "  - $email\n";
        }
        echo "\n";
        
        // 1. Cari pra_pendaftaran
        echo "📋 Mencari Pra-Pendaftaran...\n";
        $praPendaftaran = DB::table('pra_pendaftaran')->whereIn('email', $emails)->get();
        echo "   Ditemukan: " . $praPendaftaran->count() . " records\n";
        foreach ($praPendaftaran as $p) {
            echo "   - ID $p->id: $p->nama_lengkap ($p->email)\n";
        }
        echo "\n";
        
        // 2. Cari pendaftaran_sertifikasi
        echo "📋 Mencari Pendaftaran Sertifikasi...\n";
        $pendaftaran = DB::table('pendaftaran_sertifikasi')->whereIn('email', $emails)->get();
        echo "   Ditemukan: " . $pendaftaran->count() . " records\n";
        foreach ($pendaftaran as $p) {
            echo "   - ID $p->id: $p->nomor_pendaftaran - $p->nama_lengkap ($p->email)\n";
        }
        echo "\n";
        
        // 3. Cari related data
        $pendaftaranIds = $pendaftaran->pluck('id')->toArray();
        
        if (!empty($pendaftaranIds)) {
            echo "📋 Mencari Related Data...\n";
            $countAsesmen = DB::table('asesmen')->whereIn('pendaftaran_id', $pendaftaranIds)->count();
            $countKeputusan = DB::table('keputusan_sertifikasi')->whereIn('pendaftaran_id', $pendaftaranIds)->count();
            $countSertifikat = DB::table('sertifikat')->whereIn('pendaftaran_id', $pendaftaranIds)->count();
            
            echo "   - Asesmen: $countAsesmen\n";
            echo "   - Keputusan: $countKeputusan\n";
            echo "   - Sertifikat: $countSertifikat\n";
            echo "\n";
        }
        
        echo "--- MENGHAPUS DATA ---\n\n";
        
        // Hapus child data dulu
        if (!empty($pendaftaranIds)) {
            echo "🗑️  Menghapus Sertifikat...\n";
            $deletedSertifikat = DB::table('sertifikat')->whereIn('pendaftaran_id', $pendaftaranIds)->delete();
            echo "   ✓ Dihapus: $deletedSertifikat records\n\n";
            
            echo "🗑️  Menghapus Keputusan Sertifikasi...\n";
            $deletedKeputusan = DB::table('keputusan_sertifikasi')->whereIn('pendaftaran_id', $pendaftaranIds)->delete();
            echo "   ✓ Dihapus: $deletedKeputusan records\n\n";
            
            echo "🗑️  Menghapus Asesmen...\n";
            $deletedAsesmen = DB::table('asesmen')->whereIn('pendaftaran_id', $pendaftaranIds)->delete();
            echo "   ✓ Dihapus: $deletedAsesmen records\n\n";
        }
        
        echo "🗑️  Menghapus Pendaftaran Sertifikasi...\n";
        $deletedPendaftaran = DB::table('pendaftaran_sertifikasi')->whereIn('email', $emails)->delete();
        echo "   ✓ Dihapus: $deletedPendaftaran records\n\n";
        
        echo "🗑️  Menghapus Pra-Pendaftaran...\n";
        $deletedPra = DB::table('pra_pendaftaran')->whereIn('email', $emails)->delete();
        echo "   ✓ Dihapus: $deletedPra records\n\n";
        
        echo "✅ SELESAI!\n";
        echo "Semua data testing berhasil dihapus.\n";
    });
    
} catch (\Exception $e) {
    echo "\n❌ ERROR!\n";
    echo $e->getMessage() . "\n";
    echo "\nStack Trace:\n";
    echo $e->getTraceAsString();
}

?>
    </div>
    <hr>
    <p><strong>⚠️ PENTING:</strong> Hapus file <code>delete_testing_data.php</code> dari server setelah selesai!</p>
    <p>
        <a href="/adminui/pra-pendaftaran" style="display:inline-block; padding:10px 20px; background:#10b981; color:white; text-decoration:none; border-radius:5px;">
            Ke Pra-Pendaftaran
        </a>
        <a href="/adminui/pendaftaran-sertifikasi" style="display:inline-block; padding:10px 20px; background:#3b82f6; color:white; text-decoration:none; border-radius:5px; margin-left:10px;">
            Ke Pendaftaran Sertifikasi
        </a>
    </p>
</body>
</html>

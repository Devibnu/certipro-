<?php
// Simpan sebagai: /var/www/lsp-ui.ibnuapps.cloud/current/hapus_data.php
// Akses: php hapus_data.php
// HAPUS FILE INI SETELAH SELESAI!

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$emails = ['ibnuqosim022@gmail.com', 'asroasrobantani@gmail.com'];

echo "\n=== MENGHAPUS DATA TESTING ===\n\n";
echo "Email: " . implode(', ', $emails) . "\n\n";

DB::transaction(function() use ($emails) {
    // Cari data
    $pra = DB::table('pra_pendaftaran')->whereIn('email', $emails)->get();
    $pend = DB::table('pendaftaran_sertifikasi')->whereIn('email', $emails)->get();
    
    echo "Ditemukan:\n";
    echo "  Pra-Pendaftaran: " . $pra->count() . "\n";
    echo "  Pendaftaran: " . $pend->count() . "\n\n";
    
    $ids = $pend->pluck('id')->toArray();
    
    // Hapus child data
    if (!empty($ids)) {
        $s = DB::table('sertifikat')->whereIn('pendaftaran_id', $ids)->delete();
        $k = DB::table('keputusan_sertifikasi')->whereIn('pendaftaran_id', $ids)->delete();
        $a = DB::table('asesmen')->whereIn('pendaftaran_id', $ids)->delete();
        
        echo "Dihapus:\n";
        echo "  Sertifikat: $s\n";
        echo "  Keputusan: $k\n";
        echo "  Asesmen: $a\n";
    }
    
    // Hapus parent data
    $d1 = DB::table('pendaftaran_sertifikasi')->whereIn('email', $emails)->delete();
    $d2 = DB::table('pra_pendaftaran')->whereIn('email', $emails)->delete();
    
    echo "  Pendaftaran: $d1\n";
    echo "  Pra-Pendaftaran: $d2\n";
});

echo "\n✅ SELESAI! Data berhasil dihapus.\n\n";

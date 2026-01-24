<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$email = 'ibnuqosim022@gmail.com';

echo "\n=== MENGHAPUS DATA: $email ===\n\n";

DB::transaction(function() use ($email) {
    $pra = DB::table('pra_pendaftaran')->where('email', $email)->get();
    $pend = DB::table('pendaftaran_sertifikasi')->where('email', $email)->get();
    
    echo "Ditemukan:\n";
    echo "  Pra-Pendaftaran: " . $pra->count() . "\n";
    foreach ($pra as $p) {
        echo "    - ID $p->id: $p->nama_lengkap\n";
    }
    
    echo "  Pendaftaran: " . $pend->count() . "\n";
    foreach ($pend as $p) {
        echo "    - ID $p->id: $p->nomor_pendaftaran\n";
    }
    
    $ids = $pend->pluck('id')->toArray();
    
    if (!empty($ids)) {
        $s = DB::table('sertifikat')->whereIn('pendaftaran_id', $ids)->delete();
        $k = DB::table('keputusan_sertifikasi')->whereIn('pendaftaran_id', $ids)->delete();
        $a = DB::table('asesmen')->whereIn('pendaftaran_id', $ids)->delete();
        echo "\nDihapus: Sertifikat=$s, Keputusan=$k, Asesmen=$a\n";
    }
    
    $d1 = DB::table('pendaftaran_sertifikasi')->where('email', $email)->delete();
    $d2 = DB::table('pra_pendaftaran')->where('email', $email)->delete();
    
    echo "Dihapus: Pendaftaran=$d1, Pra=$d2\n";
});

echo "\n✅ Selesai!\n\n";

#!/bin/bash
# Delete testing data from production server

ssh root@76.13.18.166 << 'ENDSSH'
cd /var/www/lsp-ui.ibnuapps.cloud/current

php artisan tinker << 'ENDTINKER'
$emails = ['ibnuqosim022@gmail.com', 'asroasrobantani@gmail.com'];

echo "\n=== Menghapus Data Testing ===\n\n";

$pra = DB::table('pra_pendaftaran')->whereIn('email', $emails)->get();
$pend = DB::table('pendaftaran_sertifikasi')->whereIn('email', $emails)->get();

echo "Pra-Pendaftaran: " . $pra->count() . "\n";
foreach ($pra as $p) {
    echo "  - $p->id: $p->nama_lengkap\n";
}

echo "\nPendaftaran: " . $pend->count() . "\n";
foreach ($pend as $p) {
    echo "  - $p->id: $p->nomor_pendaftaran - $p->nama_lengkap\n";
}

$ids = $pend->pluck('id')->toArray();

echo "\n--- Menghapus ---\n";

if (count($ids) > 0) {
    $s = DB::table('sertifikat')->whereIn('pendaftaran_id', $ids)->delete();
    $k = DB::table('keputusan_sertifikasi')->whereIn('pendaftaran_id', $ids)->delete();
    $a = DB::table('asesmen')->whereIn('pendaftaran_id', $ids)->delete();
    echo "Sertifikat: $s\n";
    echo "Keputusan: $k\n";
    echo "Asesmen: $a\n";
}

$delPend = DB::table('pendaftaran_sertifikasi')->whereIn('email', $emails)->delete();
$delPra = DB::table('pra_pendaftaran')->whereIn('email', $emails)->delete();

echo "Pendaftaran: $delPend\n";
echo "Pra-Pendaftaran: $delPra\n";

echo "\n✅ Selesai!\n";
exit
ENDTINKER
ENDSSH

#!/bin/bash

# SCRIPT UNTUK HAPUS DATA TESTING DI PRODUCTION
# Run di server: lsp-ui.ibnuapps.cloud

echo "=== HAPUS DATA TESTING - PRODUCTION ==="
echo ""
echo "Target Emails:"
echo "  - ibnuqosim022@gmail.com"
echo "  - asroasrobantani@gmail.com"
echo ""
read -p "Lanjutkan? (yes/no): " confirm

if [ "$confirm" != "yes" ]; then
    echo "Dibatalkan."
    exit 1
fi

# Path ke aplikasi di production
cd /var/www/certipro

# Jalankan script hapus
php artisan tinker << 'TINKER'
DB::transaction(function() {
    $emails = ['ibnuqosim022@gmail.com', 'asroasrobantani@gmail.com'];
    
    echo "\n=== MENGHAPUS DATA TESTING ===\n\n";
    
    // 1. Cari semua pra_pendaftaran
    $praPendaftaran = DB::table('pra_pendaftaran')->whereIn('email', $emails)->get();
    echo "Pra-Pendaftaran ditemukan: " . $praPendaftaran->count() . "\n";
    foreach ($praPendaftaran as $p) {
        echo "  - ID $p->id: $p->nama_lengkap ($p->email)\n";
    }
    
    // 2. Cari semua pendaftaran_sertifikasi
    $pendaftaran = DB::table('pendaftaran_sertifikasi')->whereIn('email', $emails)->get();
    echo "\nPendaftaran Sertifikasi ditemukan: " . $pendaftaran->count() . "\n";
    foreach ($pendaftaran as $p) {
        echo "  - ID $p->id: $p->nomor_pendaftaran - $p->nama_lengkap ($p->email)\n";
    }
    
    // 3. Cari related data
    $pendaftaranIds = $pendaftaran->pluck('id')->toArray();
    
    if (!empty($pendaftaranIds)) {
        $asesmen = DB::table('asesmen')->whereIn('pendaftaran_id', $pendaftaranIds)->count();
        $keputusan = DB::table('keputusan_sertifikasi')->whereIn('pendaftaran_id', $pendaftaranIds)->count();
        $sertifikat = DB::table('sertifikat')->whereIn('pendaftaran_id', $pendaftaranIds)->count();
        
        echo "\nRelated Data:\n";
        echo "  - Asesmen: $asesmen\n";
        echo "  - Keputusan: $keputusan\n";
        echo "  - Sertifikat: $sertifikat\n";
    }
    
    echo "\n--- MENGHAPUS DATA ---\n\n";
    
    // Hapus dalam urutan yang benar (child first)
    if (!empty($pendaftaranIds)) {
        $deletedSertifikat = DB::table('sertifikat')->whereIn('pendaftaran_id', $pendaftaranIds)->delete();
        echo "Sertifikat dihapus: $deletedSertifikat\n";
        
        $deletedKeputusan = DB::table('keputusan_sertifikasi')->whereIn('pendaftaran_id', $pendaftaranIds)->delete();
        echo "Keputusan dihapus: $deletedKeputusan\n";
        
        $deletedAsesmen = DB::table('asesmen')->whereIn('pendaftaran_id', $pendaftaranIds)->delete();
        echo "Asesmen dihapus: $deletedAsesmen\n";
    }
    
    $deletedPendaftaran = DB::table('pendaftaran_sertifikasi')->whereIn('email', $emails)->delete();
    echo "Pendaftaran dihapus: $deletedPendaftaran\n";
    
    $deletedPra = DB::table('pra_pendaftaran')->whereIn('email', $emails)->delete();
    echo "Pra-Pendaftaran dihapus: $deletedPra\n";
    
    echo "\n✅ SEMUA DATA BERHASIL DIHAPUS!\n";
    echo "Siap untuk testing ulang.\n\n";
});
exit
TINKER

echo ""
echo "Done!"

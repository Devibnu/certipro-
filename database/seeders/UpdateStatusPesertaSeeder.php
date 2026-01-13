<?php

namespace Database\Seeders;

use App\Models\PendaftaranSertifikasi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateStatusPesertaSeeder extends Seeder
{
    /**
     * Update status_peserta for existing pendaftaran records.
     * 
     * Mapping:
     * - draft, diajukan → DALAM_PROSES
     * - diverifikasi → DITERIMA
     * - ditolak → DITOLAK
     * - siap_asesmen → DIJADWALKAN_ASESMEN
     * - belum_kompeten → SEDANG_ASESMEN
     * - menunggu_keputusan → MENUNGGU_KEPUTUSAN
     * - kompeten_final → LULUS_SERTIFIKASI
     * - belum_kompeten_final → TIDAK_LULUS
     */
    public function run(): void
    {
        $this->command->info('Updating status_peserta for existing pendaftaran records...');
        
        $pendaftaranList = PendaftaranSertifikasi::all();
        $updated = 0;
        
        foreach ($pendaftaranList as $pendaftaran) {
            $statusPeserta = PendaftaranSertifikasi::mapToStatusPeserta($pendaftaran->status);
            $pesan = PendaftaranSertifikasi::getStatusPesertaMessage($statusPeserta);
            
            $pendaftaran->update([
                'status_peserta' => $statusPeserta,
                'pesan_status_peserta' => $pesan,
                'status_peserta_updated_at' => $pendaftaran->updated_at,
            ]);
            
            $updated++;
        }
        
        $this->command->info("Updated {$updated} pendaftaran records with status_peserta.");
    }
}

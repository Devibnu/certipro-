<?php

namespace Database\Seeders;

use App\Http\Controllers\PraPendaftaranController;
use App\Models\PraPendaftaran;
use Illuminate\Database\Seeder;

class UpdateNomorPraPendaftaranSeeder extends Seeder
{
    /**
     * Update semua pra pendaftaran yang belum punya nomor.
     */
    public function run(): void
    {
        $praPendaftarans = PraPendaftaran::whereNull('nomor_pra_pendaftaran')
            ->orWhere('nomor_pra_pendaftaran', '')
            ->get();

        $count = 0;
        foreach ($praPendaftarans as $praPendaftaran) {
            $praPendaftaran->update([
                'nomor_pra_pendaftaran' => PraPendaftaranController::generateNomorPraPendaftaran()
            ]);
            $count++;
        }

        $this->command->info("Updated {$count} pra-pendaftaran records with nomor_pra_pendaftaran.");
    }
}

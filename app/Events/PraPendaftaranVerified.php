<?php

namespace App\Events;

use App\Models\PraPendaftaran;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PraPendaftaranVerified
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public PraPendaftaran $praPendaftaran,
        public string $newStatus // 'diterima' or 'ditolak'
    ) {}

    /**
     * Get event identifier untuk notification log
     */
    public function getEventType(): string
    {
        return $this->newStatus === 'diterima' 
            ? 'pra_pendaftaran_diterima' 
            : 'pra_pendaftaran_ditolak';
    }

    /**
     * Get notification data
     */
    public function getNotificationData(): array
    {
        return [
            'nomor_pra_pendaftaran' => $this->praPendaftaran->nomor_pra_pendaftaran,
            'nama' => $this->praPendaftaran->nama_lengkap,
            'email' => $this->praPendaftaran->email,
            'no_hp' => $this->praPendaftaran->no_hp,
            'status' => $this->newStatus,
            'tanggal_verifikasi' => $this->praPendaftaran->status_updated_at?->format('d F Y, H:i'),
            'alasan_penolakan' => $this->praPendaftaran->alasan_penolakan,
        ];
    }
}

<?php

namespace App\Events;

use App\Models\KeputusanSertifikasi;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KeputusanDitetapkan
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public KeputusanSertifikasi $keputusan
    ) {}

    public function getEventType(): string
    {
        return $this->keputusan->keputusan === 'kompeten'
            ? 'keputusan_kompeten'
            : 'keputusan_belum_kompeten';
    }

    public function getNotificationData(): array
    {
        $pendaftaran = $this->keputusan->pendaftaranSertifikasi;
        
        return [
            'nomor_pendaftaran' => $pendaftaran->nomor_pendaftaran,
            'nama' => $pendaftaran->nama_lengkap,
            'email' => $pendaftaran->email,
            'no_hp' => $pendaftaran->no_hp,
            'keputusan' => $this->keputusan->keputusan,
            'skema' => $pendaftaran->skemaKlasifikasi?->nama_skema,
            'tanggal_keputusan' => $this->keputusan->decided_at?->format('d F Y'),
            'catatan' => $this->keputusan->catatan,
        ];
    }
}

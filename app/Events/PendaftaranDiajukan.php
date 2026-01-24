<?php

namespace App\Events;

use App\Models\PendaftaranSertifikasi;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PendaftaranDiajukan
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public PendaftaranSertifikasi $pendaftaran
    ) {}

    public function getEventType(): string
    {
        return 'pendaftaran_diajukan';
    }

    public function getNotificationData(): array
    {
        return [
            'nomor_pendaftaran' => $this->pendaftaran->nomor_pendaftaran,
            'nama' => $this->pendaftaran->nama_lengkap,
            'email' => $this->pendaftaran->email,
            'no_hp' => $this->pendaftaran->no_hp,
            'skema' => $this->pendaftaran->skemaKlasifikasi?->nama_skema,
            'tanggal_daftar' => $this->pendaftaran->tanggal_daftar?->format('d F Y'),
        ];
    }
}

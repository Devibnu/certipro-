<?php

namespace App\Events;

use App\Models\Sertifikat;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SertifikatDiterbitkan
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Sertifikat $sertifikat
    ) {}

    public function getEventType(): string
    {
        return 'sertifikat_diterbitkan';
    }

    public function getNotificationData(): array
    {
        $pendaftaran = $this->sertifikat->pendaftaranSertifikasi;
        
        return [
            'nomor_sertifikat' => $this->sertifikat->nomor_sertifikat,
            'nama' => $pendaftaran->nama_lengkap,
            'email' => $pendaftaran->email,
            'no_hp' => $pendaftaran->no_hp,
            'skema' => $pendaftaran->skemaKlasifikasi?->nama_skema,
            'tanggal_terbit' => $this->sertifikat->tanggal_terbit?->format('d F Y'),
            'berlaku_sampai' => $this->sertifikat->tanggal_berlaku_sampai?->format('d F Y'),
            'download_url' => route('sertifikat.download', $this->sertifikat->id),
            'verify_url' => route('sertifikat.verify', $this->sertifikat->nomor_sertifikat),
        ];
    }
}

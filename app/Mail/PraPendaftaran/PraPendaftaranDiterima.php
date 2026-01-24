<?php

namespace App\Mail\PraPendaftaran;

use App\Http\Controllers\ResumePendaftaranController;
use App\Models\PraPendaftaran;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PraPendaftaranDiterima extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public PraPendaftaran $praPendaftaran
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address', 'noreply@certipro.id'),
                config('mail.from.name', 'CertiPro LSP')
            ),
            subject: 'Pra-Pendaftaran Diterima - ' . $this->praPendaftaran->nomor_pra_pendaftaran,
        );
    }

    /**
     * Get the message content definition.
     * 
     * SMART LINK LOGIC (IDEMPOTENT):
     * - Jika pendaftaran SUDAH ADA → Link ke detail pendaftaran (signed URL)
     * - Jika pendaftaran BELUM ADA → Link ke "lanjut pendaftaran" (signed URL, akan create 1x)
     * 
     * SECURITY:
     * - Menggunakan signed URL (30 hari expiry)
     * - Tidak bisa ditebak atau dipakai untuk user lain
     * 
     * IDEMPOTENCY:
     * - Link bisa diklik berkali-kali tanpa membuat duplikat
     * - Aman dari double click, refresh, retry
     */
    public function content(): Content
    {
        // Cek apakah pendaftaran sudah dibuat
        $pendaftaran = $this->praPendaftaran->pendaftaranSertifikasi;
        
        // SMART LINK: Context-aware URL generation
        if ($pendaftaran) {
            // CASE 1: Pendaftaran SUDAH ADA → Arahkan ke detail (signed URL)
            $daftarUrl = ResumePendaftaranController::generateDetailSignedUrl($pendaftaran);
            $hasPendaftaran = true;
        } else {
            // CASE 2: Pendaftaran BELUM ADA → Arahkan ke "lanjut" (akan create 1x dengan lock)
            $daftarUrl = ResumePendaftaranController::generateSignedUrl($this->praPendaftaran);
            $hasPendaftaran = false;
        }

        // URL untuk cek status (public)
        $statusUrl = route('status-pra-pendaftaran.index') . '?nomor=' . $this->praPendaftaran->nomor_pra_pendaftaran;

        return new Content(
            view: 'emails.pra-pendaftaran.diterima',
            with: [
                'praPendaftaran' => $this->praPendaftaran,
                'daftarUrl' => $daftarUrl,
                'statusUrl' => $statusUrl,
                'hasPendaftaran' => $hasPendaftaran,
                'pendaftaran' => $pendaftaran,
                'systemName' => config('app.name', 'CertiPro LSP'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * ============================================================================
 * Mail: PraPendaftaranDitolakMail
 * ============================================================================
 * Email sent when pra-pendaftaran status is DITOLAK
 * Template: emails.pra-ditolak
 * 
 * Compliance: BNSP, ISO 17024
 * ============================================================================
 */
class PraPendaftaranDitolakMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Peserta name
     */
    public string $nama;

    /**
     * Nomor pra-pendaftaran
     */
    public string $nomor_pra_pendaftaran;

    /**
     * Alasan penolakan (wajib ada)
     */
    public string $alasan_penolakan;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $nama,
        string $nomor_pra_pendaftaran,
        string $alasan_penolakan
    ) {
        $this->nama = $nama;
        $this->nomor_pra_pendaftaran = $nomor_pra_pendaftaran;
        $this->alasan_penolakan = $alasan_penolakan;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[LSP] Pra-Pendaftaran Ditolak – {$this->nomor_pra_pendaftaran}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.pra-ditolak',
            with: [
                'nama' => $this->nama,
                'nomor_pra_pendaftaran' => $this->nomor_pra_pendaftaran,
                'alasan_penolakan' => $this->alasan_penolakan,
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

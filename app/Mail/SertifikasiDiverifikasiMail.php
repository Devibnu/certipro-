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
 * Mail: SertifikasiDiverifikasiMail
 * ============================================================================
 * Email sent when pendaftaran sertifikasi is verified (DIVERIFIKASI)
 * Template: emails.sertifikasi-diverifikasi
 * 
 * Compliance: BNSP, ISO 17024
 * ============================================================================
 */
class SertifikasiDiverifikasiMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Peserta name
     */
    public string $nama;

    /**
     * Nomor pendaftaran
     */
    public string $nomor_pendaftaran;

    /**
     * Skema sertifikasi
     */
    public string $skema;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $nama,
        string $nomor_pendaftaran,
        string $skema
    ) {
        $this->nama = $nama;
        $this->nomor_pendaftaran = $nomor_pendaftaran;
        $this->skema = $skema;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[LSP] Pendaftaran Sertifikasi Diverifikasi",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.sertifikasi-diverifikasi',
            with: [
                'nama' => $this->nama,
                'nomor_pendaftaran' => $this->nomor_pendaftaran,
                'skema' => $this->skema,
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

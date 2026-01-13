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
 * Mail: PraPendaftaranDiterimaMail
 * ============================================================================
 * Email sent when pra-pendaftaran status is DITERIMA
 * Template: emails.pra-diterima
 * 
 * Compliance: BNSP, ISO 17024
 * ============================================================================
 */
class PraPendaftaranDiterimaMail extends Mailable implements ShouldQueue
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
     * Tanggal pendaftaran
     */
    public string $tanggal;

    /**
     * Link to check status
     */
    public string $link_status;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $nama,
        string $nomor_pra_pendaftaran,
        string $tanggal,
        string $link_status
    ) {
        $this->nama = $nama;
        $this->nomor_pra_pendaftaran = $nomor_pra_pendaftaran;
        $this->tanggal = $tanggal;
        $this->link_status = $link_status;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[LSP] Pra-Pendaftaran Diterima – {$this->nomor_pra_pendaftaran}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.pra-diterima',
            with: [
                'nama' => $this->nama,
                'nomor_pra_pendaftaran' => $this->nomor_pra_pendaftaran,
                'tanggal' => $this->tanggal,
                'link_status' => $this->link_status,
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

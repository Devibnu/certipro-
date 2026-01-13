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
 * Mail: BelumKompetenMail
 * ============================================================================
 * Email sent when keputusan sertifikasi is BELUM KOMPETEN (final)
 * Template: emails.belum-kompeten
 * 
 * IMPORTANT: This email should only be sent ONCE per sertifikasi
 * 
 * Compliance: BNSP, ISO 17024
 * ============================================================================
 */
class BelumKompetenMail extends Mailable implements ShouldQueue
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
     * Create a new message instance.
     */
    public function __construct(
        string $nama,
        string $nomor_pendaftaran
    ) {
        $this->nama = $nama;
        $this->nomor_pendaftaran = $nomor_pendaftaran;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[LSP] Hasil Sertifikasi: BELUM KOMPETEN",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.belum-kompeten',
            with: [
                'nama' => $this->nama,
                'nomor_pendaftaran' => $this->nomor_pendaftaran,
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

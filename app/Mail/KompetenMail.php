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
 * Mail: KompetenMail
 * ============================================================================
 * Email sent when keputusan sertifikasi is KOMPETEN (final)
 * Template: emails.kompeten
 * 
 * IMPORTANT: This email should only be sent ONCE per sertifikasi
 * 
 * Compliance: BNSP, ISO 17024
 * ============================================================================
 */
class KompetenMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Peserta name
     */
    public string $nama;

    /**
     * Nomor sertifikat
     */
    public string $nomor_sertifikat;

    /**
     * Masa berlaku sertifikat
     */
    public string $masa_berlaku;

    /**
     * Link to download sertifikat
     */
    public string $link_sertifikat;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $nama,
        string $nomor_sertifikat,
        string $masa_berlaku,
        string $link_sertifikat
    ) {
        $this->nama = $nama;
        $this->nomor_sertifikat = $nomor_sertifikat;
        $this->masa_berlaku = $masa_berlaku;
        $this->link_sertifikat = $link_sertifikat;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[LSP] Hasil Sertifikasi: KOMPETEN",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.kompeten',
            with: [
                'nama' => $this->nama,
                'nomor_sertifikat' => $this->nomor_sertifikat,
                'masa_berlaku' => $this->masa_berlaku,
                'link_sertifikat' => $this->link_sertifikat,
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

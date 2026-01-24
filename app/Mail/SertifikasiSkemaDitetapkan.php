<?php

namespace App\Mail;

use App\Models\PendaftaranSertifikasi;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SertifikasiSkemaDitetapkan extends Mailable
{
    use Queueable, SerializesModels;

    public $pendaftaran;

    /**
     * Create a new message instance.
     */
    public function __construct(PendaftaranSertifikasi $pendaftaran)
    {
        $this->pendaftaran = $pendaftaran;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Skema Sertifikasi Ditetapkan – Siap Asesmen',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.sertifikasi.skema-ditetapkan',
            with: [
                'pendaftaran' => $this->pendaftaran,
                'skema' => $this->pendaftaran->skemaSertifikasi,
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

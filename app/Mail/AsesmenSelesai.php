<?php

namespace App\Mail;

use App\Models\Asesmen;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AsesmenSelesai extends Mailable
{
    use Queueable, SerializesModels;

    public $asesmen;

    /**
     * Create a new message instance.
     */
    public function __construct(Asesmen $asesmen)
    {
        $this->asesmen = $asesmen;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Asesmen Telah Selesai – Menunggu Keputusan',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.sertifikasi.asesmen-selesai',
            with: [
                'asesmen' => $this->asesmen,
                'pendaftaran' => $this->asesmen->pendaftaran,
                'skema' => $this->asesmen->pendaftaran->skemaSertifikasi,
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

<?php

namespace App\Mail\PraPendaftaran;

use App\Models\PraPendaftaran;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PraPendaftaranDibuat extends Mailable
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
            subject: 'Konfirmasi Pra-Pendaftaran - ' . $this->praPendaftaran->nomor_pra_pendaftaran,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.pra-pendaftaran.dibuat',
            with: [
                'praPendaftaran' => $this->praPendaftaran,
                'statusUrl' => route('status-pra-pendaftaran.search', ['search' => $this->praPendaftaran->nomor_pra_pendaftaran]),
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

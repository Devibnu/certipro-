<?php

namespace App\Mail;

use App\Models\PendaftaranSertifikasi;
use App\Models\Sertifikat;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SertifikatTerbitMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Sertifikat $sertifikat,
        public PendaftaranSertifikasi $pendaftaran,
        public ?string $pdfPath = null
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $lspName = config('certipro.lsp.nama', 'LSP CertiPro');
        $lspEmail = config('certipro.lsp.email', 'admin@lsp-certipro.id');

        return new Envelope(
            from: new Address($lspEmail, $lspName),
            subject: "Sertifikat Kompetensi Terbit - {$this->sertifikat->nomor_sertifikat}",
            replyTo: [
                new Address($lspEmail, $lspName),
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.sertifikat-terbit',
            with: [
                'sertifikat' => $this->sertifikat,
                'pendaftaran' => $this->pendaftaran,
                'asesi' => $this->pendaftaran->user,
                'skema' => $this->pendaftaran->skemaSertifikasi,
                'verificationUrl' => route('public.sertifikat.verify', $this->sertifikat->uuid),
                'lspName' => config('certipro.lsp.nama', 'LSP CertiPro'),
                'lspAlamat' => config('certipro.lsp.alamat'),
                'lspTelepon' => config('certipro.lsp.telepon'),
                'lspEmail' => config('certipro.lsp.email'),
                'lspWebsite' => config('certipro.lsp.website'),
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        // Attach PDF if exists
        if ($this->pdfPath && file_exists($this->pdfPath)) {
            $attachments[] = Attachment::fromPath($this->pdfPath)
                ->as('Sertifikat_' . $this->sertifikat->nomor_sertifikat . '.pdf')
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}

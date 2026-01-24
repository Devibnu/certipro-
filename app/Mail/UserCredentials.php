<?php

namespace App\Mail;

use App\Models\User;
use App\Models\PendaftaranSertifikasi;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserCredentials extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $pendaftaran;
    public $temporaryPassword;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, PendaftaranSertifikasi $pendaftaran, string $temporaryPassword)
    {
        $this->user = $user;
        $this->pendaftaran = $pendaftaran;
        $this->temporaryPassword = $temporaryPassword;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Akun Anda di ' . config('app.name', 'CertiPro LSP'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $loginUrl = route('login');

        return new Content(
            view: 'emails.user-credentials',
            with: [
                'user' => $this->user,
                'pendaftaran' => $this->pendaftaran,
                'temporaryPassword' => $this->temporaryPassword,
                'loginUrl' => $loginUrl,
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

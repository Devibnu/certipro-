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
 * Mail: PasswordResetSelfServiceMail
 * ============================================================================
 * Email sent when user requests password reset (forgot password)
 * Template: emails.password-reset-self-service
 * 
 * Compliance: ISO 27001, ISO 17024
 * Security: Token-based reset, expires in 60 minutes
 * ============================================================================
 */
class PasswordResetSelfServiceMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * User name
     */
    public string $nama;

    /**
     * Reset password link
     */
    public string $resetLink;

    /**
     * Expiry time in minutes
     */
    public int $expiresInMinutes;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $nama,
        string $resetLink,
        int $expiresInMinutes = 60
    ) {
        $this->nama = $nama;
        $this->resetLink = $resetLink;
        $this->expiresInMinutes = $expiresInMinutes;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Password Akun - ' . (systemCompanyName() ?? 'LSP'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset-self-service',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}

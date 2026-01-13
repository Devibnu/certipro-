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
 * Mail: PasswordResetByAdminMail
 * ============================================================================
 * Email sent when admin resets a user's password
 * Template: emails.password-reset-by-admin
 * 
 * Compliance: ISO 27001, ISO 17024
 * Security: Password link expires in 60 minutes, one-time use
 * ============================================================================
 */
class PasswordResetByAdminMail extends Mailable implements ShouldQueue
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
     * Admin name who initiated reset
     */
    public string $adminName;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $nama,
        string $resetLink,
        int $expiresInMinutes = 60,
        string $adminName = 'Administrator'
    ) {
        $this->nama = $nama;
        $this->resetLink = $resetLink;
        $this->expiresInMinutes = $expiresInMinutes;
        $this->adminName = $adminName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Password - ' . (systemCompanyName() ?? 'LSP'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset-by-admin',
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

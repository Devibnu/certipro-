<?php

namespace App\Listeners;

use App\Events\PendaftaranSertifikasiStatusChanged;
use App\Mail\SertifikasiDiverifikasiMail;
use App\Models\AuditLog;
use App\Models\EmailSetting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * ============================================================================
 * Listener: SendEmailOnPendaftaranStatus
 * ============================================================================
 * Handles email sending when pendaftaran sertifikasi status changes
 * Triggers: diverifikasi, siap_asesmen
 * 
 * Compliance: BNSP, ISO 17024
 * ============================================================================
 */
class SendEmailOnPendaftaranStatus implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the queued listener may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the listener.
     */
    public int $backoff = 60;

    /**
     * Handle the event.
     */
    public function handle(PendaftaranSertifikasiStatusChanged $event): void
    {
        // Guard: Skip if status hasn't changed
        if (!$event->hasStatusChanged()) {
            Log::info('SendEmailOnPendaftaranStatus: Status unchanged, skipping email', [
                'reference' => $event->getReferenceNumber(),
            ]);
            return;
        }

        // Guard: Check if email settings are configured
        if (!$this->isEmailConfigured()) {
            Log::warning('SendEmailOnPendaftaranStatus: Email not configured, skipping', [
                'reference' => $event->getReferenceNumber(),
            ]);
            return;
        }

        // Load required relationships
        $event->pendaftaran->load(['peserta', 'praPendaftaran', 'skemaSertifikasi']);

        // Guard: Check if peserta has email
        $pesertaEmail = $event->getPesertaEmail();
        if (empty($pesertaEmail)) {
            Log::warning('SendEmailOnPendaftaranStatus: No peserta email found', [
                'reference' => $event->getReferenceNumber(),
            ]);
            $this->logEmailFailed($event, null, 'No peserta email found');
            return;
        }

        // Determine which email to send based on status
        $normalizedStatus = strtolower(trim($event->newStatus));
        
        try {
            match ($normalizedStatus) {
                'diverifikasi', 'verified', 'siap_asesmen', 'ready_assessment' => $this->sendDiverifikasiMail($event, $pesertaEmail),
                default => Log::info('SendEmailOnPendaftaranStatus: No email template for status', [
                    'status' => $normalizedStatus,
                    'reference' => $event->getReferenceNumber(),
                ]),
            };
        } catch (\Exception $e) {
            Log::error('SendEmailOnPendaftaranStatus: Failed to send email', [
                'reference' => $event->getReferenceNumber(),
                'error' => $e->getMessage(),
            ]);
            $this->logEmailFailed($event, $pesertaEmail, $e->getMessage());
            throw $e; // Re-throw for queue retry
        }
    }

    /**
     * Send email for DIVERIFIKASI status
     */
    protected function sendDiverifikasiMail(PendaftaranSertifikasiStatusChanged $event, string $email): void
    {
        $mail = new SertifikasiDiverifikasiMail(
            nama: $event->getPesertaName(),
            nomor_pendaftaran: $event->getReferenceNumber(),
            skema: $event->getSkemaName(),
        );

        Mail::to($email)->send($mail);

        $this->logEmailSent($event, $email, 'sertifikasi-diverifikasi');
        
        Log::info('SendEmailOnPendaftaranStatus: Email sent successfully', [
            'template' => 'sertifikasi-diverifikasi',
            'to' => $email,
            'reference' => $event->getReferenceNumber(),
        ]);
    }

    /**
     * Check if email is properly configured
     */
    protected function isEmailConfigured(): bool
    {
        try {
            $emailSetting = EmailSetting::getActive();
            return $emailSetting && $emailSetting->isConfigured();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Log successful email send to audit log
     */
    protected function logEmailSent(PendaftaranSertifikasiStatusChanged $event, string $email, string $template): void
    {
        try {
            AuditLog::create([
                'user_id' => $event->triggeredBy,
                'event' => 'email_sent',
                'auditable_type' => get_class($event->pendaftaran),
                'auditable_id' => $event->pendaftaran->id,
                'old_values' => null,
                'new_values' => [
                    'template' => $template,
                    'email_to' => $email,
                    'reference_number' => $event->getReferenceNumber(),
                    'status_trigger' => $event->newStatus,
                ],
                'url' => request()->fullUrl(),
                'ip_address' => $event->ipAddress,
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('SendEmailOnPendaftaranStatus: Failed to log audit', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Log failed email send to audit log
     */
    protected function logEmailFailed(PendaftaranSertifikasiStatusChanged $event, ?string $email, string $reason): void
    {
        try {
            AuditLog::create([
                'user_id' => $event->triggeredBy,
                'event' => 'email_failed',
                'auditable_type' => get_class($event->pendaftaran),
                'auditable_id' => $event->pendaftaran->id,
                'old_values' => null,
                'new_values' => [
                    'email_to' => $email,
                    'reference_number' => $event->getReferenceNumber(),
                    'status_trigger' => $event->newStatus,
                    'failure_reason' => $reason,
                ],
                'url' => request()->fullUrl(),
                'ip_address' => $event->ipAddress,
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('SendEmailOnPendaftaranStatus: Failed to log audit failure', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(PendaftaranSertifikasiStatusChanged $event, \Throwable $exception): void
    {
        Log::error('SendEmailOnPendaftaranStatus: Listener failed permanently', [
            'reference' => $event->getReferenceNumber(),
            'error' => $exception->getMessage(),
        ]);

        $this->logEmailFailed(
            $event, 
            $event->getPesertaEmail(), 
            'Permanent failure: ' . $exception->getMessage()
        );
    }
}

<?php

namespace App\Listeners;

use App\Events\PraPendaftaranStatusChanged;
use App\Mail\PraPendaftaranDiterimaMail;
use App\Mail\PraPendaftaranDitolakMail;
use App\Models\AuditLog;
use App\Models\EmailSetting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * ============================================================================
 * Listener: SendEmailOnPraPendaftaranStatus
 * ============================================================================
 * Handles email sending when pra-pendaftaran status changes
 * Triggers: diterima, ditolak
 * 
 * Compliance: BNSP, ISO 17024
 * ============================================================================
 */
class SendEmailOnPraPendaftaranStatus implements ShouldQueue
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
    public function handle(PraPendaftaranStatusChanged $event): void
    {
        // Guard: Skip if status hasn't changed
        if (!$event->hasStatusChanged()) {
            Log::info('SendEmailOnPraPendaftaranStatus: Status unchanged, skipping email', [
                'reference' => $event->getReferenceNumber(),
            ]);
            return;
        }

        // Guard: Check if email settings are configured
        if (!$this->isEmailConfigured()) {
            Log::warning('SendEmailOnPraPendaftaranStatus: Email not configured, skipping', [
                'reference' => $event->getReferenceNumber(),
            ]);
            return;
        }

        // Guard: Check if peserta has email
        $pesertaEmail = $event->getPesertaEmail();
        if (empty($pesertaEmail)) {
            Log::warning('SendEmailOnPraPendaftaranStatus: No peserta email found', [
                'reference' => $event->getReferenceNumber(),
            ]);
            $this->logEmailFailed($event, null, 'No peserta email found');
            return;
        }

        // Determine which email to send based on status
        $normalizedStatus = strtolower(trim($event->newStatus));
        
        try {
            match ($normalizedStatus) {
                'diterima', 'accepted', 'approved' => $this->sendDiterimaMail($event, $pesertaEmail),
                'ditolak', 'rejected', 'declined' => $this->sendDitolakMail($event, $pesertaEmail),
                default => Log::info('SendEmailOnPraPendaftaranStatus: No email template for status', [
                    'status' => $normalizedStatus,
                    'reference' => $event->getReferenceNumber(),
                ]),
            };
        } catch (\Exception $e) {
            Log::error('SendEmailOnPraPendaftaranStatus: Failed to send email', [
                'reference' => $event->getReferenceNumber(),
                'error' => $e->getMessage(),
            ]);
            $this->logEmailFailed($event, $pesertaEmail, $e->getMessage());
            throw $e; // Re-throw for queue retry
        }
    }

    /**
     * Send email for DITERIMA status
     */
    protected function sendDiterimaMail(PraPendaftaranStatusChanged $event, string $email): void
    {
        $praPendaftaran = $event->praPendaftaran;
        
        $mail = new PraPendaftaranDiterimaMail(
            nama: $event->getPesertaName(),
            nomor_pra_pendaftaran: $event->getReferenceNumber(),
            tanggal: $praPendaftaran->created_at?->format('d M Y') ?? now()->format('d M Y'),
            link_status: config('app.url') . '/portal/status/' . $praPendaftaran->id,
        );

        Mail::to($email)->send($mail);

        $this->logEmailSent($event, $email, 'pra-diterima');
        
        Log::info('SendEmailOnPraPendaftaranStatus: Email sent successfully', [
            'template' => 'pra-diterima',
            'to' => $email,
            'reference' => $event->getReferenceNumber(),
        ]);
    }

    /**
     * Send email for DITOLAK status
     */
    protected function sendDitolakMail(PraPendaftaranStatusChanged $event, string $email): void
    {
        $praPendaftaran = $event->praPendaftaran;
        
        // Alasan penolakan is required
        $alasanPenolakan = $praPendaftaran->alasan_penolakan 
            ?? $praPendaftaran->catatan_penolakan 
            ?? $praPendaftaran->keterangan
            ?? 'Dokumen tidak memenuhi persyaratan yang ditentukan.';

        $mail = new PraPendaftaranDitolakMail(
            nama: $event->getPesertaName(),
            nomor_pra_pendaftaran: $event->getReferenceNumber(),
            alasan_penolakan: $alasanPenolakan,
        );

        Mail::to($email)->send($mail);

        $this->logEmailSent($event, $email, 'pra-ditolak');
        
        Log::info('SendEmailOnPraPendaftaranStatus: Email sent successfully', [
            'template' => 'pra-ditolak',
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
    protected function logEmailSent(PraPendaftaranStatusChanged $event, string $email, string $template): void
    {
        try {
            AuditLog::create([
                'user_id' => $event->triggeredBy,
                'event' => 'email_sent',
                'auditable_type' => get_class($event->praPendaftaran),
                'auditable_id' => $event->praPendaftaran->id,
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
            Log::error('SendEmailOnPraPendaftaranStatus: Failed to log audit', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Log failed email send to audit log
     */
    protected function logEmailFailed(PraPendaftaranStatusChanged $event, ?string $email, string $reason): void
    {
        try {
            AuditLog::create([
                'user_id' => $event->triggeredBy,
                'event' => 'email_failed',
                'auditable_type' => get_class($event->praPendaftaran),
                'auditable_id' => $event->praPendaftaran->id,
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
            Log::error('SendEmailOnPraPendaftaranStatus: Failed to log audit failure', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(PraPendaftaranStatusChanged $event, \Throwable $exception): void
    {
        Log::error('SendEmailOnPraPendaftaranStatus: Listener failed permanently', [
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

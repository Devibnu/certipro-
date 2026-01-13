<?php

namespace App\Listeners;

use App\Events\KeputusanSertifikasiDitetapkan;
use App\Mail\KompetenMail;
use App\Mail\BelumKompetenMail;
use App\Models\AuditLog;
use App\Models\EmailSetting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * ============================================================================
 * Listener: SendEmailOnKeputusanFinal
 * ============================================================================
 * Handles email sending when keputusan sertifikasi is finalized
 * Triggers: kompeten_final, belum_kompeten_final
 * 
 * CRITICAL: This listener is IDEMPOTENT - emails for final decisions 
 * are only sent ONCE to prevent duplicates
 * 
 * Compliance: BNSP, ISO 17024
 * ============================================================================
 */
class SendEmailOnKeputusanFinal implements ShouldQueue
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
    public function handle(KeputusanSertifikasiDitetapkan $event): void
    {
        // Guard: Skip if status hasn't changed
        if (!$event->hasStatusChanged()) {
            Log::info('SendEmailOnKeputusanFinal: Status unchanged, skipping email', [
                'reference' => $event->getReferenceNumber(),
            ]);
            return;
        }

        // Guard: Skip if not a final decision
        if (!$event->isFinalDecision()) {
            Log::info('SendEmailOnKeputusanFinal: Not a final decision, skipping email', [
                'reference' => $event->getReferenceNumber(),
                'status' => $event->newStatus,
            ]);
            return;
        }

        // Guard: CRITICAL - Skip if email was already sent for this final decision
        if ($event->emailAlreadySent) {
            Log::warning('SendEmailOnKeputusanFinal: Email already sent for this decision, skipping', [
                'reference' => $event->getReferenceNumber(),
            ]);
            return;
        }

        // Guard: Check if email settings are configured
        if (!$this->isEmailConfigured()) {
            Log::warning('SendEmailOnKeputusanFinal: Email not configured, skipping', [
                'reference' => $event->getReferenceNumber(),
            ]);
            return;
        }

        // Load required relationships
        $event->keputusan->load(['pendaftaranSertifikasi.peserta', 'pendaftaranSertifikasi.praPendaftaran', 'sertifikat']);

        // Guard: Check if peserta has email
        $pesertaEmail = $event->getPesertaEmail();
        if (empty($pesertaEmail)) {
            Log::warning('SendEmailOnKeputusanFinal: No peserta email found', [
                'reference' => $event->getReferenceNumber(),
            ]);
            $this->logEmailFailed($event, null, 'No peserta email found');
            return;
        }

        // Determine which email to send based on status
        $normalizedStatus = strtolower(trim($event->newStatus));
        
        try {
            match ($normalizedStatus) {
                'kompeten', 'kompeten_final', 'competent' => $this->sendKompetenMail($event, $pesertaEmail),
                'belum_kompeten', 'belum_kompeten_final', 'not_competent' => $this->sendBelumKompetenMail($event, $pesertaEmail),
                default => Log::info('SendEmailOnKeputusanFinal: No email template for status', [
                    'status' => $normalizedStatus,
                    'reference' => $event->getReferenceNumber(),
                ]),
            };
        } catch (\Exception $e) {
            Log::error('SendEmailOnKeputusanFinal: Failed to send email', [
                'reference' => $event->getReferenceNumber(),
                'error' => $e->getMessage(),
            ]);
            $this->logEmailFailed($event, $pesertaEmail, $e->getMessage());
            throw $e; // Re-throw for queue retry
        }
    }

    /**
     * Send email for KOMPETEN status
     */
    protected function sendKompetenMail(KeputusanSertifikasiDitetapkan $event, string $email): void
    {
        $nomorSertifikat = $event->getNomorSertifikat() 
            ?? 'CERT-' . str_pad($event->keputusan->id, 6, '0', STR_PAD_LEFT);
        
        $linkSertifikat = config('app.url') . '/portal/sertifikat/' . $event->keputusan->id;

        $mail = new KompetenMail(
            nama: $event->getPesertaName(),
            nomor_sertifikat: $nomorSertifikat,
            masa_berlaku: $event->getMasaBerlaku(),
            link_sertifikat: $linkSertifikat,
        );

        Mail::to($email)->send($mail);

        $this->logEmailSent($event, $email, 'kompeten');
        
        Log::info('SendEmailOnKeputusanFinal: KOMPETEN email sent successfully', [
            'template' => 'kompeten',
            'to' => $email,
            'reference' => $event->getReferenceNumber(),
            'nomor_sertifikat' => $nomorSertifikat,
        ]);
    }

    /**
     * Send email for BELUM KOMPETEN status
     */
    protected function sendBelumKompetenMail(KeputusanSertifikasiDitetapkan $event, string $email): void
    {
        $pendaftaran = $event->keputusan->pendaftaranSertifikasi;
        $nomorPendaftaran = $pendaftaran?->nomor_pendaftaran 
            ?? 'REG-' . str_pad($pendaftaran?->id ?? $event->keputusan->id, 6, '0', STR_PAD_LEFT);

        $mail = new BelumKompetenMail(
            nama: $event->getPesertaName(),
            nomor_pendaftaran: $nomorPendaftaran,
        );

        Mail::to($email)->send($mail);

        $this->logEmailSent($event, $email, 'belum-kompeten');
        
        Log::info('SendEmailOnKeputusanFinal: BELUM KOMPETEN email sent successfully', [
            'template' => 'belum-kompeten',
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
    protected function logEmailSent(KeputusanSertifikasiDitetapkan $event, string $email, string $template): void
    {
        try {
            AuditLog::create([
                'user_id' => $event->triggeredBy,
                'event' => 'email_sent',
                'auditable_type' => get_class($event->keputusan),
                'auditable_id' => $event->keputusan->id,
                'old_values' => null,
                'new_values' => [
                    'template' => $template,
                    'email_to' => $email,
                    'reference_number' => $event->getReferenceNumber(),
                    'status_trigger' => $event->newStatus,
                    'is_final_decision' => true,
                ],
                'url' => request()->fullUrl(),
                'ip_address' => $event->ipAddress,
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('SendEmailOnKeputusanFinal: Failed to log audit', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Log failed email send to audit log
     */
    protected function logEmailFailed(KeputusanSertifikasiDitetapkan $event, ?string $email, string $reason): void
    {
        try {
            AuditLog::create([
                'user_id' => $event->triggeredBy,
                'event' => 'email_failed',
                'auditable_type' => get_class($event->keputusan),
                'auditable_id' => $event->keputusan->id,
                'old_values' => null,
                'new_values' => [
                    'email_to' => $email,
                    'reference_number' => $event->getReferenceNumber(),
                    'status_trigger' => $event->newStatus,
                    'failure_reason' => $reason,
                    'is_final_decision' => true,
                ],
                'url' => request()->fullUrl(),
                'ip_address' => $event->ipAddress,
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('SendEmailOnKeputusanFinal: Failed to log audit failure', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(KeputusanSertifikasiDitetapkan $event, \Throwable $exception): void
    {
        Log::error('SendEmailOnKeputusanFinal: Listener failed permanently', [
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

<?php

namespace App\Listeners;

use App\Events\PraPendaftaranDiterimaEvent;
use App\Services\EmailNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * ============================================================================
 * Listener: SendPraPendaftaranDiterimaEmail
 * ============================================================================
 * Mendengarkan event PraPendaftaranDiterimaEvent dan mengirim email notifikasi.
 * 
 * Features:
 * - Queue-based (async)
 * - Retry mechanism (3x dengan backoff)
 * - Idempotent (tidak kirim ulang jika sudah pernah kirim)
 * - Audit logging
 * 
 * Triggered by:
 * - PraPendaftaranAdminController::updateStatus() ketika status = 'diterima'
 * 
 * Compliance: BNSP, ISO 17024, EMAIL_STATUS_MATRIX.md
 * ============================================================================
 */
class SendPraPendaftaranDiterimaEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 60;

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public $backoff = [30, 60, 120]; // 30s, 1min, 2min

    /**
     * The email notification service
     */
    protected EmailNotificationService $emailService;

    /**
     * Create the event listener.
     */
    public function __construct(EmailNotificationService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Handle the event.
     */
    public function handle(PraPendaftaranDiterimaEvent $event): void
    {
        $praPendaftaran = $event->praPendaftaran;

        Log::info('[Listener] Processing PraPendaftaranDiterimaEvent', [
            'pra_id' => $praPendaftaran->id,
            'email' => $praPendaftaran->email,
            'attempt' => $this->attempts(),
        ]);

        try {
            $this->emailService->sendPraDiterimaFromEvent($praPendaftaran);
            
            // ========================================================================
            // MARK EMAIL AS SENT (Idempotent tracking)
            // ========================================================================
            $praPendaftaran->update([
                'status_email' => 'sent',
                'email_sent_at' => now(),
            ]);
            
            Log::info('[Listener] Email marked as sent', [
                'pra_id' => $praPendaftaran->id,
                'status_email' => 'sent',
            ]);
            
        } catch (\Exception $e) {
            Log::error('[Listener] Failed to process PraPendaftaranDiterimaEvent', [
                'pra_id' => $praPendaftaran->id,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            // Mark as failed if max retries reached
            if ($this->attempts() >= 3) {
                $praPendaftaran->update(['status_email' => 'failed']);
            }

            // Re-throw untuk retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(PraPendaftaranDiterimaEvent $event, \Throwable $exception): void
    {
        Log::critical('[Listener] PraPendaftaranDiterimaEmail failed after max retries', [
            'pra_id' => $event->praPendaftaran->id,
            'email' => $event->praPendaftaran->email,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // TODO: Notify admin via Slack/Telegram
        // Notifikasi::adminCritical('Email gagal setelah 3x retry', ...);
    }
}

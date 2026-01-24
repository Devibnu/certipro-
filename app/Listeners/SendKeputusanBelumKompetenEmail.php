<?php

namespace App\Listeners;

use App\Events\KeputusanBelumKompetenEvent;
use App\Services\EmailNotificationService;
use App\Services\InAppNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * ============================================================================
 * Listener: SendKeputusanBelumKompetenEmail
 * ============================================================================
 * Mendengarkan event KeputusanBelumKompetenEvent dan mengirim email notifikasi.
 * 
 * Features:
 * - Queue-based (async)
 * - Retry mechanism (3x dengan backoff)
 * - Idempotent (tidak kirim ulang jika sudah pernah kirim)
 * - Audit logging
 * 
 * Triggered by:
 * - KeputusanSertifikasiController::simpan() ketika keputusan = 'belum_kompeten'
 * 
 * Compliance: BNSP, ISO 17024, EMAIL_STATUS_MATRIX.md
 * ============================================================================
 */
class SendKeputusanBelumKompetenEmail implements ShouldQueue
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
    public $backoff = [30, 60, 120];

    /**
     * The email notification service
     */
    protected EmailNotificationService $emailService;

    /**
     * The in-app notification service
     */
    protected InAppNotificationService $inAppService;

    /**
     * Create the event listener.
     */
    public function __construct(EmailNotificationService $emailService, InAppNotificationService $inAppService)
    {
        $this->emailService = $emailService;
        $this->inAppService = $inAppService;
    }

    /**
     * Handle the event.
     */
    public function handle(KeputusanBelumKompetenEvent $event): void
    {
        $keputusan = $event->keputusan;

        Log::info('[Listener] Processing KeputusanBelumKompetenEvent', [
            'keputusan_id' => $keputusan->id,
            'attempt' => $this->attempts(),
        ]);

        try {
            $this->emailService->sendBelumKompetenFromEvent($keputusan);
            
            // Create in-app notification
            $asesiName = $keputusan->pendaftaranSertifikasi->asesi->nama_lengkap ?? 'Unknown';
            $this->inAppService->notifyKeputusanBelumKompeten(
                $keputusan->id,
                $asesiName
            );
            
            Log::info('[Listener] In-app notification created for Keputusan Belum Kompeten', [
                'keputusan_id' => $keputusan->id,
            ]);
        } catch (\Exception $e) {
            Log::error('[Listener] Failed to process KeputusanBelumKompetenEvent', [
                'keputusan_id' => $keputusan->id,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(KeputusanBelumKompetenEvent $event, \Throwable $exception): void
    {
        Log::critical('[Listener] KeputusanBelumKompetenEmail failed after max retries', [
            'keputusan_id' => $event->keputusan->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}

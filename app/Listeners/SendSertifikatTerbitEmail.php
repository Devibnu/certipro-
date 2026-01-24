<?php

namespace App\Listeners;

use App\Events\SertifikatTerbitEvent;
use App\Services\InAppNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * ============================================================================
 * Listener: SendSertifikatTerbitEmail
 * ============================================================================
 * Mendengarkan event SertifikatTerbitEvent dan mengirim email notifikasi.
 * 
 * STATUS: RESERVED (belum diimplementasikan di flow saat ini)
 * 
 * Catatan:
 * - Event dan Listener sudah dibuat untuk persiapan future feature
 * - Saat ini belum ada Mailable class untuk sertifikat terbit
 * - Method sendSertifikatTerbitFromEvent() belum ada di EmailNotificationService
 * 
 * TODO:
 * - Buat Mailable class: SertifikatTerbitMail
 * - Buat email template: emails.sertifikat-terbit
 * - Tambahkan method di EmailNotificationService
 * - Trigger event di SertifikatController::approve()
 * 
 * Compliance: BNSP, ISO 17024, EMAIL_STATUS_MATRIX.md
 * ============================================================================
 */
class SendSertifikatTerbitEmail implements ShouldQueue
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
     * The in-app notification service
     */
    protected InAppNotificationService $inAppService;

    /**
     * Create the event listener.
     */
    public function __construct(InAppNotificationService $inAppService)
    {
        $this->inAppService = $inAppService;
    }

    /**
     * Handle the event.
     */
    public function handle(SertifikatTerbitEvent $event): void
    {
        $sertifikat = $event->sertifikat;

        Log::warning('[Listener] SertifikatTerbitEvent received but not implemented yet', [
            'sertifikat_id' => $sertifikat->id,
        ]);

        // TODO: Implement email sending
        // $this->emailService->sendSertifikatTerbitFromEvent($sertifikat);
        
        // Create in-app notification
        try {
            $asesiName = $sertifikat->pendaftaranSertifikasi->asesi->nama_lengkap ?? 'Unknown';
            $this->inAppService->notifySertifikatDiterbitkan(
                $sertifikat->id,
                $asesiName
            );
            Log::info('[Listener] In-app notification created for Sertifikat', [
                'sertifikat_id' => $sertifikat->id,
            ]);
        } catch (\Exception $e) {
            Log::error('[Listener] Failed to create in-app notification for Sertifikat', [
                'sertifikat_id' => $sertifikat->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(SertifikatTerbitEvent $event, \Throwable $exception): void
    {
        Log::critical('[Listener] SertifikatTerbitEmail failed', [
            'sertifikat_id' => $event->sertifikat->id,
            'error' => $exception->getMessage(),
        ]);
    }
}

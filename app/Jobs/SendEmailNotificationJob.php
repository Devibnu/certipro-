<?php

namespace App\Jobs;

use App\Models\NotificationLog;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendEmailNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah retry attempts
     */
    public $tries = 5;

    /**
     * Timeout in seconds
     */
    public $timeout = 30;

    /**
     * Backoff time in seconds (progressive)
     */
    public $backoff = [30, 60, 120, 300, 600];

    /**
     * Delete job if models are missing
     */
    public $deleteWhenMissingModels = true;

    public function __construct(
        public NotificationLog $notificationLog
    ) {}

    /**
     * Execute the job
     */
    public function handle(EmailService $emailService): void
    {
        try {
            // Skip jika sudah sent
            if ($this->notificationLog->status === NotificationLog::STATUS_SENT) {
                Log::info("Email sudah terkirim, skip", [
                    'notification_log_id' => $this->notificationLog->id,
                ]);
                return;
            }

            // Kirim email
            $emailService->send($this->notificationLog);

            // Mark as sent
            $this->notificationLog->markAsSent();

            Log::info("Email notification berhasil", [
                'notification_log_id' => $this->notificationLog->id,
                'event_type' => $this->notificationLog->event_type,
                'recipient' => $this->notificationLog->recipient,
            ]);

        } catch (\Exception $e) {
            // Mark as failed
            $this->notificationLog->markAsFailed($e->getMessage());

            Log::error("Email notification gagal", [
                'notification_log_id' => $this->notificationLog->id,
                'retry_count' => $this->notificationLog->retry_count,
                'error' => $e->getMessage(),
            ]);

            // Re-throw exception agar queue retry otomatis
            throw $e;
        }
    }

    /**
     * Handle a job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical("Email notification permanently failed", [
            'notification_log_id' => $this->notificationLog->id,
            'event_type' => $this->notificationLog->event_type,
            'recipient' => $this->notificationLog->recipient,
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage(),
        ]);

        // Update final status
        $this->notificationLog->update([
            'status' => NotificationLog::STATUS_FAILED,
            'error_message' => "PERMANENT FAILURE after {$this->attempts()} attempts: " . $exception->getMessage(),
        ]);
    }
}

<?php

namespace App\Jobs;

use App\Models\NotificationLog;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    public $timeout = 30;
    public $backoff = [30, 60, 120, 300, 600];
    public $deleteWhenMissingModels = true;

    public function __construct(
        public NotificationLog $notificationLog
    ) {}

    public function handle(WhatsAppService $whatsAppService): void
    {
        try {
            // Skip jika sudah sent
            if ($this->notificationLog->status === NotificationLog::STATUS_SENT) {
                Log::info("WhatsApp sudah terkirim, skip", [
                    'notification_log_id' => $this->notificationLog->id,
                ]);
                return;
            }

            // Kirim WhatsApp
            $whatsAppService->send($this->notificationLog);

            // Mark as sent
            $this->notificationLog->markAsSent();

            Log::info("WhatsApp notification berhasil", [
                'notification_log_id' => $this->notificationLog->id,
                'event_type' => $this->notificationLog->event_type,
                'recipient' => $this->notificationLog->recipient,
            ]);

        } catch (\Exception $e) {
            // Mark as failed
            $this->notificationLog->markAsFailed($e->getMessage());

            Log::error("WhatsApp notification gagal", [
                'notification_log_id' => $this->notificationLog->id,
                'retry_count' => $this->notificationLog->retry_count,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::critical("WhatsApp notification permanently failed", [
            'notification_log_id' => $this->notificationLog->id,
            'event_type' => $this->notificationLog->event_type,
            'recipient' => $this->notificationLog->recipient,
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage(),
        ]);

        $this->notificationLog->update([
            'status' => NotificationLog::STATUS_FAILED,
            'error_message' => "PERMANENT FAILURE after {$this->attempts()} attempts: " . $exception->getMessage(),
        ]);
    }
}

<?php

namespace App\Services;

use App\Jobs\SendEmailNotificationJob;
use App\Jobs\SendWhatsAppNotificationJob;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send notification via multiple channels
     */
    public function send(
        string $eventType,
        int $entityId,
        array $data,
        array $channels = ['email', 'whatsapp']
    ): void {
        foreach ($channels as $channel) {
            $this->sendToChannel($eventType, $entityId, $data, $channel);
        }
    }

    /**
     * Send notification ke channel tertentu
     */
    protected function sendToChannel(
        string $eventType,
        int $entityId,
        array $data,
        string $channel
    ): void {
        try {
            // Generate unique key untuk idempotent
            $uniqueKey = NotificationLog::generateUniqueKey($eventType, $entityId, $channel);

            // Skip jika sudah pernah dikirim
            if (NotificationLog::alreadySent($uniqueKey)) {
                Log::info("Notification sudah pernah dikirim", [
                    'unique_key' => $uniqueKey,
                    'event_type' => $eventType,
                    'channel' => $channel,
                ]);
                return;
            }

            // Tentukan recipient
            $recipient = $channel === NotificationLog::CHANNEL_EMAIL 
                ? ($data['email'] ?? null)
                : ($data['no_hp'] ?? null);

            if (!$recipient) {
                Log::warning("Recipient tidak ditemukan untuk channel: {$channel}", [
                    'event_type' => $eventType,
                    'data' => $data,
                ]);
                return;
            }

            // Create notification log
            $notificationLog = NotificationLog::create([
                'channel' => $channel,
                'event_type' => $eventType,
                'recipient' => $recipient,
                'payload' => $data,
                'status' => NotificationLog::STATUS_PENDING,
                'unique_key' => $uniqueKey,
            ]);

            // Dispatch ke queue sesuai channel
            match ($channel) {
                NotificationLog::CHANNEL_EMAIL => SendEmailNotificationJob::dispatch($notificationLog)
                    ->onQueue('notifications'),
                    
                NotificationLog::CHANNEL_WHATSAPP => SendWhatsAppNotificationJob::dispatch($notificationLog)
                    ->onQueue('notifications'),
                    
                default => Log::error("Channel tidak dikenali: {$channel}"),
            };

            Log::info("Notification queued", [
                'notification_log_id' => $notificationLog->id,
                'event_type' => $eventType,
                'channel' => $channel,
                'recipient' => $recipient,
            ]);

        } catch (\Exception $e) {
            Log::error("Gagal queue notification", [
                'event_type' => $eventType,
                'channel' => $channel,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Retry failed notifications
     */
    public function retryFailed(int $maxRetries = 5): int
    {
        $failedNotifications = NotificationLog::retryable($maxRetries)->get();
        $retried = 0;

        foreach ($failedNotifications as $notification) {
            try {
                match ($notification->channel) {
                    NotificationLog::CHANNEL_EMAIL => SendEmailNotificationJob::dispatch($notification)
                        ->onQueue('notifications'),
                        
                    NotificationLog::CHANNEL_WHATSAPP => SendWhatsAppNotificationJob::dispatch($notification)
                        ->onQueue('notifications'),
                };

                $retried++;
            } catch (\Exception $e) {
                Log::error("Gagal retry notification", [
                    'notification_log_id' => $notification->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $retried;
    }
}

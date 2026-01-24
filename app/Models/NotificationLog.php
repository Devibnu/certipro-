<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $fillable = [
        'channel',
        'event_type',
        'recipient',
        'payload',
        'status',
        'error_message',
        'retry_count',
        'sent_at',
        'unique_key',
    ];

    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
    ];

    const CHANNEL_EMAIL = 'email';
    const CHANNEL_WHATSAPP = 'whatsapp';

    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';

    /**
     * Generate unique key untuk prevent duplicate notification
     */
    public static function generateUniqueKey(string $eventType, int $entityId, string $channel): string
    {
        return md5("{$eventType}_{$entityId}_{$channel}");
    }

    /**
     * Cek apakah notifikasi sudah pernah dikirim
     */
    public static function alreadySent(string $uniqueKey): bool
    {
        return self::where('unique_key', $uniqueKey)
            ->where('status', self::STATUS_SENT)
            ->exists();
    }

    /**
     * Scope untuk pending notifications
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope untuk failed notifications yang bisa di-retry
     */
    public function scopeRetryable($query, int $maxRetries = 5)
    {
        return $query->where('status', self::STATUS_FAILED)
            ->where('retry_count', '<', $maxRetries);
    }

    /**
     * Mark as sent
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'retry_count' => $this->retry_count + 1,
        ]);
    }
}

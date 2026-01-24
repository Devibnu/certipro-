<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InAppNotification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'icon',
        'icon_color',
        'route_name',
        'route_params',
        'read_at',
    ];

    protected $casts = [
        'route_params' => 'array',
        'read_at' => 'datetime',
    ];

    // Notification Types
    const TYPE_PENDAFTARAN_BARU = 'pendaftaran_baru';
    const TYPE_ASESMEN_SELESAI = 'asesmen_selesai';
    const TYPE_SERTIFIKAT_DITERBITKAN = 'sertifikat_diterbitkan';
    const TYPE_KEPUTUSAN_KOMPETEN = 'keputusan_kompeten';
    const TYPE_KEPUTUSAN_BELUM_KOMPETEN = 'keputusan_belum_kompeten';

    /**
     * User relationship
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope untuk unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope untuk read notifications
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Mark as read
     */
    public function markAsRead(): void
    {
        if ($this->read_at === null) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Mark as unread
     */
    public function markAsUnread(): void
    {
        $this->update(['read_at' => null]);
    }

    /**
     * Check if notification is unread
     */
    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * Get the URL for this notification
     */
    public function getUrl(): ?string
    {
        if (!$this->route_name) {
            return null;
        }

        try {
            return route($this->route_name, $this->route_params ?? []);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get relative time (e.g., "13 menit yang lalu")
     */
    public function getRelativeTime(): string
    {
        return $this->created_at->locale('id')->diffForHumans();
    }

    /**
     * Bulk mark as read
     */
    public static function markAllAsReadForUser(int $userId): int
    {
        return self::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Create notification helper
     */
    public static function createNotification(array $data): self
    {
        return self::create($data);
    }
}

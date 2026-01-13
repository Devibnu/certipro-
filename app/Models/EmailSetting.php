<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

/**
 * Model EmailSetting
 * 
 * Single-row table untuk menyimpan konfigurasi email sistem.
 * Password SMTP dienkripsi menggunakan Laravel encryption.
 * Cache digunakan untuk performa dengan TTL 24 jam.
 * 
 * @property int $id
 * @property string $mail_driver
 * @property string|null $mail_host
 * @property int $mail_port
 * @property string $mail_encryption
 * @property string|null $mail_username
 * @property string|null $mail_password (encrypted)
 * @property string $mail_from_name
 * @property string $mail_from_address
 * @property bool $is_active
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class EmailSetting extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'email_settings';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'mail_driver',
        'mail_host',
        'mail_port',
        'mail_encryption',
        'mail_username',
        'mail_password',
        'mail_from_name',
        'mail_from_address',
        'is_active',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     * 
     * Password dienkripsi otomatis menggunakan Laravel's encrypted cast.
     */
    protected $casts = [
        'mail_password' => 'encrypted',
        'mail_port' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'mail_password',
    ];

    /**
     * Cache configuration
     */
    const CACHE_KEY = 'email_settings_active';
    const CACHE_TTL = 86400; // 24 hours in seconds

    /**
     * Available mail drivers
     */
    const DRIVERS = ['smtp', 'sendmail', 'mailgun', 'ses', 'log'];

    /**
     * Available encryption options
     */
    const ENCRYPTIONS = ['tls', 'ssl', 'null'];

    /**
     * Get the user who created this setting.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this setting.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the active email settings (singleton pattern with cache).
     * 
     * @return self|null
     */
    public static function getActive(): ?self
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return self::where('is_active', true)->first();
        });
    }

    /**
     * Get settings without cache (for forms).
     * 
     * @return self|null
     */
    public static function getActiveWithoutCache(): ?self
    {
        return self::where('is_active', true)->first();
    }

    /**
     * Clear the cache.
     * 
     * @return void
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Refresh the cache with current data.
     * 
     * @return self|null
     */
    public static function refreshCache(): ?self
    {
        self::clearCache();
        return self::getActive();
    }

    /**
     * Check if email is configured.
     * 
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->mail_host) && !empty($this->mail_username);
    }

    /**
     * Check if password is set.
     * 
     * @return bool
     */
    public function hasPassword(): bool
    {
        return !empty($this->mail_password);
    }

    /**
     * Get settings as array for config injection.
     * 
     * @return array
     */
    public function toConfigArray(): array
    {
        return [
            'driver' => $this->mail_driver,
            'host' => $this->mail_host,
            'port' => $this->mail_port,
            'encryption' => $this->mail_encryption === 'null' ? null : $this->mail_encryption,
            'username' => $this->mail_username,
            'password' => $this->mail_password, // Already decrypted by cast
            'from_address' => $this->mail_from_address,
            'from_name' => $this->mail_from_name,
        ];
    }

    /**
     * Get settings for display (masked password).
     * 
     * @return array
     */
    public function toDisplayArray(): array
    {
        return [
            'mail_driver' => $this->mail_driver,
            'mail_host' => $this->mail_host,
            'mail_port' => $this->mail_port,
            'mail_encryption' => $this->mail_encryption,
            'mail_username' => $this->mail_username,
            'mail_password' => $this->hasPassword() ? '********' : null,
            'mail_from_name' => $this->mail_from_name,
            'mail_from_address' => $this->mail_from_address,
            'is_active' => $this->is_active,
        ];
    }

    /**
     * Get comparison array for audit log (password masked).
     * 
     * @return array
     */
    public function toAuditArray(): array
    {
        return [
            'mail_driver' => $this->mail_driver,
            'mail_host' => $this->mail_host,
            'mail_port' => $this->mail_port,
            'mail_encryption' => $this->mail_encryption,
            'mail_username' => $this->mail_username,
            'mail_password' => $this->hasPassword() ? '[ENCRYPTED]' : null,
            'mail_from_name' => $this->mail_from_name,
            'mail_from_address' => $this->mail_from_address,
        ];
    }
}

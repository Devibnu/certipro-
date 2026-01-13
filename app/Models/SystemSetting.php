<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

/**
 * Model SystemSetting
 * 
 * Menyimpan konfigurasi sistem secara dinamis di database.
 * Digunakan untuk Email Settings, dll tanpa perlu edit .env
 * 
 * @property int $id
 * @property string $group
 * @property string $key
 * @property string|null $value
 * @property bool $is_encrypted
 * @property string $type
 * @property string|null $description
 * @property int|null $updated_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $fillable = [
        'group',
        'key',
        'value',
        'is_encrypted',
        'type',
        'description',
        'updated_by',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Cache key prefix
     */
    const CACHE_PREFIX = 'system_settings';
    const CACHE_TTL = 3600; // 1 hour

    /**
     * Get the user who last updated this setting
     */
    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get setting value (auto-decrypt if encrypted)
     */
    public function getDecodedValueAttribute(): mixed
    {
        if (empty($this->value)) {
            return null;
        }

        // Decrypt if encrypted
        $value = $this->is_encrypted ? $this->decryptValue($this->value) : $this->value;

        // Cast to appropriate type
        return match ($this->type) {
            'integer' => (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Set value (auto-encrypt if needed)
     */
    public function setValueAttribute($value): void
    {
        if ($this->is_encrypted && !empty($value)) {
            $this->attributes['value'] = $this->encryptValue($value);
        } else {
            $this->attributes['value'] = $value;
        }
    }

    /**
     * Encrypt a value
     */
    protected function encryptValue(string $value): string
    {
        return Crypt::encryptString($value);
    }

    /**
     * Decrypt a value
     */
    protected function decryptValue(string $value): string
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            // Return original if decryption fails (might be plain text)
            return $value;
        }
    }

    /**
     * Get a setting value by group and key
     * 
     * @param string $group
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getValue(string $group, string $key, mixed $default = null): mixed
    {
        $cacheKey = self::CACHE_PREFIX . ".{$group}.{$key}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($group, $key, $default) {
            $setting = self::where('group', $group)->where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }

            return $setting->decoded_value ?? $default;
        });
    }

    /**
     * Set a setting value
     * 
     * @param string $group
     * @param string $key
     * @param mixed $value
     * @param bool $isEncrypted
     * @param string $type
     * @param string|null $description
     * @return self
     */
    public static function setValue(
        string $group,
        string $key,
        mixed $value,
        bool $isEncrypted = false,
        string $type = 'string',
        ?string $description = null
    ): self {
        // Convert value to string for storage
        $stringValue = match ($type) {
            'json' => json_encode($value),
            'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };

        $setting = self::updateOrCreate(
            ['group' => $group, 'key' => $key],
            [
                'value' => $isEncrypted ? Crypt::encryptString($stringValue) : $stringValue,
                'is_encrypted' => $isEncrypted,
                'type' => $type,
                'description' => $description,
                'updated_by' => auth()->id(),
            ]
        );

        // Clear cache
        self::clearCache($group, $key);

        return $setting;
    }

    /**
     * Get all settings for a group
     * 
     * @param string $group
     * @return array
     */
    public static function getGroup(string $group): array
    {
        $cacheKey = self::CACHE_PREFIX . ".group.{$group}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($group) {
            $settings = self::where('group', $group)->get();
            
            $result = [];
            foreach ($settings as $setting) {
                $result[$setting->key] = $setting->decoded_value;
            }
            
            return $result;
        });
    }

    /**
     * Set multiple settings at once
     * 
     * @param string $group
     * @param array $settings Array of ['key' => ['value' => x, 'encrypted' => bool, 'type' => x]]
     * @return void
     */
    public static function setGroup(string $group, array $settings): void
    {
        foreach ($settings as $key => $config) {
            if (is_array($config)) {
                self::setValue(
                    $group,
                    $key,
                    $config['value'] ?? null,
                    $config['encrypted'] ?? false,
                    $config['type'] ?? 'string',
                    $config['description'] ?? null
                );
            } else {
                self::setValue($group, $key, $config);
            }
        }

        // Clear group cache
        self::clearGroupCache($group);
    }

    /**
     * Clear cache for a specific setting
     */
    public static function clearCache(string $group, string $key): void
    {
        Cache::forget(self::CACHE_PREFIX . ".{$group}.{$key}");
        Cache::forget(self::CACHE_PREFIX . ".group.{$group}");
    }

    /**
     * Clear cache for a group
     */
    public static function clearGroupCache(string $group): void
    {
        Cache::forget(self::CACHE_PREFIX . ".group.{$group}");

        // Clear individual keys
        $settings = self::where('group', $group)->pluck('key');
        foreach ($settings as $key) {
            Cache::forget(self::CACHE_PREFIX . ".{$group}.{$key}");
        }
    }

    /**
     * Clear all settings cache
     */
    public static function clearAllCache(): void
    {
        // Get all groups
        $groups = self::select('group')->distinct()->pluck('group');
        
        foreach ($groups as $group) {
            self::clearGroupCache($group);
        }
    }
}

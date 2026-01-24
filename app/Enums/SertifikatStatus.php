<?php

namespace App\Enums;

use Carbon\Carbon;

enum SertifikatStatus: string
{
    case BELUM_TERBIT = 'belum_terbit';
    case TERBIT = 'terbit';
    case KADALUARSA = 'kadaluarsa';
    case DICABUT = 'dicabut';

    public function label(): string
    {
        return match($this) {
            self::BELUM_TERBIT => 'Belum Terbit',
            self::TERBIT => 'Terbit',
            self::KADALUARSA => 'Kadaluarsa',
            self::DICABUT => 'Dicabut',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::BELUM_TERBIT => 'warning',
            self::TERBIT => 'success',
            self::KADALUARSA => 'secondary',
            self::DICABUT => 'danger',
        };
    }

    public function badge(): string
    {
        return match($this) {
            self::BELUM_TERBIT => 'bg-yellow-100 text-yellow-800',
            self::TERBIT => 'bg-green-100 text-green-800 font-bold',
            self::KADALUARSA => 'bg-gray-100 text-gray-800',
            self::DICABUT => 'bg-red-100 text-red-800',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::BELUM_TERBIT => '⏳',
            self::TERBIT => '📜',
            self::KADALUARSA => '⏰',
            self::DICABUT => '🚫',
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match($this) {
            self::BELUM_TERBIT => $newStatus === self::TERBIT,
            self::TERBIT => in_array($newStatus, [self::KADALUARSA, self::DICABUT]),
            self::KADALUARSA => false, // Terminal
            self::DICABUT => false,    // Terminal
        };
    }

    public function isActive(): bool
    {
        return $this === self::TERBIT;
    }

    public function isValid(): bool
    {
        return $this === self::TERBIT;
    }

    public function isExpired(): bool
    {
        return $this === self::KADALUARSA;
    }

    public function isRevoked(): bool
    {
        return $this === self::DICABUT;
    }

    public function canBeDownloaded(): bool
    {
        return in_array($this, [self::TERBIT, self::KADALUARSA]);
    }

    public function canBeRevoked(): bool
    {
        return $this === self::TERBIT;
    }

    public function requiresRenewal(): bool
    {
        return $this === self::KADALUARSA;
    }

    public static function initial(): self
    {
        return self::BELUM_TERBIT;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return array_map(
            fn($case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }

    /**
     * Get validity period description
     */
    public function validityDescription(): string
    {
        return match($this) {
            self::BELUM_TERBIT => 'Menunggu penerbitan',
            self::TERBIT => 'Aktif dan berlaku',
            self::KADALUARSA => 'Sudah tidak berlaku',
            self::DICABUT => 'Dicabut oleh penerbit',
        };
    }

    /**
     * Check if certificate should be marked as expired
     */
    public static function shouldExpire(Carbon $expiryDate): bool
    {
        return $expiryDate->isPast();
    }
}

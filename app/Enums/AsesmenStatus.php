<?php

namespace App\Enums;

enum AsesmenStatus: string
{
    case BELUM_DIMULAI = 'belum_dimulai';
    case DALAM_PROSES = 'proses';  // Database uses 'proses' not 'dalam_proses'
    case SELESAI = 'selesai';

    public function label(): string
    {
        return match($this) {
            self::BELUM_DIMULAI => 'Belum Dimulai',
            self::DALAM_PROSES => 'Dalam Proses',
            self::SELESAI => 'Selesai',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::BELUM_DIMULAI => 'secondary',
            self::DALAM_PROSES => 'warning',
            self::SELESAI => 'success',
        };
    }

    public function badge(): string
    {
        return match($this) {
            self::BELUM_DIMULAI => 'bg-gray-100 text-gray-800',
            self::DALAM_PROSES => 'bg-yellow-100 text-yellow-800',
            self::SELESAI => 'bg-green-100 text-green-800',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::BELUM_DIMULAI => '⏸️',
            self::DALAM_PROSES => '▶️',
            self::SELESAI => '✅',
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match($this) {
            self::BELUM_DIMULAI => $newStatus === self::DALAM_PROSES,
            self::DALAM_PROSES => $newStatus === self::SELESAI,
            self::SELESAI => false, // Terminal state
        };
    }

    public function isCompleted(): bool
    {
        return $this === self::SELESAI;
    }

    public function isInProgress(): bool
    {
        return $this === self::DALAM_PROSES;
    }

    public function canStart(): bool
    {
        return $this === self::BELUM_DIMULAI;
    }

    public function canComplete(): bool
    {
        return $this === self::DALAM_PROSES;
    }

    public static function initial(): self
    {
        return self::BELUM_DIMULAI;
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
     * Get progress percentage
     */
    public function progressPercentage(): int
    {
        return match($this) {
            self::BELUM_DIMULAI => 0,
            self::DALAM_PROSES => 50,
            self::SELESAI => 100,
        };
    }
}

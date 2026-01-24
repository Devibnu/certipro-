<?php

namespace App\Enums;

enum KeputusanStatus: string
{
    case BELUM_DITETAPKAN = 'belum_ditetapkan';  // For 'status' column
    case KOMPETEN = 'kompeten';                  // For 'keputusan' column  
    case BELUM_KOMPETEN = 'belum_kompeten';      // For 'keputusan' column

    public function label(): string
    {
        return match($this) {
            self::BELUM_DITETAPKAN => 'Belum Ditetapkan',
            self::KOMPETEN => 'Kompeten',
            self::BELUM_KOMPETEN => 'Belum Kompeten',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::BELUM_DITETAPKAN => 'warning',
            self::KOMPETEN => 'success',
            self::BELUM_KOMPETEN => 'danger',
        };
    }

    public function badge(): string
    {
        return match($this) {
            self::BELUM_DITETAPKAN => 'bg-yellow-100 text-yellow-800',
            self::KOMPETEN => 'bg-green-100 text-green-800 font-bold',
            self::BELUM_KOMPETEN => 'bg-red-100 text-red-800',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::BELUM_DITETAPKAN => '⏳',
            self::KOMPETEN => '🏆',
            self::BELUM_KOMPETEN => '⚠️',
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match($this) {
            self::BELUM_DITETAPKAN => in_array($newStatus, [self::KOMPETEN, self::BELUM_KOMPETEN]),
            self::KOMPETEN => false,        // Terminal
            self::BELUM_KOMPETEN => false,  // Terminal
        };
    }

    public function isCompetent(): bool
    {
        return $this === self::KOMPETEN;
    }

    public function isNotCompetent(): bool
    {
        return $this === self::BELUM_KOMPETEN;
    }

    public function isDecided(): bool
    {
        return in_array($this, [self::KOMPETEN, self::BELUM_KOMPETEN]);
    }

    public function canDecide(): bool
    {
        return $this === self::BELUM_DITETAPKAN;
    }

    public function allowsCertificateIssuance(): bool
    {
        return $this === self::KOMPETEN;
    }

    public static function initial(): self
    {
        return self::BELUM_DITETAPKAN;
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
     * Get decision outcome for reporting
     */
    public function outcome(): ?string
    {
        return match($this) {
            self::KOMPETEN => 'PASS',
            self::BELUM_KOMPETEN => 'FAIL',
            self::BELUM_DITETAPKAN => null,
        };
    }
}

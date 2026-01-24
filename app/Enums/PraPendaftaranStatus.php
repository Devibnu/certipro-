<?php

namespace App\Enums;

enum PraPendaftaranStatus: string
{
    case DIAJUKAN = 'diajukan';
    case DITERIMA = 'diterima';
    case DITOLAK = 'ditolak';

    public function label(): string
    {
        return match($this) {
            self::DIAJUKAN => 'Diajukan',
            self::DITERIMA => 'Diterima',
            self::DITOLAK => 'Ditolak',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DIAJUKAN => 'warning',
            self::DITERIMA => 'success',
            self::DITOLAK => 'danger',
        };
    }

    public function badge(): string
    {
        return match($this) {
            self::DIAJUKAN => 'bg-yellow-100 text-yellow-800',
            self::DITERIMA => 'bg-green-100 text-green-800',
            self::DITOLAK => 'bg-red-100 text-red-800',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::DIAJUKAN => '⏳',
            self::DITERIMA => '✅',
            self::DITOLAK => '❌',
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match($this) {
            self::DIAJUKAN => in_array($newStatus, [self::DITERIMA, self::DITOLAK]),
            self::DITERIMA => false, // Terminal state
            self::DITOLAK => false,  // Terminal state
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::DITERIMA, self::DITOLAK]);
    }

    public static function initial(): self
    {
        return self::DIAJUKAN;
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
}

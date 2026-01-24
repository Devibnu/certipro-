<?php

namespace App\Enums;

enum PendaftaranStatus: string
{
    case DRAFT = 'draft';
    case DIAJUKAN = 'diajukan';
    case DIVERIFIKASI = 'diverifikasi';
    case DITOLAK = 'ditolak';
    case DIKUNCI = 'dikunci';          // Locked for asesmen
    case DIBATALKAN = 'dibatalkan';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::DIAJUKAN => 'Diajukan',
            self::DIVERIFIKASI => 'Diverifikasi',
            self::DITOLAK => 'Ditolak',
            self::DIKUNCI => 'Terkunci',
            self::DIBATALKAN => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DRAFT => 'secondary',
            self::DIAJUKAN => 'info',
            self::DIVERIFIKASI => 'primary',
            self::DITOLAK => 'danger',
            self::DIKUNCI => 'dark',
            self::DIBATALKAN => 'muted',
        };
    }

    public function badge(): string
    {
        return match($this) {
            self::DRAFT => 'bg-gray-100 text-gray-800',
            self::DIAJUKAN => 'bg-blue-100 text-blue-800',
            self::DIVERIFIKASI => 'bg-indigo-100 text-indigo-800',
            self::DITOLAK => 'bg-red-100 text-red-800',
            self::DIKUNCI => 'bg-gray-800 text-white',
            self::DIBATALKAN => 'bg-gray-400 text-gray-600',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::DRAFT => '📝',
            self::DIAJUKAN => '📤',
            self::DIVERIFIKASI => '✓',
            self::DITOLAK => '❌',
            self::DIKUNCI => '🔒',
            self::DIBATALKAN => '🚫',
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match($this) {
            self::DRAFT => in_array($newStatus, [self::DIAJUKAN, self::DIBATALKAN]),
            self::DIAJUKAN => in_array($newStatus, [self::DIVERIFIKASI, self::DITOLAK]),
            self::DIVERIFIKASI => $newStatus === self::DIKUNCI,
            self::DIKUNCI => false,      // Locked, cannot transition
            self::DITOLAK => false,      // Terminal
            self::DIBATALKAN => false,   // Terminal
        };
    }

    public function isLocked(): bool
    {
        return $this === self::DIKUNCI;
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::DITOLAK, self::DIBATALKAN, self::DIKUNCI]);
    }

    public function isEditable(): bool
    {
        return $this === self::DRAFT;
    }

    public function allowsSubmission(): bool
    {
        return $this === self::DRAFT;
    }

    public function allowsVerification(): bool
    {
        return $this === self::DIAJUKAN;
    }

    public function allowsLocking(): bool
    {
        return $this === self::DIVERIFIKASI;
    }

    public static function initial(): self
    {
        return self::DRAFT;
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
     * Get next possible statuses from current status
     */
    public function nextStatuses(): array
    {
        return array_filter(
            self::cases(),
            fn($status) => $this->canTransitionTo($status)
        );
    }
}

<?php

namespace App\Events;

use App\Models\PraPendaftaran;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * ============================================================================
 * Event: PraPendaftaranStatusChanged
 * ============================================================================
 * Fired when pra-pendaftaran status changes.
 * Triggers email notifications for: diterima, ditolak
 * 
 * Compliance: BNSP, ISO 17024
 * ============================================================================
 */
class PraPendaftaranStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The pra-pendaftaran model instance
     */
    public PraPendaftaran $praPendaftaran;

    /**
     * The old status before change
     */
    public ?string $oldStatus;

    /**
     * The new status after change
     */
    public string $newStatus;

    /**
     * User ID who triggered the change
     */
    public ?int $triggeredBy;

    /**
     * Timestamp when event occurred
     */
    public string $timestamp;

    /**
     * IP address of the user
     */
    public ?string $ipAddress;

    /**
     * Create a new event instance.
     */
    public function __construct(
        PraPendaftaran $praPendaftaran,
        ?string $oldStatus,
        string $newStatus,
        ?int $triggeredBy = null,
        ?string $ipAddress = null
    ) {
        $this->praPendaftaran = $praPendaftaran;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->triggeredBy = $triggeredBy ?? auth()->id();
        $this->timestamp = now()->toIso8601String();
        $this->ipAddress = $ipAddress ?? request()->ip();
    }

    /**
     * Get the model reference number
     */
    public function getReferenceNumber(): string
    {
        return $this->praPendaftaran->nomor_pra_pendaftaran 
            ?? 'PRA-' . str_pad($this->praPendaftaran->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Check if status actually changed
     */
    public function hasStatusChanged(): bool
    {
        return $this->oldStatus !== $this->newStatus;
    }

    /**
     * Get peserta email
     */
    public function getPesertaEmail(): ?string
    {
        return $this->praPendaftaran->email;
    }

    /**
     * Get peserta name
     */
    public function getPesertaName(): string
    {
        return $this->praPendaftaran->nama_lengkap ?? 'Peserta';
    }
}

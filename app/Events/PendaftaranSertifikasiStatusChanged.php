<?php

namespace App\Events;

use App\Models\PendaftaranSertifikasi;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * ============================================================================
 * Event: PendaftaranSertifikasiStatusChanged
 * ============================================================================
 * Fired when pendaftaran sertifikasi status changes.
 * Triggers email notifications for: diverifikasi, siap_asesmen
 * 
 * Compliance: BNSP, ISO 17024
 * ============================================================================
 */
class PendaftaranSertifikasiStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The pendaftaran sertifikasi model instance
     */
    public PendaftaranSertifikasi $pendaftaran;

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
        PendaftaranSertifikasi $pendaftaran,
        ?string $oldStatus,
        string $newStatus,
        ?int $triggeredBy = null,
        ?string $ipAddress = null
    ) {
        $this->pendaftaran = $pendaftaran;
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
        return $this->pendaftaran->nomor_pendaftaran 
            ?? 'REG-' . str_pad($this->pendaftaran->id, 6, '0', STR_PAD_LEFT);
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
        // Try multiple relations/fields
        if ($this->pendaftaran->relationLoaded('peserta') && $this->pendaftaran->peserta) {
            return $this->pendaftaran->peserta->email;
        }
        
        if ($this->pendaftaran->relationLoaded('praPendaftaran') && $this->pendaftaran->praPendaftaran) {
            return $this->pendaftaran->praPendaftaran->email;
        }

        return $this->pendaftaran->email ?? null;
    }

    /**
     * Get peserta name
     */
    public function getPesertaName(): string
    {
        if ($this->pendaftaran->relationLoaded('peserta') && $this->pendaftaran->peserta) {
            return $this->pendaftaran->peserta->nama_lengkap ?? $this->pendaftaran->peserta->name ?? 'Peserta';
        }
        
        if ($this->pendaftaran->relationLoaded('praPendaftaran') && $this->pendaftaran->praPendaftaran) {
            return $this->pendaftaran->praPendaftaran->nama_lengkap ?? 'Peserta';
        }

        return $this->pendaftaran->nama_lengkap ?? 'Peserta';
    }

    /**
     * Get skema sertifikasi name
     */
    public function getSkemaName(): string
    {
        if ($this->pendaftaran->relationLoaded('skemaSertifikasi') && $this->pendaftaran->skemaSertifikasi) {
            return $this->pendaftaran->skemaSertifikasi->nama_skema ?? 'Skema Sertifikasi';
        }

        return $this->pendaftaran->nama_skema ?? 'Skema Sertifikasi';
    }
}

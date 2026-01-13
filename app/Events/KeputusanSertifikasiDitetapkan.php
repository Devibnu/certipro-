<?php

namespace App\Events;

use App\Models\KeputusanSertifikasi;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * ============================================================================
 * Event: KeputusanSertifikasiDitetapkan
 * ============================================================================
 * Fired when keputusan sertifikasi is finalized.
 * Triggers email notifications for: kompeten_final, belum_kompeten_final
 * 
 * IMPORTANT: This is a FINAL decision - email should only be sent ONCE
 * 
 * Compliance: BNSP, ISO 17024
 * ============================================================================
 */
class KeputusanSertifikasiDitetapkan
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The keputusan sertifikasi model instance
     */
    public KeputusanSertifikasi $keputusan;

    /**
     * The old status before change
     */
    public ?string $oldStatus;

    /**
     * The new/final status
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
     * Flag to indicate if email was already sent for this decision
     */
    public bool $emailAlreadySent;

    /**
     * Create a new event instance.
     */
    public function __construct(
        KeputusanSertifikasi $keputusan,
        ?string $oldStatus,
        string $newStatus,
        ?int $triggeredBy = null,
        ?string $ipAddress = null
    ) {
        $this->keputusan = $keputusan;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->triggeredBy = $triggeredBy ?? auth()->id();
        $this->timestamp = now()->toIso8601String();
        $this->ipAddress = $ipAddress ?? request()->ip();
        
        // Check if email was already sent for final decision
        $this->emailAlreadySent = $this->checkEmailAlreadySent();
    }

    /**
     * Get the model reference number
     */
    public function getReferenceNumber(): string
    {
        return $this->keputusan->nomor_keputusan 
            ?? 'KEP-' . str_pad($this->keputusan->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Check if status actually changed
     */
    public function hasStatusChanged(): bool
    {
        return $this->oldStatus !== $this->newStatus;
    }

    /**
     * Check if this is a final decision
     */
    public function isFinalDecision(): bool
    {
        return in_array($this->newStatus, ['kompeten', 'belum_kompeten', 'kompeten_final', 'belum_kompeten_final']);
    }

    /**
     * Check if email was already sent for this final decision
     * Prevents duplicate emails for the same decision
     */
    protected function checkEmailAlreadySent(): bool
    {
        // Check audit log for existing email_sent record
        return \App\Models\AuditLog::where('auditable_type', KeputusanSertifikasi::class)
            ->where('auditable_id', $this->keputusan->id)
            ->where('event', 'email_sent')
            ->whereIn('new_values->template', ['kompeten', 'belum-kompeten'])
            ->exists();
    }

    /**
     * Get peserta email
     */
    public function getPesertaEmail(): ?string
    {
        // Navigate through relationships
        if ($this->keputusan->relationLoaded('pendaftaranSertifikasi') && $this->keputusan->pendaftaranSertifikasi) {
            $pendaftaran = $this->keputusan->pendaftaranSertifikasi;
            
            if ($pendaftaran->relationLoaded('peserta') && $pendaftaran->peserta) {
                return $pendaftaran->peserta->email;
            }
            
            if ($pendaftaran->relationLoaded('praPendaftaran') && $pendaftaran->praPendaftaran) {
                return $pendaftaran->praPendaftaran->email;
            }

            return $pendaftaran->email ?? null;
        }

        return null;
    }

    /**
     * Get peserta name
     */
    public function getPesertaName(): string
    {
        if ($this->keputusan->relationLoaded('pendaftaranSertifikasi') && $this->keputusan->pendaftaranSertifikasi) {
            $pendaftaran = $this->keputusan->pendaftaranSertifikasi;
            
            if ($pendaftaran->relationLoaded('peserta') && $pendaftaran->peserta) {
                return $pendaftaran->peserta->nama_lengkap ?? $pendaftaran->peserta->name ?? 'Peserta';
            }
            
            if ($pendaftaran->relationLoaded('praPendaftaran') && $pendaftaran->praPendaftaran) {
                return $pendaftaran->praPendaftaran->nama_lengkap ?? 'Peserta';
            }
        }

        return 'Peserta';
    }

    /**
     * Get sertifikat number (for kompeten)
     */
    public function getNomorSertifikat(): ?string
    {
        if ($this->keputusan->relationLoaded('sertifikat') && $this->keputusan->sertifikat) {
            return $this->keputusan->sertifikat->nomor_sertifikat;
        }

        return $this->keputusan->nomor_sertifikat ?? null;
    }

    /**
     * Get masa berlaku sertifikat
     */
    public function getMasaBerlaku(): ?string
    {
        if ($this->keputusan->relationLoaded('sertifikat') && $this->keputusan->sertifikat) {
            $sertifikat = $this->keputusan->sertifikat;
            $mulai = $sertifikat->tanggal_terbit ?? now();
            $selesai = $sertifikat->tanggal_expired ?? now()->addYears(3);
            
            return $mulai->format('d M Y') . ' - ' . $selesai->format('d M Y');
        }

        // Default 3 years validity
        return now()->format('d M Y') . ' - ' . now()->addYears(3)->format('d M Y');
    }
}

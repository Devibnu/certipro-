<?php

namespace App\Services;

use App\Events\PraPendaftaranStatusChanged;
use App\Events\PendaftaranSertifikasiStatusChanged;
use App\Events\KeputusanSertifikasiDitetapkan;
use App\Models\PraPendaftaran;
use App\Models\PendaftaranSertifikasi;
use App\Models\KeputusanSertifikasi;
use Illuminate\Support\Facades\Log;

/**
 * ============================================================================
 * Service: EmailEventService
 * ============================================================================
 * Centralized service for dispatching email notification events.
 * 
 * Benefits:
 * - Single point of control for all email events
 * - Logging and monitoring
 * - Easy to test and mock
 * - Consistent behavior across the application
 * 
 * Usage:
 * 
 * $emailEventService = app(EmailEventService::class);
 * $emailEventService->onPraPendaftaranStatusChange($praPendaftaran, 'pending', 'diterima');
 * 
 * Compliance: BNSP, ISO 17024
 * ============================================================================
 */
class EmailEventService
{
    /**
     * Dispatch event when pra-pendaftaran status changes
     * 
     * @param PraPendaftaran $praPendaftaran
     * @param string|null $oldStatus
     * @param string $newStatus
     * @return bool True if event was dispatched
     */
    public function onPraPendaftaranStatusChange(
        PraPendaftaran $praPendaftaran,
        ?string $oldStatus,
        string $newStatus
    ): bool {
        // Guard: Only dispatch if status actually changed
        if ($oldStatus === $newStatus) {
            Log::debug('EmailEventService: PraPendaftaran status unchanged, skipping', [
                'id' => $praPendaftaran->id,
                'status' => $newStatus,
            ]);
            return false;
        }

        Log::info('EmailEventService: Dispatching PraPendaftaranStatusChanged', [
            'id' => $praPendaftaran->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);

        event(new PraPendaftaranStatusChanged(
            praPendaftaran: $praPendaftaran,
            oldStatus: $oldStatus,
            newStatus: $newStatus,
            triggeredBy: auth()->id(),
            ipAddress: request()->ip()
        ));

        return true;
    }

    /**
     * Dispatch event when pendaftaran sertifikasi status changes
     * 
     * @param PendaftaranSertifikasi $pendaftaran
     * @param string|null $oldStatus
     * @param string $newStatus
     * @return bool True if event was dispatched
     */
    public function onPendaftaranStatusChange(
        PendaftaranSertifikasi $pendaftaran,
        ?string $oldStatus,
        string $newStatus
    ): bool {
        // Guard: Only dispatch if status actually changed
        if ($oldStatus === $newStatus) {
            Log::debug('EmailEventService: Pendaftaran status unchanged, skipping', [
                'id' => $pendaftaran->id,
                'status' => $newStatus,
            ]);
            return false;
        }

        Log::info('EmailEventService: Dispatching PendaftaranSertifikasiStatusChanged', [
            'id' => $pendaftaran->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);

        event(new PendaftaranSertifikasiStatusChanged(
            pendaftaran: $pendaftaran,
            oldStatus: $oldStatus,
            newStatus: $newStatus,
            triggeredBy: auth()->id(),
            ipAddress: request()->ip()
        ));

        return true;
    }

    /**
     * Dispatch event when keputusan sertifikasi is finalized
     * 
     * @param KeputusanSertifikasi $keputusan
     * @param string|null $oldStatus
     * @param string $newStatus
     * @return bool True if event was dispatched
     */
    public function onKeputusanFinal(
        KeputusanSertifikasi $keputusan,
        ?string $oldStatus,
        string $newStatus
    ): bool {
        // Guard: Only dispatch if status actually changed
        if ($oldStatus === $newStatus) {
            Log::debug('EmailEventService: Keputusan status unchanged, skipping', [
                'id' => $keputusan->id,
                'status' => $newStatus,
            ]);
            return false;
        }

        Log::info('EmailEventService: Dispatching KeputusanSertifikasiDitetapkan', [
            'id' => $keputusan->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);

        event(new KeputusanSertifikasiDitetapkan(
            keputusan: $keputusan,
            oldStatus: $oldStatus,
            newStatus: $newStatus,
            triggeredBy: auth()->id(),
            ipAddress: request()->ip()
        ));

        return true;
    }

    /**
     * Get list of email-triggering statuses for each model
     * 
     * @return array
     */
    public function getEmailTriggerStatuses(): array
    {
        return [
            'pra_pendaftaran' => [
                'diterima' => 'Pra-Pendaftaran Diterima',
                'ditolak' => 'Pra-Pendaftaran Ditolak',
            ],
            'pendaftaran_sertifikasi' => [
                'diverifikasi' => 'Pendaftaran Diverifikasi',
                'siap_asesmen' => 'Siap Asesmen',
            ],
            'keputusan_sertifikasi' => [
                'kompeten' => 'Hasil: KOMPETEN',
                'belum_kompeten' => 'Hasil: BELUM KOMPETEN',
            ],
        ];
    }
}

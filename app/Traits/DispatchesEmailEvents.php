<?php

namespace App\Traits;

use App\Events\PraPendaftaranStatusChanged;
use App\Events\PendaftaranSertifikasiStatusChanged;
use App\Events\KeputusanSertifikasiDitetapkan;
use App\Models\PraPendaftaran;
use App\Models\PendaftaranSertifikasi;
use App\Models\KeputusanSertifikasi;

/**
 * ============================================================================
 * Trait: DispatchesEmailEvents
 * ============================================================================
 * Provides convenient methods to dispatch email notification events
 * from controllers or services.
 * 
 * Usage in Controller:
 * 
 * use App\Traits\DispatchesEmailEvents;
 * 
 * class MyController extends Controller
 * {
 *     use DispatchesEmailEvents;
 * 
 *     public function updateStatus(Request $request, PraPendaftaran $praPendaftaran)
 *     {
 *         $oldStatus = $praPendaftaran->status;
 *         $praPendaftaran->update(['status' => $request->status]);
 *         
 *         $this->dispatchPraPendaftaranStatusChanged($praPendaftaran, $oldStatus, $request->status);
 *     }
 * }
 * 
 * Compliance: BNSP, ISO 17024
 * ============================================================================
 */
trait DispatchesEmailEvents
{
    /**
     * Dispatch PraPendaftaranStatusChanged event
     * 
     * @param PraPendaftaran $praPendaftaran
     * @param string|null $oldStatus
     * @param string $newStatus
     * @return void
     */
    protected function dispatchPraPendaftaranStatusChanged(
        PraPendaftaran $praPendaftaran,
        ?string $oldStatus,
        string $newStatus
    ): void {
        // Only dispatch if status actually changed
        if ($oldStatus === $newStatus) {
            return;
        }

        event(new PraPendaftaranStatusChanged(
            praPendaftaran: $praPendaftaran,
            oldStatus: $oldStatus,
            newStatus: $newStatus,
            triggeredBy: auth()->id(),
            ipAddress: request()->ip()
        ));
    }

    /**
     * Dispatch PendaftaranSertifikasiStatusChanged event
     * 
     * @param PendaftaranSertifikasi $pendaftaran
     * @param string|null $oldStatus
     * @param string $newStatus
     * @return void
     */
    protected function dispatchPendaftaranStatusChanged(
        PendaftaranSertifikasi $pendaftaran,
        ?string $oldStatus,
        string $newStatus
    ): void {
        // Only dispatch if status actually changed
        if ($oldStatus === $newStatus) {
            return;
        }

        event(new PendaftaranSertifikasiStatusChanged(
            pendaftaran: $pendaftaran,
            oldStatus: $oldStatus,
            newStatus: $newStatus,
            triggeredBy: auth()->id(),
            ipAddress: request()->ip()
        ));
    }

    /**
     * Dispatch KeputusanSertifikasiDitetapkan event
     * 
     * @param KeputusanSertifikasi $keputusan
     * @param string|null $oldStatus
     * @param string $newStatus
     * @return void
     */
    protected function dispatchKeputusanFinal(
        KeputusanSertifikasi $keputusan,
        ?string $oldStatus,
        string $newStatus
    ): void {
        // Only dispatch if status actually changed
        if ($oldStatus === $newStatus) {
            return;
        }

        event(new KeputusanSertifikasiDitetapkan(
            keputusan: $keputusan,
            oldStatus: $oldStatus,
            newStatus: $newStatus,
            triggeredBy: auth()->id(),
            ipAddress: request()->ip()
        ));
    }
}

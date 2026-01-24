<?php

namespace App\Listeners;

use App\Events\KeputusanDitetapkan;
use App\Events\PendaftaranDiajukan;
use App\Events\PraPendaftaranVerified;
use App\Events\SertifikatDiterbitkan;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class SendNotificationListener
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Handle PraPendaftaranVerified event
     */
    public function handlePraPendaftaranVerified(PraPendaftaranVerified $event): void
    {
        try {
            $data = $event->getNotificationData();
            $data['pra_pendaftaran_id'] = $event->praPendaftaran->id; // Untuk mapping Mailable

            $this->notificationService->send(
                eventType: $event->getEventType(),
                entityId: $event->praPendaftaran->id,
                data: $data,
                channels: ['email', 'whatsapp']
            );

        } catch (\Exception $e) {
            Log::error("Listener error: PraPendaftaranVerified", [
                'pra_pendaftaran_id' => $event->praPendaftaran->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle PendaftaranDiajukan event
     */
    public function handlePendaftaranDiajukan(PendaftaranDiajukan $event): void
    {
        try {
            $data = $event->getNotificationData();
            $data['pendaftaran_id'] = $event->pendaftaran->id;

            $this->notificationService->send(
                eventType: $event->getEventType(),
                entityId: $event->pendaftaran->id,
                data: $data,
                channels: ['email', 'whatsapp']
            );

        } catch (\Exception $e) {
            Log::error("Listener error: PendaftaranDiajukan", [
                'pendaftaran_id' => $event->pendaftaran->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle KeputusanDitetapkan event
     */
    public function handleKeputusanDitetapkan(KeputusanDitetapkan $event): void
    {
        try {
            $data = $event->getNotificationData();
            $data['keputusan_id'] = $event->keputusan->id;

            $this->notificationService->send(
                eventType: $event->getEventType(),
                entityId: $event->keputusan->id,
                data: $data,
                channels: ['email', 'whatsapp']
            );

        } catch (\Exception $e) {
            Log::error("Listener error: KeputusanDitetapkan", [
                'keputusan_id' => $event->keputusan->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle SertifikatDiterbitkan event
     */
    public function handleSertifikatDiterbitkan(SertifikatDiterbitkan $event): void
    {
        try {
            $data = $event->getNotificationData();
            $data['sertifikat_id'] = $event->sertifikat->id;

            $this->notificationService->send(
                eventType: $event->getEventType(),
                entityId: $event->sertifikat->id,
                data: $data,
                channels: ['email', 'whatsapp']
            );

        } catch (\Exception $e) {
            Log::error("Listener error: SertifikatDiterbitkan", [
                'sertifikat_id' => $event->sertifikat->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

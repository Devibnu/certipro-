<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

// CertiPro Events
use App\Events\PraPendaftaranStatusChanged;
use App\Events\PendaftaranSertifikasiStatusChanged;
use App\Events\KeputusanSertifikasiDitetapkan;

// NEW: Event-Driven Email Architecture
use App\Events\PraPendaftaranDiterimaEvent;
use App\Events\PraPendaftaranDitolakEvent;
use App\Events\KeputusanKompetenEvent;
use App\Events\KeputusanBelumKompetenEvent;
use App\Events\SertifikatTerbitEvent;

// CertiPro Listeners
use App\Listeners\SendEmailOnPraPendaftaranStatus;
use App\Listeners\SendEmailOnPendaftaranStatus;
use App\Listeners\SendEmailOnKeputusanFinal;

// NEW: Queue-based Email Listeners
use App\Listeners\SendPraPendaftaranDiterimaEmail;
use App\Listeners\SendPraPendaftaranDitolakEmail;
use App\Listeners\SendKeputusanKompetenEmail;
use App\Listeners\SendKeputusanBelumKompetenEmail;
use App\Listeners\SendSertifikatTerbitEmail;

/**
 * ============================================================================
 * EventServiceProvider
 * ============================================================================
 * Registers event-listener mappings for the application.
 * 
 * CertiPro Email Events:
 * - PraPendaftaranStatusChanged → SendEmailOnPraPendaftaranStatus
 * - PendaftaranSertifikasiStatusChanged → SendEmailOnPendaftaranStatus
 * - KeputusanSertifikasiDitetapkan → SendEmailOnKeputusanFinal
 * 
 * Compliance: BNSP, ISO 17024
 * ============================================================================
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // Laravel Default
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // =====================================================
        // CERTIPRO: Email Notification Events (LEGACY)
        // =====================================================
        
        // Pra-Pendaftaran Status Changes
        // Triggers: diterima, ditolak
        PraPendaftaranStatusChanged::class => [
            SendEmailOnPraPendaftaranStatus::class,
        ],

        // Pendaftaran Sertifikasi Status Changes
        // Triggers: diverifikasi, siap_asesmen
        PendaftaranSertifikasiStatusChanged::class => [
            SendEmailOnPendaftaranStatus::class,
        ],

        // Keputusan Sertifikasi Final
        // Triggers: kompeten, belum_kompeten
        // IMPORTANT: Emails are sent only ONCE (idempotent)
        KeputusanSertifikasiDitetapkan::class => [
            SendEmailOnKeputusanFinal::class,
        ],

        // =====================================================
        // CERTIPRO: Event-Driven Email Architecture (NEW)
        // Queue-based, Retry-safe, Idempotent
        // Compliance: EMAIL_STATUS_MATRIX.md
        // =====================================================

        // Pra-Pendaftaran Diterima
        // Trigger: PraPendaftaranAdminController::updateStatus() status='diterima'
        PraPendaftaranDiterimaEvent::class => [
            SendPraPendaftaranDiterimaEmail::class,
        ],

        // Pra-Pendaftaran Ditolak
        // Trigger: PraPendaftaranAdminController::updateStatus() status='ditolak'
        PraPendaftaranDitolakEvent::class => [
            SendPraPendaftaranDitolakEmail::class,
        ],

        // Keputusan Kompeten
        // Trigger: KeputusanSertifikasiController::simpan() keputusan='kompeten'
        KeputusanKompetenEvent::class => [
            SendKeputusanKompetenEmail::class,
        ],

        // Keputusan Belum Kompeten
        // Trigger: KeputusanSertifikasiController::simpan() keputusan='belum_kompeten'
        KeputusanBelumKompetenEvent::class => [
            SendKeputusanBelumKompetenEmail::class,
        ],

        // Sertifikat Terbit (RESERVED - belum diimplementasikan)
        // Trigger: SertifikatController::approve()
        SertifikatTerbitEvent::class => [
            SendSertifikatTerbitEmail::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

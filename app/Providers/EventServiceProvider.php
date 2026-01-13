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

// CertiPro Listeners
use App\Listeners\SendEmailOnPraPendaftaranStatus;
use App\Listeners\SendEmailOnPendaftaranStatus;
use App\Listeners\SendEmailOnKeputusanFinal;

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
        // CERTIPRO: Email Notification Events
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

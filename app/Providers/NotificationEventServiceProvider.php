<?php

namespace App\Providers;

use App\Events\KeputusanDitetapkan;
use App\Events\PendaftaranDiajukan;
use App\Events\PraPendaftaranVerified;
use App\Events\SertifikatDiterbitkan;
use App\Listeners\SendNotificationListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class NotificationEventServiceProvider extends ServiceProvider
{
    /**
     * Event listener mappings
     */
    protected $listen = [
        PraPendaftaranVerified::class => [
            [SendNotificationListener::class, 'handlePraPendaftaranVerified'],
        ],

        PendaftaranDiajukan::class => [
            [SendNotificationListener::class, 'handlePendaftaranDiajukan'],
        ],

        KeputusanDitetapkan::class => [
            [SendNotificationListener::class, 'handleKeputusanDitetapkan'],
        ],

        SertifikatDiterbitkan::class => [
            [SendNotificationListener::class, 'handleSertifikatDiterbitkan'],
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;

// Models
use App\Models\PraPendaftaran;
use App\Models\PendaftaranSertifikasi;
use App\Models\Asesmen;
use App\Models\KeputusanSertifikasi;
use App\Models\Sertifikat;

// Observers
use App\Observers\PraPendaftaranObserver;
use App\Observers\PendaftaranObserver;
use App\Observers\AsesmenObserver;
use App\Observers\KeputusanObserver;
use App\Observers\SertifikatObserver;

// Services
use App\Services\EmailSettingService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Register EmailSettingService as singleton
        $this->app->singleton(EmailSettingService::class, function ($app) {
            return new EmailSettingService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Tambahkan folder website sebagai lokasi view
        $customPath = base_path('website');
        View::addLocation($customPath);
        
        // Register View Composer untuk Header Info
        // Agar headerInfo tersedia di semua view yang menggunakan layout frontend
        View::composer('frontend.layouts.app', \App\View\Composers\HeaderInfoComposer::class);
        
        // =====================================================
        // REGISTER MODEL OBSERVERS - BNSP Audit Trail
        // =====================================================
        PraPendaftaran::observe(PraPendaftaranObserver::class);
        PendaftaranSertifikasi::observe(PendaftaranObserver::class);
        Asesmen::observe(AsesmenObserver::class);
        KeputusanSertifikasi::observe(KeputusanObserver::class);
        Sertifikat::observe(SertifikatObserver::class);
        
        // =====================================================
        // LOAD EMAIL SETTINGS FROM DATABASE
        // =====================================================
        $this->loadEmailSettings();
    }
    
    /**
     * Load email settings from database to Laravel config
     * 
     * @return void
     */
    protected function loadEmailSettings(): void
    {
        // Only load if database is available and table exists
        try {
            if (Schema::hasTable('email_settings')) {
                $emailService = $this->app->make(EmailSettingService::class);
                $emailService->loadToConfig();
            }
        } catch (\Exception $e) {
            // Silently fail - fallback to .env settings
            // This prevents errors during migrations or when DB is unavailable
        }
    }
}

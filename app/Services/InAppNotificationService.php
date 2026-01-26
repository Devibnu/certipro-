<?php

namespace App\Services;

use App\Models\InAppNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class InAppNotificationService
{
    /**
     * Send notification to specific user(s)
     */
    public function send(
        int|array $userIds,
        string $type,
        string $title,
        ?string $message = null,
        ?string $icon = null,
        string $iconColor = 'primary',
        ?string $routeName = null,
        ?array $routeParams = null
    ): void {
        $userIds = is_array($userIds) ? $userIds : [$userIds];
        
        foreach ($userIds as $userId) {
            try {
                InAppNotification::create([
                    'user_id' => $userId,
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'icon' => $icon ?? $this->getDefaultIcon($type),
                    'icon_color' => $iconColor,
                    'route_name' => $routeName,
                    'route_params' => $routeParams,
                ]);
                
                Log::info('In-app notification created', [
                    'user_id' => $userId,
                    'type' => $type,
                    'title' => $title,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create in-app notification', [
                    'user_id' => $userId,
                    'type' => $type,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
    
    /**
     * Send notification to all super admins
     */
    public function sendToSuperAdmins(
        string $type,
        string $title,
        ?string $message = null,
        ?string $icon = null,
        string $iconColor = 'primary',
        ?string $routeName = null,
        ?array $routeParams = null
    ): void {
        $superAdminIds = User::whereHas('userRole', function($q) {
            $q->where('name', 'super_admin');
        })->pluck('id')->toArray();
        
        if (empty($superAdminIds)) {
            Log::warning('No super admins found for notification');
            return;
        }
        
        $this->send(
            $superAdminIds,
            $type,
            $title,
            $message,
            $icon,
            $iconColor,
            $routeName,
            $routeParams
        );
    }
    
    /**
     * Send notification to all admins (super_admin + admin)
     */
    public function sendToAllAdmins(
        string $type,
        string $title,
        ?string $message = null,
        ?string $icon = null,
        string $iconColor = 'primary',
        ?string $routeName = null,
        ?array $routeParams = null
    ): void {
        $adminIds = User::whereHas('userRole', function($q) {
            $q->whereIn('name', ['super_admin', 'admin']);
        })->pluck('id')->toArray();
        
        if (empty($adminIds)) {
            Log::warning('No admins found for notification');
            return;
        }
        
        $this->send(
            $adminIds,
            $type,
            $title,
            $message,
            $icon,
            $iconColor,
            $routeName,
            $routeParams
        );
    }
    
    /**
     * Notify about new Pra-Pendaftaran
     */
    public function notifyPraPendaftaranBaru(int $praPendaftaranId, string $asesiName): void
    {
        // Decode HTML entities to prevent &quot; display issues
        $cleanName = html_entity_decode($asesiName, ENT_QUOTES, 'UTF-8');
        
        $this->sendToAllAdmins(
            InAppNotification::TYPE_PENDAFTARAN_BARU,
            'Pra-Pendaftaran Baru',
            "Asesi baru: {$cleanName}",
            'fas fa-clipboard-list',
            'warning',
            'adminui.pra-pendaftaran.show',
            ['pra_pendaftaran' => $praPendaftaranId]
        );
    }
    
    /**
     * Notify about assessment completion
     */
    public function notifyAsesmenSelesai(int $asesmenId, string $asesiName): void
    {
        $this->sendToAllAdmins(
            InAppNotification::TYPE_ASESMEN_SELESAI,
            'Asesmen Selesai',
            "Asesmen untuk {$asesiName} telah selesai",
            'fas fa-clipboard-check',
            'info',
            'adminui.asesmen.show',
            ['asesmen' => $asesmenId]
        );
    }
    
    /**
     * Notify about certificate issuance
     */
    public function notifySertifikatDiterbitkan(int $sertifikatId, string $asesiName): void
    {
        $this->sendToAllAdmins(
            InAppNotification::TYPE_SERTIFIKAT_DITERBITKAN,
            'Sertifikat Diterbitkan',
            "Sertifikat untuk {$asesiName} telah diterbitkan",
            'fas fa-award',
            'success',
            'adminui.sertifikat.show',
            ['sertifikat' => $sertifikatId]
        );
    }
    
    /**
     * Notify about competent decision
     */
    public function notifyKeputusanKompeten(int $keputusanId, string $asesiName): void
    {
        $this->sendToAllAdmins(
            InAppNotification::TYPE_KEPUTUSAN_KOMPETEN,
            'Keputusan: Kompeten',
            "Asesi {$asesiName} dinyatakan kompeten",
            'fas fa-check-circle',
            'success',
            'adminui.keputusan.show',
            ['keputusan' => $keputusanId]
        );
    }
    
    /**
     * Notify about not yet competent decision
     */
    public function notifyKeputusanBelumKompeten(int $keputusanId, string $asesiName): void
    {
        $this->sendToAllAdmins(
            InAppNotification::TYPE_KEPUTUSAN_BELUM_KOMPETEN,
            'Keputusan: Belum Kompeten',
            "Asesi {$asesiName} dinyatakan belum kompeten",
            'fas fa-exclamation-circle',
            'danger',
            'adminui.keputusan.show',
            ['keputusan' => $keputusanId]
        );
    }
    
    /**
     * Get default icon for notification type
     */
    protected function getDefaultIcon(string $type): string
    {
        return match($type) {
            InAppNotification::TYPE_PENDAFTARAN_BARU => 'fas fa-clipboard-list',
            InAppNotification::TYPE_ASESMEN_SELESAI => 'fas fa-clipboard-check',
            InAppNotification::TYPE_SERTIFIKAT_DITERBITKAN => 'fas fa-award',
            InAppNotification::TYPE_KEPUTUSAN_KOMPETEN => 'fas fa-check-circle',
            InAppNotification::TYPE_KEPUTUSAN_BELUM_KOMPETEN => 'fas fa-exclamation-circle',
            default => 'fas fa-bell',
        };
    }
}

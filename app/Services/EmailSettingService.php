<?php

namespace App\Services;

use App\Models\EmailSetting;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * EmailSettingService
 * 
 * Service untuk mengelola konfigurasi email sistem.
 * - Load settings dari database/cache ke Laravel config
 * - Simpan settings dengan enkripsi password
 * - Kirim email test
 * - Audit log semua perubahan
 * 
 * @package App\Services
 */
class EmailSettingService
{
    /**
     * Get current email settings.
     * Uses cache for performance.
     * 
     * @return EmailSetting|null
     */
    public function getSettings(): ?EmailSetting
    {
        return EmailSetting::getActive();
    }

    /**
     * Get settings without cache (for form display).
     * 
     * @return EmailSetting|null
     */
    public function getSettingsForEdit(): ?EmailSetting
    {
        return EmailSetting::getActiveWithoutCache();
    }

    /**
     * Check if email is properly configured.
     * 
     * @return bool
     */
    public function isConfigured(): bool
    {
        $settings = $this->getSettings();
        return $settings && $settings->isConfigured();
    }

    /**
     * Save email settings.
     * 
     * @param array $data Validated input data
     * @return array ['success' => bool, 'message' => string]
     */
    public function saveSettings(array $data): array
    {
        try {
            DB::beginTransaction();

            // Get current settings for comparison
            $settings = EmailSetting::getActiveWithoutCache() ?? new EmailSetting(['id' => 1]);
            $oldValues = $settings->exists ? $settings->toAuditArray() : [];

            // Prepare data for update
            $updateData = [
                'mail_driver' => $data['mail_driver'] ?? 'smtp',
                'mail_host' => $data['mail_host'] ?? null,
                'mail_port' => (int) ($data['mail_port'] ?? 587),
                'mail_encryption' => $data['mail_encryption'] ?? 'tls',
                'mail_username' => $data['mail_username'] ?? null,
                'mail_from_name' => $data['mail_from_name'] ?? 'CertiPro LSP',
                'mail_from_address' => $data['mail_from_address'] ?? 'noreply@certipro.id',
                'is_active' => true,
                'updated_by' => auth()->id(),
            ];

            // Only update password if provided
            if (!empty($data['mail_password'])) {
                $updateData['mail_password'] = $data['mail_password'];
            }

            // Set created_by if new record
            if (!$settings->exists) {
                $updateData['created_by'] = auth()->id();
            }

            // Update or create settings
            $settings = EmailSetting::updateOrCreate(
                ['id' => 1],
                $updateData
            );

            // Clear cache to reload fresh data
            EmailSetting::clearCache();

            // Log to audit
            $newValues = $settings->fresh()->toAuditArray();
            
            AuditLog::log(
                AuditLog::ACTION_UPDATE,
                AuditLog::MODULE_SETTINGS,
                'Pengaturan email sistem diperbarui',
                null, // No specific model
                $oldValues,
                $newValues,
                [
                    'event_type' => 'email_settings_updated',
                    'reference' => 'EMAIL_SETTINGS',
                    'changes_count' => $this->countChanges($oldValues, $newValues),
                ]
            );

            DB::commit();

            // Reload config with new settings
            $this->loadToConfig();

            return [
                'success' => true,
                'message' => 'Pengaturan email berhasil disimpan.',
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('EmailSettingService::saveSettings failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menyimpan pengaturan: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Load email settings to Laravel mail config at runtime.
     * Called on every request via ServiceProvider boot.
     * 
     * @return void
     */
    public function loadToConfig(): void
    {
        try {
            $settings = $this->getSettings();

            // Skip if no settings or not configured
            if (!$settings || !$settings->isConfigured()) {
                return; // Fallback to .env config
            }

            // Get config array with decrypted password
            $config = $settings->toConfigArray();

            // Set Laravel mail configuration
            Config::set('mail.default', $config['driver'] ?? 'smtp');

            Config::set('mail.mailers.smtp', [
                'transport' => 'smtp',
                'host' => $config['host'],
                'port' => $config['port'],
                'encryption' => $config['encryption'],
                'username' => $config['username'],
                'password' => $config['password'],
                'timeout' => null,
                'local_domain' => env('MAIL_EHLO_DOMAIN'),
            ]);

            Config::set('mail.from', [
                'address' => $config['from_address'],
                'name' => $config['from_name'],
            ]);

        } catch (\Exception $e) {
            Log::warning('EmailSettingService::loadToConfig failed', [
                'error' => $e->getMessage(),
            ]);
            // Silently fail - fallback to .env config
        }
    }

    /**
     * Send test email to verify configuration.
     * 
     * @param string $toEmail Recipient email address
     * @return array ['success' => bool, 'message' => string]
     */
    public function sendTestEmail(string $toEmail): array
    {
        try {
            // Reload config with latest settings
            $this->loadToConfig();

            $settings = $this->getSettings();
            
            if (!$settings || !$settings->isConfigured()) {
                return [
                    'success' => false,
                    'message' => 'Konfigurasi email belum lengkap. Silakan simpan konfigurasi terlebih dahulu.',
                ];
            }

            // Prepare email content
            $content = $this->buildTestEmailContent($settings);

            // Send email using raw text
            Mail::raw($content, function ($message) use ($toEmail, $settings) {
                $message->to($toEmail)
                    ->subject('🔧 Test Email - CertiPro LSP');
            });

            // Log to audit
            AuditLog::log(
                AuditLog::ACTION_VIEW, // Using view as test action
                AuditLog::MODULE_SETTINGS,
                "Email test berhasil dikirim ke {$toEmail}",
                null,
                null,
                null,
                [
                    'event_type' => 'email_test_sent',
                    'reference' => 'EMAIL_TEST',
                    'recipient' => $toEmail,
                    'smtp_host' => $settings->mail_host,
                    'smtp_port' => $settings->mail_port,
                    'status' => 'success',
                ]
            );

            return [
                'success' => true,
                'message' => "Email test berhasil dikirim ke {$toEmail}. Silakan periksa inbox Anda.",
            ];

        } catch (\Exception $e) {
            Log::error('EmailSettingService::sendTestEmail failed', [
                'to' => $toEmail,
                'error' => $e->getMessage(),
            ]);

            // Log failed attempt
            AuditLog::log(
                AuditLog::ACTION_VIEW,
                AuditLog::MODULE_SETTINGS,
                "Email test gagal dikirim ke {$toEmail}",
                null,
                null,
                null,
                [
                    'event_type' => 'email_test_failed',
                    'reference' => 'EMAIL_TEST',
                    'recipient' => $toEmail,
                    'error' => $e->getMessage(),
                    'status' => 'failed',
                ]
            );

            return [
                'success' => false,
                'message' => 'Gagal mengirim email: ' . $this->parseMailError($e->getMessage()),
            ];
        }
    }

    /**
     * Build test email content.
     * 
     * @param EmailSetting $settings
     * @return string
     */
    protected function buildTestEmailContent(EmailSetting $settings): string
    {
        $timestamp = now()->format('d/m/Y H:i:s');
        $user = auth()->user();
        
        return <<<TEXT
====================================
TEST EMAIL - CERTIPRO LSP
====================================

Ini adalah email test dari sistem CertiPro LSP.

Jika Anda menerima email ini, berarti konfigurasi SMTP sudah benar dan siap digunakan.

INFORMASI KONFIGURASI:
- SMTP Host: {$settings->mail_host}
- SMTP Port: {$settings->mail_port}
- Encryption: {$settings->mail_encryption}
- From Name: {$settings->mail_from_name}
- From Address: {$settings->mail_from_address}

INFORMASI PENGIRIM:
- User: {$user->name}
- Role: {$user->role}
- Waktu: {$timestamp}

------------------------------------
Email ini dikirim secara otomatis oleh sistem.
Harap jangan membalas email ini.

CertiPro LSP - Sistem Sertifikasi Profesi
====================================
TEXT;
    }

    /**
     * Parse mail error to user-friendly message.
     * 
     * @param string $error
     * @return string
     */
    protected function parseMailError(string $error): string
    {
        if (str_contains($error, 'Connection refused')) {
            return 'Koneksi ke server SMTP ditolak. Periksa host dan port.';
        }
        if (str_contains($error, 'Authentication')) {
            return 'Autentikasi gagal. Periksa username dan password SMTP.';
        }
        if (str_contains($error, 'timeout')) {
            return 'Koneksi timeout. Server SMTP tidak merespon.';
        }
        if (str_contains($error, 'SSL')) {
            return 'Error SSL/TLS. Periksa konfigurasi encryption.';
        }
        if (str_contains($error, 'certificate')) {
            return 'Sertifikat SSL tidak valid. Periksa konfigurasi server.';
        }
        
        return $error;
    }

    /**
     * Count changes between old and new values.
     * 
     * @param array $old
     * @param array $new
     * @return int
     */
    protected function countChanges(array $old, array $new): int
    {
        $count = 0;
        foreach ($new as $key => $value) {
            if (!isset($old[$key]) || $old[$key] !== $value) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Get available mail drivers.
     * 
     * @return array
     */
    public function getDriverOptions(): array
    {
        return EmailSetting::DRIVERS;
    }

    /**
     * Get available encryption options.
     * 
     * @return array
     */
    public function getEncryptionOptions(): array
    {
        return EmailSetting::ENCRYPTIONS;
    }
}

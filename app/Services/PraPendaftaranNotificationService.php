<?php

namespace App\Services;

use App\Mail\PraPendaftaran\PraPendaftaranDibuat;
use App\Mail\PraPendaftaran\PraPendaftaranDiterima;
use App\Mail\PraPendaftaran\PraPendaftaranDitolak;
use App\Models\AuditLog;
use App\Models\PraPendaftaran;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PraPendaftaranNotificationService
{
    /**
     * Send notification when pra-pendaftaran is created.
     */
    public static function sendCreatedNotification(PraPendaftaran $praPendaftaran): void
    {
        // DEBUGGING: Log function call
        Log::info('[PraPendaftaran] sendCreatedNotification CALLED', [
            'pra_id' => $praPendaftaran->id,
            'email' => $praPendaftaran->email,
            'nama' => $praPendaftaran->nama_lengkap,
        ]);
        
        // Send Email
        try {
            Mail::to($praPendaftaran->email)
                ->send(new PraPendaftaranDibuat($praPendaftaran));

            Log::info('[PraPendaftaran] Email SENT successfully', [
                'pra_id' => $praPendaftaran->id,
                'email' => $praPendaftaran->email,
            ]);

            self::logNotification($praPendaftaran, 'email', 'dibuat', true);
        } catch (\Exception $e) {
            Log::error('[PraPendaftaran] Failed to send created email', [
                'pra_pendaftaran_id' => $praPendaftaran->id,
                'email' => $praPendaftaran->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            self::logNotification($praPendaftaran, 'email', 'dibuat', false, $e->getMessage());
        }

        // Send WhatsApp (optional)
        if (config('services.whatsapp.enabled', false)) {
            self::sendWhatsAppNotification($praPendaftaran, 'dibuat');
        }
    }

    /**
     * Send notification when pra-pendaftaran is accepted.
     * CRITICAL FIX: Direct Mail::send() - NO EVENTS/QUEUE
     */
    public static function sendAcceptedNotification(PraPendaftaran $praPendaftaran): void
    {
        // ======================================================
        // GUARANTEED DELIVERY: Direct Mail::to()->send()
        // No Event, No Listener, No Queue - SYNC ONLY
        // ======================================================
        try {
            Mail::to($praPendaftaran->email)
                ->send(new PraPendaftaranDiterima($praPendaftaran));

            Log::info('[EMAIL SENT] Pra-Pendaftaran DITERIMA', [
                'pra_id' => $praPendaftaran->id,
                'email' => $praPendaftaran->email,
            ]);

            self::logNotification($praPendaftaran, 'email', 'diterima', true);
        } catch (\Throwable $e) {
            Log::error('[EMAIL FAILED] Pra-Pendaftaran DITERIMA', [
                'pra_id' => $praPendaftaran->id,
                'email' => $praPendaftaran->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            self::logNotification($praPendaftaran, 'email', 'diterima', false, $e->getMessage());
        }

        // Send WhatsApp (optional)
        if (config('services.whatsapp.enabled', false)) {
            self::sendWhatsAppNotification($praPendaftaran, 'diterima');
        }
    }

    /**
     * Send notification when pra-pendaftaran is rejected.
     * CRITICAL FIX: Direct Mail::send() - NO EVENTS/QUEUE
     */
    public static function sendRejectedNotification(PraPendaftaran $praPendaftaran): void
    {
        // ======================================================
        // GUARANTEED DELIVERY: Direct Mail::to()->send()
        // No Event, No Listener, No Queue - SYNC ONLY
        // ======================================================
        try {
            Mail::to($praPendaftaran->email)
                ->send(new PraPendaftaranDitolak($praPendaftaran));

            Log::info('[EMAIL SENT] Pra-Pendaftaran DITOLAK', [
                'pra_id' => $praPendaftaran->id,
                'email' => $praPendaftaran->email,
            ]);

            self::logNotification($praPendaftaran, 'email', 'ditolak', true);
        } catch (\Throwable $e) {
            Log::error('[EMAIL FAILED] Pra-Pendaftaran DITOLAK', [
                'pra_id' => $praPendaftaran->id,
                'email' => $praPendaftaran->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            self::logNotification($praPendaftaran, 'email', 'ditolak', false, $e->getMessage());
        }

        // Send WhatsApp (optional)
        if (config('services.whatsapp.enabled', false)) {
            self::sendWhatsAppNotification($praPendaftaran, 'ditolak');
        }
    }

    /**
     * Send WhatsApp notification (placeholder - implement with your WA provider).
     */
    protected static function sendWhatsAppNotification(PraPendaftaran $praPendaftaran, string $event): void
    {
        if (empty($praPendaftaran->no_hp)) {
            return;
        }

        $messages = [
            'dibuat' => self::getWhatsAppMessageDibuat($praPendaftaran),
            'diterima' => self::getWhatsAppMessageDiterima($praPendaftaran),
            'ditolak' => self::getWhatsAppMessageDitolak($praPendaftaran),
        ];

        $message = $messages[$event] ?? null;

        if (!$message) {
            return;
        }

        try {
            // Example: Using generic WhatsApp API
            // Replace with your actual WhatsApp provider (Fonnte, Wablas, etc.)
            $apiUrl = config('services.whatsapp.api_url');
            $apiKey = config('services.whatsapp.api_key');

            if ($apiUrl && $apiKey) {
                $response = Http::withHeaders([
                    'Authorization' => $apiKey,
                ])->post($apiUrl, [
                    'target' => self::formatPhoneNumber($praPendaftaran->no_hp),
                    'message' => $message,
                ]);

                if ($response->successful()) {
                    self::logNotification($praPendaftaran, 'whatsapp', $event, true);
                } else {
                    throw new \Exception($response->body());
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp notification', [
                'pra_pendaftaran_id' => $praPendaftaran->id,
                'phone' => $praPendaftaran->no_hp,
                'event' => $event,
                'error' => $e->getMessage(),
            ]);

            self::logNotification($praPendaftaran, 'whatsapp', $event, false, $e->getMessage());
        }
    }

    /**
     * Get WhatsApp message for created event.
     */
    protected static function getWhatsAppMessageDibuat(PraPendaftaran $praPendaftaran): string
    {
        $statusUrl = route('status-pra-pendaftaran.search', ['search' => $praPendaftaran->nomor_pra_pendaftaran]);

        return <<<MSG
*CertiPro LSP*
━━━━━━━━━━━━━━━━━

Yth. {$praPendaftaran->nama_lengkap},

Terima kasih telah melakukan pra-pendaftaran di CertiPro LSP.

📋 *Detail Pendaftaran:*
• Nomor: *{$praPendaftaran->nomor_pra_pendaftaran}*
• Status: ⏳ Menunggu Verifikasi

Proses verifikasi memerlukan waktu 1-3 hari kerja.

🔗 Cek Status: {$statusUrl}

━━━━━━━━━━━━━━━━━
_Pesan ini dikirim otomatis oleh sistem._
MSG;
    }

    /**
     * Get WhatsApp message for accepted event.
     */
    protected static function getWhatsAppMessageDiterima(PraPendaftaran $praPendaftaran): string
    {
        $statusUrl = route('status-pra-pendaftaran.search', ['search' => $praPendaftaran->nomor_pra_pendaftaran]);
        $daftarUrl = route('daftar');

        return <<<MSG
*CertiPro LSP*
━━━━━━━━━━━━━━━━━

🎉 *Selamat, {$praPendaftaran->nama_lengkap}!*

Pra-pendaftaran Anda telah *DITERIMA*.

📋 *Detail:*
• Nomor: *{$praPendaftaran->nomor_pra_pendaftaran}*
• Status: ✅ Diterima

Silakan lanjutkan ke tahap Pendaftaran Sertifikasi untuk memilih skema kompetensi.

🚀 Daftar Sertifikasi: {$daftarUrl}
📊 Cek Status: {$statusUrl}

━━━━━━━━━━━━━━━━━
_Pesan ini dikirim otomatis oleh sistem._
MSG;
    }

    /**
     * Get WhatsApp message for rejected event.
     */
    protected static function getWhatsAppMessageDitolak(PraPendaftaran $praPendaftaran): string
    {
        $statusUrl = route('status-pra-pendaftaran.search', ['search' => $praPendaftaran->nomor_pra_pendaftaran]);
        $alasan = $praPendaftaran->alasan_penolakan 
            ? "\n\n⚠️ *Alasan:*\n{$praPendaftaran->alasan_penolakan}" 
            : '';

        return <<<MSG
*CertiPro LSP*
━━━━━━━━━━━━━━━━━

Yth. {$praPendaftaran->nama_lengkap},

Mohon maaf, pra-pendaftaran Anda tidak dapat kami proses.

📋 *Detail:*
• Nomor: *{$praPendaftaran->nomor_pra_pendaftaran}*
• Status: ❌ Ditolak{$alasan}

Silakan hubungi kami jika ada pertanyaan.

📊 Detail: {$statusUrl}

━━━━━━━━━━━━━━━━━
_Pesan ini dikirim otomatis oleh sistem._
MSG;
    }

    /**
     * Format phone number to international format.
     */
    protected static function formatPhoneNumber(string $phone): string
    {
        // Remove non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Convert 08xx to 628xx
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        // Add 62 if not present
        if (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    /**
     * Log notification to audit log.
     */
    protected static function logNotification(
        PraPendaftaran $praPendaftaran,
        string $channel,
        string $event,
        bool $success,
        ?string $error = null
    ): void {
        $status = $success ? 'berhasil' : 'gagal';
        $channelLabel = strtoupper($channel);

        AuditLog::log(
            AuditLog::ACTION_VIEW, // Using VIEW as there's no NOTIFY action
            'pra_pendaftaran',
            "Notifikasi {$channelLabel} ({$event}) {$status} dikirim ke {$praPendaftaran->email}",
            $praPendaftaran,
            null,
            null,
            [
                'event' => "notification_{$event}",
                'channel' => $channel,
                'recipient' => $channel === 'email' ? $praPendaftaran->email : $praPendaftaran->no_hp,
                'success' => $success,
                'error' => $error,
            ]
        );
    }
}

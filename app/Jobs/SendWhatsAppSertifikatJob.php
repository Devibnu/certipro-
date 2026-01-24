<?php

namespace App\Jobs;

use App\Models\Sertifikat;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class SendWhatsAppSertifikatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The sertifikat instance.
     *
     * @var \App\Models\Sertifikat
     */
    public $sertifikat;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 60;

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [30, 120, 300]; // 30s, 2m, 5m
    }

    /**
     * Create a new job instance.
     */
    public function __construct(Sertifikat $sertifikat)
    {
        $this->sertifikat = $sertifikat;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $whatsappService): void
    {
        try {
            // Load relations jika belum
            if (!$this->sertifikat->relationLoaded('pendaftaran')) {
                $this->sertifikat->load([
                    'pendaftaran.user',
                    'pendaftaran.skemaSertifikasi',
                    'pendaftaran.keputusan'
                ]);
            }

            $pendaftaran = $this->sertifikat->pendaftaran;
            $user = $pendaftaran->user;
            $skema = $pendaftaran->skemaSertifikasi;

            // Validate phone number exists
            $phoneNumber = $user->no_telepon ?? $user->no_hp ?? null;
            if (empty($phoneNumber)) {
                Log::warning('WhatsApp notification skipped: User has no phone number', [
                    'sertifikat_id' => $this->sertifikat->id,
                    'user_id' => $user->id
                ]);
                return;
            }

            // Format phone number (remove spaces, dashes, add country code if needed)
            $phoneNumber = $this->formatPhoneNumber($phoneNumber);

            // Build verification URL
            $verificationUrl = route('public.sertifikat.verify', $this->sertifikat->uuid);

            // Build message
            $message = $this->buildWhatsAppMessage(
                $user->name,
                $skema->nama,
                $this->sertifikat->nomor_sertifikat,
                $verificationUrl
            );

            // Send via WhatsApp service
            $response = $whatsappService->sendMessage($phoneNumber, $message);

            // Log success
            Log::info('WhatsApp notification sent successfully', [
                'sertifikat_id' => $this->sertifikat->id,
                'sertifikat_nomor' => $this->sertifikat->nomor_sertifikat,
                'phone' => $phoneNumber,
                'attempt' => $this->attempts(),
                'response' => $response
            ]);

            // Update notification flag
            $this->sertifikat->update([
                'whatsapp_sent_at' => now()
            ]);

        } catch (Exception $e) {
            Log::error('Failed to send WhatsApp notification', [
                'sertifikat_id' => $this->sertifikat->id,
                'phone' => $phoneNumber ?? 'N/A',
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw untuk retry mechanism
            throw $e;
        }
    }

    /**
     * Build WhatsApp message content.
     */
    private function buildWhatsAppMessage(string $nama, string $skema, string $nomorSertifikat, string $verificationUrl): string
    {
        $lspName = config('certipro.lsp.nama', 'LSP CertiPro');
        
        return "✅ *SERTIFIKAT KOMPETENSI TERBIT*\n\n"
            . "Kepada Yth. *{$nama}*,\n\n"
            . "Selamat! Sertifikat kompetensi Anda telah resmi diterbitkan oleh {$lspName}.\n\n"
            . "📋 *Detail Sertifikat:*\n"
            . "• Nomor: {$nomorSertifikat}\n"
            . "• Skema: {$skema}\n"
            . "• Tanggal Terbit: " . $this->sertifikat->tanggal_terbit->format('d F Y') . "\n"
            . "• Berlaku Sampai: " . $this->sertifikat->tanggal_berlaku_sampai->format('d F Y') . "\n\n"
            . "🔗 *Verifikasi Sertifikat:*\n"
            . "{$verificationUrl}\n\n"
            . "📧 Sertifikat PDF juga telah dikirimkan ke email Anda.\n\n"
            . "Terima kasih atas partisipasi Anda.\n\n"
            . "Salam Profesional,\n"
            . "*{$lspName}*";
    }

    /**
     * Format phone number to international format.
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove spaces, dashes, parentheses
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);

        // Remove leading zeros
        $phone = ltrim($phone, '0');

        // Add country code if not present
        if (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('WhatsApp notification job permanently failed', [
            'sertifikat_id' => $this->sertifikat->id,
            'sertifikat_nomor' => $this->sertifikat->nomor_sertifikat,
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage()
        ]);

        // Update flag untuk manual retry
        $this->sertifikat->update([
            'whatsapp_failed_at' => now(),
            'whatsapp_error' => $exception->getMessage()
        ]);
    }
}

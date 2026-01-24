<?php

namespace App\Services;

use App\Mail\PraPendaftaran\PraPendaftaranDiterima;
use App\Mail\PraPendaftaran\PraPendaftaranDitolak;
use App\Models\NotificationLog;
use App\Models\PraPendaftaran;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    /**
     * Send email berdasarkan event type
     */
    public function send(NotificationLog $notificationLog): void
    {
        $eventType = $notificationLog->event_type;
        $data = $notificationLog->payload;
        $recipient = $notificationLog->recipient;

        try {
            $mailable = $this->getMailable($eventType, $data);

            if (!$mailable) {
                throw new \Exception("Mailable tidak ditemukan untuk event: {$eventType}");
            }

            Mail::to($recipient)->send($mailable);

            Log::info("Email terkirim", [
                'notification_log_id' => $notificationLog->id,
                'event_type' => $eventType,
                'recipient' => $recipient,
            ]);

        } catch (\Exception $e) {
            Log::error("Gagal kirim email", [
                'notification_log_id' => $notificationLog->id,
                'event_type' => $eventType,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get mailable instance berdasarkan event type
     */
    protected function getMailable(string $eventType, array $data): mixed
    {
        return match ($eventType) {
            'pra_pendaftaran_diterima' => new PraPendaftaranDiterima(
                PraPendaftaran::find($data['pra_pendaftaran_id'] ?? null)
            ),

            'pra_pendaftaran_ditolak' => new PraPendaftaranDitolak(
                PraPendaftaran::find($data['pra_pendaftaran_id'] ?? null)
            ),

            'pendaftaran_diajukan' => $this->buildGenericMailable(
                'Pendaftaran Sertifikasi Diajukan',
                'emails.pendaftaran.diajukan',
                $data
            ),

            'keputusan_kompeten' => $this->buildGenericMailable(
                'Selamat! Anda Dinyatakan KOMPETEN',
                'emails.keputusan.kompeten',
                $data
            ),

            'keputusan_belum_kompeten' => $this->buildGenericMailable(
                'Pemberitahuan Hasil Asesmen',
                'emails.keputusan.belum-kompeten',
                $data
            ),

            'sertifikat_diterbitkan' => $this->buildGenericMailable(
                'Sertifikat Kompetensi Telah Diterbitkan',
                'emails.sertifikat.diterbitkan',
                $data
            ),

            default => null,
        };
    }

    /**
     * Build generic mailable untuk event yang belum punya Mail class
     */
    protected function buildGenericMailable(string $subject, string $view, array $data): mixed
    {
        return new class($subject, $view, $data) extends \Illuminate\Mail\Mailable {
            public function __construct(
                protected string $emailSubject,
                protected string $emailView,
                protected array $emailData
            ) {}

            public function build()
            {
                return $this->subject($this->emailSubject)
                    ->view($this->emailView)
                    ->with($this->emailData);
            }
        };
    }
}

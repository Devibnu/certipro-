<?php

namespace App\Jobs;

use App\Mail\SertifikatTerbitMail;
use App\Models\Sertifikat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Exception;

class SendEmailSertifikatJob implements ShouldQueue
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
    public $tries = 5;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 300, 600, 1800, 3600]; // 1m, 5m, 10m, 30m, 1h
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
    public function handle(): void
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

            // Validate email exists
            if (empty($user->email)) {
                Log::warning('Email notification skipped: User has no email', [
                    'sertifikat_id' => $this->sertifikat->id,
                    'user_id' => $user->id
                ]);
                return;
            }

            // Get PDF file path
            $pdfPath = null;
            if ($this->sertifikat->file_pdf && Storage::disk('public')->exists($this->sertifikat->file_pdf)) {
                $pdfPath = Storage::disk('public')->path($this->sertifikat->file_pdf);
            }

            // Send email
            Mail::to($user->email)
                ->send(new SertifikatTerbitMail($this->sertifikat, $pendaftaran, $pdfPath));

            // Log success
            Log::info('Email sertifikat sent successfully', [
                'sertifikat_id' => $this->sertifikat->id,
                'sertifikat_nomor' => $this->sertifikat->nomor_sertifikat,
                'email' => $user->email,
                'attempt' => $this->attempts()
            ]);

            // Update notification flag (optional)
            $this->sertifikat->update([
                'email_sent_at' => now()
            ]);

        } catch (Exception $e) {
            Log::error('Failed to send email sertifikat', [
                'sertifikat_id' => $this->sertifikat->id,
                'email' => $user->email ?? 'N/A',
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw untuk retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('Email sertifikat job permanently failed', [
            'sertifikat_id' => $this->sertifikat->id,
            'sertifikat_nomor' => $this->sertifikat->nomor_sertifikat,
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage()
        ]);

        // Update flag untuk manual retry
        $this->sertifikat->update([
            'email_failed_at' => now(),
            'email_error' => $exception->getMessage()
        ]);

        // Optional: Send alert ke admin
        // Mail::to(config('mail.admin_email'))->send(new NotificationFailedAlert($this->sertifikat, 'email'));
    }
}

<?php

namespace App\Services;

use App\Mail\PraPendaftaranDiterimaMail;
use App\Mail\PraPendaftaranDitolakMail;
use App\Mail\SertifikasiDiverifikasiMail;
use App\Mail\KompetenMail;
use App\Mail\BelumKompetenMail;
use App\Models\PraPendaftaran;
use App\Models\PendaftaranSertifikasi;
use App\Models\KeputusanSertifikasi;
use App\Models\AuditLog;
use App\Models\EmailSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;

/**
 * ============================================================================
 * Service: EmailNotificationService
 * ============================================================================
 * Centralized service for email preview and resend functionality.
 * Used by Admin Panel for manual email operations.
 * 
 * Features:
 * - Preview email HTML without sending
 * - Resend email with audit logging
 * - Check resend limits
 * - Track email history
 * 
 * Compliance: BNSP, ISO 17024
 * ============================================================================
 */
class EmailNotificationService
{
    /**
     * Maximum resend attempts per record
     */
    const MAX_RESEND_ATTEMPTS = 3;

    /**
     * Supported email types and their configurations
     */
    protected array $emailTypes = [
        'pra-diterima' => [
            'model' => PraPendaftaran::class,
            'status' => ['diterima', 'accepted', 'approved'],
            'mail_class' => PraPendaftaranDiterimaMail::class,
            'template' => 'emails.pra-diterima',
            'subject' => '[LSP] Pra-Pendaftaran Diterima',
        ],
        'pra-ditolak' => [
            'model' => PraPendaftaran::class,
            'status' => ['ditolak', 'rejected', 'declined'],
            'mail_class' => PraPendaftaranDitolakMail::class,
            'template' => 'emails.pra-ditolak',
            'subject' => '[LSP] Pra-Pendaftaran Ditolak',
        ],
        'sertifikasi-diverifikasi' => [
            'model' => PendaftaranSertifikasi::class,
            'status' => ['diverifikasi', 'verified', 'siap_asesmen'],
            'mail_class' => SertifikasiDiverifikasiMail::class,
            'template' => 'emails.sertifikasi-diverifikasi',
            'subject' => '[LSP] Pendaftaran Sertifikasi Diverifikasi',
        ],
        'kompeten' => [
            'model' => KeputusanSertifikasi::class,
            'status' => ['kompeten', 'kompeten_final', 'competent'],
            'mail_class' => KompetenMail::class,
            'template' => 'emails.kompeten',
            'subject' => '[LSP] Hasil Sertifikasi: KOMPETEN',
        ],
        'belum-kompeten' => [
            'model' => KeputusanSertifikasi::class,
            'status' => ['belum_kompeten', 'belum_kompeten_final', 'not_competent'],
            'mail_class' => BelumKompetenMail::class,
            'template' => 'emails.belum-kompeten',
            'subject' => '[LSP] Hasil Sertifikasi: BELUM KOMPETEN',
        ],
    ];

    /**
     * Get all supported email types
     */
    public function getSupportedTypes(): array
    {
        return array_keys($this->emailTypes);
    }

    /**
     * Get email type configuration
     */
    public function getTypeConfig(string $type): ?array
    {
        return $this->emailTypes[$type] ?? null;
    }

    /**
     * Get the appropriate email type for a model based on its status
     */
    public function getEmailTypeForModel($model): ?string
    {
        $modelClass = get_class($model);
        $status = strtolower($model->status ?? '');

        foreach ($this->emailTypes as $type => $config) {
            if ($config['model'] === $modelClass && in_array($status, $config['status'])) {
                return $type;
            }
        }

        return null;
    }

    /**
     * Preview email HTML without sending
     * 
     * @param string $type Email type (pra-diterima, pra-ditolak, etc.)
     * @param int $id Model ID
     * @return array ['success' => bool, 'html' => string, 'subject' => string, 'error' => string]
     */
    public function preview(string $type, int $id): array
    {
        try {
            $config = $this->getTypeConfig($type);
            if (!$config) {
                return [
                    'success' => false,
                    'error' => 'Tipe email tidak valid.',
                ];
            }

            // Find the model
            $model = $config['model']::find($id);
            if (!$model) {
                return [
                    'success' => false,
                    'error' => 'Data tidak ditemukan.',
                ];
            }

            // Build email data
            $emailData = $this->buildEmailData($type, $model);
            
            // Render the template
            $html = View::make($config['template'], $emailData)->render();

            // Log preview action
            $this->logAction('email_previewed', $model, $type, $emailData['email_to'] ?? null);

            return [
                'success' => true,
                'html' => $html,
                'subject' => $this->buildSubject($type, $emailData),
                'email_to' => $emailData['email_to'] ?? 'N/A',
                'reference' => $emailData['reference'] ?? 'N/A',
                'last_sent' => $this->getLastSentTime($model, $type),
                'resend_count' => $this->getResendCount($model, $type),
            ];
        } catch (\Exception $e) {
            Log::error('EmailNotificationService: Preview failed', [
                'type' => $type,
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Gagal memuat preview: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Resend email to peserta
     * 
     * @param string $type Email type
     * @param int $id Model ID
     * @param string|null $reason Optional reason for resend
     * @return array ['success' => bool, 'message' => string]
     */
    public function resend(string $type, int $id, ?string $reason = null): array
    {
        try {
            $config = $this->getTypeConfig($type);
            if (!$config) {
                return [
                    'success' => false,
                    'message' => 'Tipe email tidak valid.',
                ];
            }

            // Find the model
            $model = $config['model']::find($id);
            if (!$model) {
                return [
                    'success' => false,
                    'message' => 'Data tidak ditemukan.',
                ];
            }

            // Check resend limit
            $resendCount = $this->getResendCount($model, $type);
            if ($resendCount >= self::MAX_RESEND_ATTEMPTS) {
                return [
                    'success' => false,
                    'message' => 'Batas pengiriman ulang telah tercapai (maksimal ' . self::MAX_RESEND_ATTEMPTS . 'x).',
                ];
            }

            // Check if email is configured
            if (!$this->isEmailConfigured()) {
                return [
                    'success' => false,
                    'message' => 'Pengaturan email belum dikonfigurasi.',
                ];
            }

            // Build email data
            $emailData = $this->buildEmailData($type, $model);
            
            // Check if email address exists
            if (empty($emailData['email_to'])) {
                return [
                    'success' => false,
                    'message' => 'Email peserta tidak ditemukan.',
                ];
            }

            // Create and send the mail
            $mail = $this->createMailInstance($type, $emailData);
            Mail::to($emailData['email_to'])->send($mail);

            // Log resend action
            $this->logAction('email_resent', $model, $type, $emailData['email_to'], $reason);

            Log::info('EmailNotificationService: Email resent successfully', [
                'type' => $type,
                'id' => $id,
                'email_to' => $emailData['email_to'],
                'reason' => $reason,
            ]);

            return [
                'success' => true,
                'message' => 'Email berhasil dikirim ulang ke ' . $emailData['email_to'],
            ];
        } catch (\Exception $e) {
            Log::error('EmailNotificationService: Resend failed', [
                'type' => $type,
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            // Log failed attempt
            $this->logAction('email_resend_failed', $model ?? null, $type, null, $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengirim email: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Build email data based on type and model
     */
    protected function buildEmailData(string $type, $model): array
    {
        $data = [
            'reference' => $this->getReferenceNumber($model),
        ];

        switch ($type) {
            case 'pra-diterima':
                $data['nama'] = $model->nama_lengkap ?? 'Peserta';
                $data['nomor_pra_pendaftaran'] = $model->nomor_pra_pendaftaran ?? 'PRA-' . str_pad($model->id, 6, '0', STR_PAD_LEFT);
                $data['tanggal'] = $model->created_at?->format('d M Y') ?? now()->format('d M Y');
                $data['link_status'] = config('app.url') . '/portal/status/' . $model->id;
                $data['email_to'] = $model->email;
                break;

            case 'pra-ditolak':
                $data['nama'] = $model->nama_lengkap ?? 'Peserta';
                $data['nomor_pra_pendaftaran'] = $model->nomor_pra_pendaftaran ?? 'PRA-' . str_pad($model->id, 6, '0', STR_PAD_LEFT);
                $data['alasan_penolakan'] = $model->alasan_penolakan ?? $model->catatan_penolakan ?? $model->keterangan ?? 'Dokumen tidak memenuhi persyaratan.';
                $data['email_to'] = $model->email;
                break;

            case 'sertifikasi-diverifikasi':
                $model->load(['peserta', 'praPendaftaran', 'skemaSertifikasi']);
                $data['nama'] = $this->getPesertaName($model);
                $data['nomor_pendaftaran'] = $model->nomor_pendaftaran ?? 'REG-' . str_pad($model->id, 6, '0', STR_PAD_LEFT);
                $data['skema'] = $model->skemaSertifikasi?->nama_skema ?? $model->nama_skema ?? 'Skema Sertifikasi';
                $data['email_to'] = $this->getPesertaEmail($model);
                break;

            case 'kompeten':
                $model->load(['pendaftaranSertifikasi.peserta', 'pendaftaranSertifikasi.praPendaftaran', 'sertifikat']);
                $pendaftaran = $model->pendaftaranSertifikasi;
                $data['nama'] = $this->getPesertaNameFromKeputusan($model);
                $data['nomor_sertifikat'] = $model->sertifikat?->nomor_sertifikat ?? $model->nomor_sertifikat ?? 'CERT-' . str_pad($model->id, 6, '0', STR_PAD_LEFT);
                $data['masa_berlaku'] = $this->getMasaBerlaku($model);
                $data['link_sertifikat'] = config('app.url') . '/portal/sertifikat/' . $model->id;
                $data['email_to'] = $this->getPesertaEmailFromKeputusan($model);
                break;

            case 'belum-kompeten':
                $model->load(['pendaftaranSertifikasi.peserta', 'pendaftaranSertifikasi.praPendaftaran']);
                $pendaftaran = $model->pendaftaranSertifikasi;
                $data['nama'] = $this->getPesertaNameFromKeputusan($model);
                $data['nomor_pendaftaran'] = $pendaftaran?->nomor_pendaftaran ?? 'REG-' . str_pad($pendaftaran?->id ?? $model->id, 6, '0', STR_PAD_LEFT);
                $data['email_to'] = $this->getPesertaEmailFromKeputusan($model);
                break;
        }

        return $data;
    }

    /**
     * Build email subject with variables
     */
    protected function buildSubject(string $type, array $data): string
    {
        $config = $this->getTypeConfig($type);
        $subject = $config['subject'] ?? 'Notifikasi LSP';

        // Replace placeholders
        if (isset($data['nomor_pra_pendaftaran'])) {
            $subject = str_replace('{nomor}', $data['nomor_pra_pendaftaran'], $subject);
            $subject .= ' – ' . $data['nomor_pra_pendaftaran'];
        }

        return $subject;
    }

    /**
     * Create mail instance based on type
     */
    protected function createMailInstance(string $type, array $data)
    {
        return match ($type) {
            'pra-diterima' => new PraPendaftaranDiterimaMail(
                $data['nama'],
                $data['nomor_pra_pendaftaran'],
                $data['tanggal'],
                $data['link_status']
            ),
            'pra-ditolak' => new PraPendaftaranDitolakMail(
                $data['nama'],
                $data['nomor_pra_pendaftaran'],
                $data['alasan_penolakan']
            ),
            'sertifikasi-diverifikasi' => new SertifikasiDiverifikasiMail(
                $data['nama'],
                $data['nomor_pendaftaran'],
                $data['skema']
            ),
            'kompeten' => new KompetenMail(
                $data['nama'],
                $data['nomor_sertifikat'],
                $data['masa_berlaku'],
                $data['link_sertifikat']
            ),
            'belum-kompeten' => new BelumKompetenMail(
                $data['nama'],
                $data['nomor_pendaftaran']
            ),
            default => throw new \InvalidArgumentException("Unknown email type: {$type}"),
        };
    }

    /**
     * Get reference number from model
     */
    protected function getReferenceNumber($model): string
    {
        if ($model instanceof PraPendaftaran) {
            return $model->nomor_pra_pendaftaran ?? 'PRA-' . str_pad($model->id, 6, '0', STR_PAD_LEFT);
        }
        if ($model instanceof PendaftaranSertifikasi) {
            return $model->nomor_pendaftaran ?? 'REG-' . str_pad($model->id, 6, '0', STR_PAD_LEFT);
        }
        if ($model instanceof KeputusanSertifikasi) {
            return $model->nomor_keputusan ?? 'KEP-' . str_pad($model->id, 6, '0', STR_PAD_LEFT);
        }
        return 'REF-' . $model->id;
    }

    /**
     * Get peserta name from PendaftaranSertifikasi
     */
    protected function getPesertaName($pendaftaran): string
    {
        if ($pendaftaran->peserta) {
            return $pendaftaran->peserta->nama_lengkap ?? $pendaftaran->peserta->name ?? 'Peserta';
        }
        if ($pendaftaran->praPendaftaran) {
            return $pendaftaran->praPendaftaran->nama_lengkap ?? 'Peserta';
        }
        return $pendaftaran->nama_lengkap ?? 'Peserta';
    }

    /**
     * Get peserta email from PendaftaranSertifikasi
     */
    protected function getPesertaEmail($pendaftaran): ?string
    {
        if ($pendaftaran->peserta) {
            return $pendaftaran->peserta->email;
        }
        if ($pendaftaran->praPendaftaran) {
            return $pendaftaran->praPendaftaran->email;
        }
        return $pendaftaran->email ?? null;
    }

    /**
     * Get peserta name from KeputusanSertifikasi
     */
    protected function getPesertaNameFromKeputusan($keputusan): string
    {
        $pendaftaran = $keputusan->pendaftaranSertifikasi;
        if ($pendaftaran) {
            return $this->getPesertaName($pendaftaran);
        }
        return 'Peserta';
    }

    /**
     * Get peserta email from KeputusanSertifikasi
     */
    protected function getPesertaEmailFromKeputusan($keputusan): ?string
    {
        $pendaftaran = $keputusan->pendaftaranSertifikasi;
        if ($pendaftaran) {
            return $this->getPesertaEmail($pendaftaran);
        }
        return null;
    }

    /**
     * Get masa berlaku sertifikat
     */
    protected function getMasaBerlaku($keputusan): string
    {
        if ($keputusan->sertifikat) {
            $mulai = $keputusan->sertifikat->tanggal_terbit ?? now();
            $selesai = $keputusan->sertifikat->tanggal_expired ?? now()->addYears(3);
            return $mulai->format('d M Y') . ' - ' . $selesai->format('d M Y');
        }
        return now()->format('d M Y') . ' - ' . now()->addYears(3)->format('d M Y');
    }

    /**
     * Check if email is configured
     */
    protected function isEmailConfigured(): bool
    {
        try {
            $emailSetting = EmailSetting::getActive();
            return $emailSetting && $emailSetting->isConfigured();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get last sent time for this email type
     */
    public function getLastSentTime($model, string $type): ?string
    {
        $lastSent = AuditLog::where('auditable_type', get_class($model))
            ->where('auditable_id', $model->id)
            ->whereIn('event', ['email_sent', 'email_resent'])
            ->where('new_values->template', $type)
            ->orderBy('created_at', 'desc')
            ->first();

        return $lastSent?->created_at?->setTimezone('Asia/Jakarta')->format('d M Y, H:i') ?? null;
    }

    /**
     * Get resend count for this email type
     */
    public function getResendCount($model, string $type): int
    {
        return AuditLog::where('auditable_type', get_class($model))
            ->where('auditable_id', $model->id)
            ->where('event', 'email_resent')
            ->where('new_values->template', $type)
            ->count();
    }

    /**
     * Check if email has ever been sent
     */
    public function hasBeenSent($model, string $type): bool
    {
        return AuditLog::where('auditable_type', get_class($model))
            ->where('auditable_id', $model->id)
            ->whereIn('event', ['email_sent', 'email_resent'])
            ->where('new_values->template', $type)
            ->exists();
    }

    /**
     * Log action to audit log
     */
    protected function logAction(string $event, $model, string $type, ?string $emailTo, ?string $notes = null): void
    {
        try {
            AuditLog::create([
                'user_id' => auth()->id(),
                'event' => $event,
                'auditable_type' => $model ? get_class($model) : null,
                'auditable_id' => $model?->id,
                'old_values' => null,
                'new_values' => [
                    'template' => $type,
                    'email_to' => $emailTo,
                    'reference_number' => $model ? $this->getReferenceNumber($model) : null,
                    'notes' => $notes,
                ],
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('EmailNotificationService: Failed to log action', [
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get email history for a model
     */
    public function getEmailHistory($model): array
    {
        $history = AuditLog::where('auditable_type', get_class($model))
            ->where('auditable_id', $model->id)
            ->whereIn('event', ['email_sent', 'email_resent', 'email_failed', 'email_previewed'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $history->map(function ($log) {
            return [
                'event' => $log->event,
                'template' => $log->new_values['template'] ?? 'N/A',
                'email_to' => $log->new_values['email_to'] ?? 'N/A',
                'timestamp' => $log->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i:s'),
                'user' => $log->user?->name ?? 'System',
                'notes' => $log->new_values['notes'] ?? null,
            ];
        })->toArray();
    }
}

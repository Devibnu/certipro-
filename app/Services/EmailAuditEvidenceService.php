<?php

namespace App\Services;

use App\Models\PraPendaftaran;
use App\Models\PendaftaranSertifikasi;
use App\Models\KeputusanSertifikasi;
use App\Models\Sertifikat;
use App\Models\AuditLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * ============================================================================
 * Service: EmailAuditEvidenceService
 * ============================================================================
 * Generates PDF audit evidence for email notifications.
 * Used for BNSP/ISO 17024 compliance and audit purposes.
 * 
 * Features:
 * - Generate comprehensive audit evidence PDF
 * - Include email history from audit logs
 * - Document integrity with SHA-256 hash
 * - Forensic-grade communication records
 * 
 * Compliance: BNSP, ISO 17024
 * ============================================================================
 */
class EmailAuditEvidenceService
{
    /**
     * Supported reference types and their configurations
     */
    protected array $referenceTypes = [
        'pra-pendaftaran' => [
            'model' => PraPendaftaran::class,
            'email_types' => ['pra-diterima', 'pra-ditolak'],
            'label' => 'Pra-Pendaftaran',
            'reference_field' => 'nomor_pra_pendaftaran',
            'reference_prefix' => 'PRA',
        ],
        'pendaftaran' => [
            'model' => PendaftaranSertifikasi::class,
            'email_types' => ['sertifikasi-diverifikasi'],
            'label' => 'Pendaftaran Sertifikasi',
            'reference_field' => 'nomor_pendaftaran',
            'reference_prefix' => 'REG',
        ],
        'keputusan' => [
            'model' => KeputusanSertifikasi::class,
            'email_types' => ['kompeten', 'belum-kompeten'],
            'label' => 'Keputusan Sertifikasi',
            'reference_field' => 'nomor_keputusan',
            'reference_prefix' => 'KEP',
        ],
        'sertifikat' => [
            'model' => Sertifikat::class,
            'email_types' => ['kompeten'],
            'label' => 'Sertifikat',
            'reference_field' => 'nomor_sertifikat',
            'reference_prefix' => 'CERT',
        ],
    ];

    /**
     * Get supported reference types
     */
    public function getSupportedTypes(): array
    {
        return array_keys($this->referenceTypes);
    }

    /**
     * Generate audit evidence PDF
     * 
     * @param string $referenceType (pra-pendaftaran, pendaftaran, keputusan, sertifikat)
     * @param int $referenceId
     * @return array ['success' => bool, 'pdf' => Pdf|null, 'filename' => string, 'error' => string]
     */
    public function generatePdf(string $referenceType, int $referenceId): array
    {
        try {
            // Validate reference type
            if (!isset($this->referenceTypes[$referenceType])) {
                return [
                    'success' => false,
                    'error' => 'Tipe referensi tidak valid.',
                ];
            }

            $config = $this->referenceTypes[$referenceType];

            // Find the model
            $model = $config['model']::find($referenceId);
            if (!$model) {
                return [
                    'success' => false,
                    'error' => 'Data tidak ditemukan.',
                ];
            }

            // Load related data
            $model = $this->loadRelations($model, $referenceType);

            // Build document data
            $documentData = $this->buildDocumentData($model, $referenceType, $config);

            // Get email audit logs
            $emailLogs = $this->getEmailAuditLogs($model);

            // Calculate document hash
            $documentHash = $this->calculateHash($documentData, $emailLogs);

            // Prepare PDF data
            $pdfData = [
                'document' => $documentData,
                'peserta' => $this->getPesertaData($model, $referenceType),
                'emailLogs' => $emailLogs,
                'emailSummary' => $this->getEmailSummary($emailLogs),
                'integrity' => [
                    'uuid' => Str::uuid()->toString(),
                    'hash' => $documentHash,
                    'generated_at' => now()->setTimezone('Asia/Jakarta')->format('d M Y, H:i:s') . ' WIB',
                    'generated_by' => auth()->user()?->name ?? 'System',
                    'generated_by_role' => $this->getUserRole(),
                ],
                'app_name' => config('app.name', 'CertiPro'),
                'app_url' => config('app.url'),
            ];

            // Generate PDF
            $pdf = Pdf::loadView('pdf.audit-email-notification', $pdfData)
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'defaultFont' => 'sans-serif',
                ]);

            // Generate filename
            $filename = sprintf(
                'Audit-Email-%s-%s-%s.pdf',
                strtoupper($config['reference_prefix']),
                $documentData['reference_number'],
                now()->format('Ymd-His')
            );

            // Log PDF generation
            $this->logPdfGeneration($model, $referenceType, $documentData);

            return [
                'success' => true,
                'pdf' => $pdf,
                'filename' => $filename,
                'hash' => $documentHash,
            ];

        } catch (\Exception $e) {
            Log::error('EmailAuditEvidenceService: PDF generation failed', [
                'type' => $referenceType,
                'id' => $referenceId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Gagal membuat PDF: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Load related model data
     */
    protected function loadRelations($model, string $referenceType)
    {
        switch ($referenceType) {
            case 'pra-pendaftaran':
                // No additional relations needed
                break;

            case 'pendaftaran':
                $model->load(['peserta', 'praPendaftaran', 'skemaSertifikasi']);
                break;

            case 'keputusan':
                $model->load(['pendaftaranSertifikasi.peserta', 'pendaftaranSertifikasi.praPendaftaran', 'sertifikat']);
                break;

            case 'sertifikat':
                $model->load(['keputusanSertifikasi.pendaftaranSertifikasi.peserta', 'keputusanSertifikasi.pendaftaranSertifikasi.praPendaftaran']);
                break;
        }

        return $model;
    }

    /**
     * Build document identification data
     */
    protected function buildDocumentData($model, string $referenceType, array $config): array
    {
        $referenceNumber = $model->{$config['reference_field']} 
            ?? $config['reference_prefix'] . '-' . str_pad($model->id, 6, '0', STR_PAD_LEFT);

        return [
            'document_number' => 'AE-EMAIL-' . strtoupper(Str::random(8)),
            'audit_type' => 'Email & Notifikasi',
            'reference_type' => $config['label'],
            'reference_number' => $referenceNumber,
            'reference_id' => $model->id,
            'print_date' => now()->setTimezone('Asia/Jakarta')->format('d M Y'),
            'print_time' => now()->setTimezone('Asia/Jakarta')->format('H:i:s') . ' WIB',
        ];
    }

    /**
     * Get peserta data from model
     */
    protected function getPesertaData($model, string $referenceType): array
    {
        $nama = 'N/A';
        $email = 'N/A';
        $identitas = 'N/A';

        switch ($referenceType) {
            case 'pra-pendaftaran':
                $nama = $model->nama_lengkap ?? 'N/A';
                $email = $model->email ?? 'N/A';
                $identitas = $model->nik ?? $model->no_ktp ?? 'N/A';
                break;

            case 'pendaftaran':
                if ($model->peserta) {
                    $nama = $model->peserta->nama_lengkap ?? $model->peserta->name ?? 'N/A';
                    $email = $model->peserta->email ?? 'N/A';
                    $identitas = $model->peserta->nik ?? 'N/A';
                } elseif ($model->praPendaftaran) {
                    $nama = $model->praPendaftaran->nama_lengkap ?? 'N/A';
                    $email = $model->praPendaftaran->email ?? 'N/A';
                    $identitas = $model->praPendaftaran->nik ?? 'N/A';
                }
                break;

            case 'keputusan':
            case 'sertifikat':
                $pendaftaran = $referenceType === 'keputusan' 
                    ? $model->pendaftaranSertifikasi 
                    : $model->keputusanSertifikasi?->pendaftaranSertifikasi;
                    
                if ($pendaftaran?->peserta) {
                    $nama = $pendaftaran->peserta->nama_lengkap ?? $pendaftaran->peserta->name ?? 'N/A';
                    $email = $pendaftaran->peserta->email ?? 'N/A';
                    $identitas = $pendaftaran->peserta->nik ?? 'N/A';
                } elseif ($pendaftaran?->praPendaftaran) {
                    $nama = $pendaftaran->praPendaftaran->nama_lengkap ?? 'N/A';
                    $email = $pendaftaran->praPendaftaran->email ?? 'N/A';
                    $identitas = $pendaftaran->praPendaftaran->nik ?? 'N/A';
                }
                break;
        }

        return [
            'nama' => $nama,
            'email' => $email,
            'identitas' => $identitas,
        ];
    }

    /**
     * Get email audit logs for the model
     */
    protected function getEmailAuditLogs($model): array
    {
        $logs = AuditLog::where('auditable_type', get_class($model))
            ->where('auditable_id', $model->id)
            ->whereIn('event', ['email_sent', 'email_failed', 'email_resent', 'email_previewed'])
            ->orderBy('created_at', 'asc')
            ->with('user')
            ->get();

        return $logs->map(function ($log) {
            $newValues = $log->new_values ?? [];
            
            return [
                'timestamp' => $log->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i:s'),
                'event' => $this->formatEventName($log->event),
                'event_code' => $log->event,
                'template' => $newValues['template'] ?? 'N/A',
                'email_to' => $newValues['email_to'] ?? 'N/A',
                'status_trigger' => $newValues['status_trigger'] ?? 'N/A',
                'user' => $log->user?->name ?? 'System',
                'user_role' => $this->getUserRoleFromUser($log->user),
                'ip_address' => $log->ip_address ?? 'N/A',
                'notes' => $newValues['notes'] ?? $newValues['failure_reason'] ?? null,
                'is_success' => $log->event !== 'email_failed',
            ];
        })->toArray();
    }

    /**
     * Get email summary statistics
     */
    protected function getEmailSummary(array $emailLogs): array
    {
        $total = count($emailLogs);
        $sent = count(array_filter($emailLogs, fn($l) => $l['event_code'] === 'email_sent'));
        $resent = count(array_filter($emailLogs, fn($l) => $l['event_code'] === 'email_resent'));
        $failed = count(array_filter($emailLogs, fn($l) => $l['event_code'] === 'email_failed'));
        $previewed = count(array_filter($emailLogs, fn($l) => $l['event_code'] === 'email_previewed'));

        // Get unique templates
        $templates = array_unique(array_filter(array_column($emailLogs, 'template'), fn($t) => $t !== 'N/A'));

        // Get first and last sent
        $sentLogs = array_filter($emailLogs, fn($l) => in_array($l['event_code'], ['email_sent', 'email_resent']));
        $firstSent = !empty($sentLogs) ? reset($sentLogs)['timestamp'] : null;
        $lastSent = !empty($sentLogs) ? end($sentLogs)['timestamp'] : null;

        return [
            'total_events' => $total,
            'sent_count' => $sent,
            'resent_count' => $resent,
            'failed_count' => $failed,
            'previewed_count' => $previewed,
            'templates' => $templates,
            'first_sent' => $firstSent,
            'last_sent' => $lastSent,
            'has_failures' => $failed > 0,
        ];
    }

    /**
     * Format event name for display
     */
    protected function formatEventName(string $event): string
    {
        return match ($event) {
            'email_sent' => 'Email Terkirim (Otomatis)',
            'email_resent' => 'Email Dikirim Ulang (Manual)',
            'email_failed' => 'Pengiriman Gagal',
            'email_previewed' => 'Preview Email',
            default => ucwords(str_replace('_', ' ', $event)),
        };
    }

    /**
     * Calculate SHA-256 hash for document integrity
     */
    protected function calculateHash(array $documentData, array $emailLogs): string
    {
        $dataString = json_encode([
            'document' => $documentData,
            'logs_count' => count($emailLogs),
            'logs_hash' => md5(json_encode($emailLogs)),
            'timestamp' => now()->toIso8601String(),
        ]);

        return hash('sha256', $dataString);
    }

    /**
     * Get current user role
     */
    protected function getUserRole(): string
    {
        $user = auth()->user();
        if (!$user) {
            return 'System';
        }

        // Use userRole relation (primary RBAC)
        if ($user->userRole) {
            return $user->userRole->display_name ?? $user->userRole->name ?? 'User';
        }

        // Fallback: check roles() pivot
        if (method_exists($user, 'roles') && $user->roles->isNotEmpty()) {
            return $user->roles->pluck('name')->first() ?? 'User';
        }

        return 'User';
    }

    /**
     * Get role from user model
     */
    protected function getUserRoleFromUser($user): string
    {
        if (!$user) {
            return 'System';
        }

        // Use userRole relation (primary RBAC)
        if ($user->userRole) {
            return $user->userRole->display_name ?? $user->userRole->name ?? 'User';
        }

        // Fallback: check roles() pivot
        if (method_exists($user, 'roles') && $user->relationLoaded('roles') && $user->roles->isNotEmpty()) {
            return $user->roles->pluck('name')->first() ?? 'User';
        }

        return 'User';
    }

    /**
     * Log PDF generation to audit log
     */
    protected function logPdfGeneration($model, string $referenceType, array $documentData): void
    {
        try {
            AuditLog::create([
                'user_id' => auth()->id(),
                'event' => 'audit_email_pdf_generated',
                'auditable_type' => get_class($model),
                'auditable_id' => $model->id,
                'old_values' => null,
                'new_values' => [
                    'document_number' => $documentData['document_number'],
                    'reference_type' => $referenceType,
                    'reference_number' => $documentData['reference_number'],
                ],
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('EmailAuditEvidenceService: Failed to log PDF generation', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}

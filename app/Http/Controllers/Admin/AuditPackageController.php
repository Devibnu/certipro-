<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SkemaSertifikasi;
use App\Models\PraPendaftaran;
use App\Models\PendaftaranSertifikasi;
use App\Models\Sertifikat;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * ============================================================================
 * Controller: AuditPackageController
 * ============================================================================
 * Generates the FINAL AUDIT PACKAGE PDF for LSP CertiPro.
 * This document serves as the cover page and index for all audit evidence.
 * 
 * Purpose:
 * - Official audit document opener for BNSP audit
 * - Binds all technical audit evidence documents
 * - Provides system overview and compliance mapping
 * 
 * Compliance: BNSP, ISO 17024
 * ============================================================================
 */
class AuditPackageController extends Controller
{
    /**
     * LSP Information (can be moved to database/config)
     */
    protected array $lspInfo = [
        'nama' => 'LSP CertiPro',
        'nomor_lisensi' => 'BNSP-LSP-XXX-ID',
        'alamat' => 'Jl. Sertifikasi No. 123, Jakarta Selatan 12345',
        'telepon' => '(021) 1234-5678',
        'email' => 'info@certipro-lsp.id',
        'website' => 'https://certipro-lsp.id',
        'ketua_lsp' => 'Dr. Ahmad Sertifikasi, M.Kom',
        'jabatan_ketua' => 'Ketua LSP CertiPro',
    ];

    /**
     * System Information
     */
    protected array $systemInfo = [
        'nama' => 'CertiPro',
        'versi' => '1.0.0',
        'deskripsi' => 'Sistem Informasi Manajemen Sertifikasi Profesi berbasis web yang dirancang untuk mendukung operasional Lembaga Sertifikasi Profesi (LSP) sesuai dengan standar BNSP dan ISO 17024.',
        'ruang_lingkup' => 'Pengelolaan pra-pendaftaran, pendaftaran sertifikasi, pelaksanaan asesmen, keputusan sertifikasi, penerbitan sertifikat, dan manajemen audit trail.',
    ];

    /**
     * Modules list
     */
    protected array $modules = [
        ['nama' => 'Pra-Pendaftaran', 'deskripsi' => 'Pendaftaran awal calon peserta sertifikasi'],
        ['nama' => 'Pendaftaran Sertifikasi', 'deskripsi' => 'Registrasi formal peserta pada skema sertifikasi'],
        ['nama' => 'Asesmen Kompetensi', 'deskripsi' => 'Pelaksanaan dan penilaian uji kompetensi'],
        ['nama' => 'Keputusan Sertifikasi', 'deskripsi' => 'Penetapan keputusan kompeten/belum kompeten'],
        ['nama' => 'Manajemen Sertifikat', 'deskripsi' => 'Penerbitan, verifikasi, dan pencabutan sertifikat'],
        ['nama' => 'Skema Sertifikasi', 'deskripsi' => 'Pengelolaan skema dan unit kompetensi'],
        ['nama' => 'Manajemen Asesor', 'deskripsi' => 'Pengelolaan data dan penugasan asesor'],
        ['nama' => 'Audit Log', 'deskripsi' => 'Pencatatan aktivitas sistem secara otomatis'],
        ['nama' => 'Email & Notifikasi', 'deskripsi' => 'Pengiriman notifikasi email otomatis'],
        ['nama' => 'Laporan & Statistik', 'deskripsi' => 'Dashboard dan laporan operasional'],
    ];

    /**
     * Audit Evidence Index
     */
    protected array $auditEvidence = [
        [
            'nama' => 'Bukti Audit Pra-Pendaftaran',
            'modul' => 'Pra-Pendaftaran',
            'jenis' => 'PDF',
            'keterangan' => 'Data pendaftar, validasi, status, dan timeline',
        ],
        [
            'nama' => 'Bukti Audit Pendaftaran Sertifikasi',
            'modul' => 'Pendaftaran',
            'jenis' => 'PDF',
            'keterangan' => 'Data peserta, dokumen persyaratan, verifikasi',
        ],
        [
            'nama' => 'Bukti Audit Asesmen Kompetensi',
            'modul' => 'Asesmen',
            'jenis' => 'PDF',
            'keterangan' => 'Jadwal, asesor, hasil penilaian, rekomendasi',
        ],
        [
            'nama' => 'Bukti Audit Keputusan Sertifikasi',
            'modul' => 'Keputusan',
            'jenis' => 'PDF',
            'keterangan' => 'Penetapan keputusan, approval chain, tanggal efektif',
        ],
        [
            'nama' => 'Bukti Audit Sertifikat',
            'modul' => 'Sertifikat',
            'jenis' => 'PDF',
            'keterangan' => 'Nomor sertifikat, QR Code, UUID, masa berlaku',
        ],
        [
            'nama' => 'Bukti Audit Log Aktivitas',
            'modul' => 'Audit Log',
            'jenis' => 'PDF',
            'keterangan' => 'Riwayat aktivitas, user, IP, timestamp',
        ],
        [
            'nama' => 'Bukti Audit Email & Notifikasi',
            'modul' => 'Email',
            'jenis' => 'PDF',
            'keterangan' => 'Riwayat pengiriman email, status, template',
        ],
    ];

    /**
     * Compliance Mapping
     */
    protected array $compliance = [
        'bnsp' => [
            'title' => 'BNSP (Badan Nasional Sertifikasi Profesi)',
            'items' => [
                'Pedoman pelaksanaan sertifikasi kompetensi',
                'Persyaratan lisensi LSP',
                'Standar pelaksanaan uji kompetensi',
                'Mekanisme pengawasan dan surveilan',
            ],
        ],
        'iso17024' => [
            'title' => 'ISO/IEC 17024:2012',
            'items' => [
                'Prinsip impartiality (ketidakberpihakan)',
                'Traceability (ketertelusuran)',
                'Competence assessment (penilaian kompetensi)',
                'Certification decision (keputusan sertifikasi)',
                'Confidentiality (kerahasiaan)',
            ],
        ],
    ];

    /**
     * Generate Final Audit Package PDF
     * 
     * @route GET /adminui/audit/final-package/pdf
     */
    public function generateFinalPackage(Request $request)
    {
        // Authorization check
        if (!$this->canAccessAuditPackage()) {
            abort(403, 'Anda tidak memiliki akses untuk mengunduh paket audit.');
        }

        try {
            // Get statistics
            $statistics = $this->getSystemStatistics();

            // Prepare document data
            $documentData = [
                'document_number' => 'AP-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
                'generated_at' => now()->setTimezone('Asia/Jakarta')->format('d F Y, H:i:s') . ' WIB',
                'generated_by' => auth()->user()?->name ?? 'System',
                'generated_by_role' => $this->getUserRole(),
                'audit_date' => $request->get('audit_date', now()->format('d F Y')),
                'audit_period' => $request->get('audit_period', 'Januari - Desember ' . date('Y')),
                'hash' => $this->generateDocumentHash(),
                'uuid' => Str::uuid()->toString(),
            ];

            // Prepare PDF data
            $pdfData = [
                'lsp' => $this->getLspInfo(),
                'system' => $this->systemInfo,
                'modules' => $this->modules,
                'auditEvidence' => $this->auditEvidence,
                'compliance' => $this->compliance,
                'statistics' => $statistics,
                'document' => $documentData,
                'app_name' => config('app.name', 'CertiPro'),
            ];

            // Generate PDF
            $pdf = Pdf::loadView('pdf.audit-final-package', $pdfData)
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'defaultFont' => 'sans-serif',
                ]);

            // Generate filename
            $filename = sprintf(
                'Paket-Audit-LSP-%s-%s.pdf',
                str_replace(' ', '-', $this->lspInfo['nama']),
                date('Ymd-His')
            );

            // Log generation
            $this->logPackageGeneration($documentData);

            // Return based on request
            if ($request->get('preview')) {
                return $pdf->stream($filename);
            }

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('AuditPackageController: Failed to generate package', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Gagal membuat paket audit: ' . $e->getMessage());
        }
    }

    /**
     * Preview Final Audit Package PDF
     * 
     * @route GET /adminui/audit/final-package/preview
     */
    public function preview(Request $request)
    {
        $request->merge(['preview' => true]);
        return $this->generateFinalPackage($request);
    }

    /**
     * Get LSP information (can be extended to fetch from database)
     */
    protected function getLspInfo(): array
    {
        // Try to get from settings table if exists
        try {
            if (class_exists('App\Models\Setting')) {
                $settings = \App\Models\Setting::whereIn('key', [
                    'lsp_nama', 'lsp_nomor_lisensi', 'lsp_alamat', 
                    'lsp_telepon', 'lsp_email', 'lsp_website',
                    'lsp_ketua', 'lsp_jabatan_ketua'
                ])->pluck('value', 'key');

                if ($settings->isNotEmpty()) {
                    return [
                        'nama' => $settings['lsp_nama'] ?? $this->lspInfo['nama'],
                        'nomor_lisensi' => $settings['lsp_nomor_lisensi'] ?? $this->lspInfo['nomor_lisensi'],
                        'alamat' => $settings['lsp_alamat'] ?? $this->lspInfo['alamat'],
                        'telepon' => $settings['lsp_telepon'] ?? $this->lspInfo['telepon'],
                        'email' => $settings['lsp_email'] ?? $this->lspInfo['email'],
                        'website' => $settings['lsp_website'] ?? $this->lspInfo['website'],
                        'ketua_lsp' => $settings['lsp_ketua'] ?? $this->lspInfo['ketua_lsp'],
                        'jabatan_ketua' => $settings['lsp_jabatan_ketua'] ?? $this->lspInfo['jabatan_ketua'],
                    ];
                }
            }
        } catch (\Exception $e) {
            // Fallback to default
        }

        return $this->lspInfo;
    }

    /**
     * Get system statistics
     */
    protected function getSystemStatistics(): array
    {
        try {
            return [
                'total_skema' => class_exists(SkemaSertifikasi::class) 
                    ? SkemaSertifikasi::where('is_active', true)->count() 
                    : 0,
                'total_pra_pendaftaran' => class_exists(PraPendaftaran::class) 
                    ? PraPendaftaran::count() 
                    : 0,
                'total_pendaftaran' => class_exists(PendaftaranSertifikasi::class) 
                    ? PendaftaranSertifikasi::count() 
                    : 0,
                'total_sertifikat' => class_exists(Sertifikat::class) 
                    ? Sertifikat::where('status', 'aktif')->count() 
                    : 0,
                'total_asesor' => class_exists(User::class) 
                    ? User::whereHas('roles', fn($q) => $q->where('name', 'like', '%asesor%'))->count() 
                    : 0,
                'total_audit_logs' => class_exists(AuditLog::class) 
                    ? AuditLog::count() 
                    : 0,
            ];
        } catch (\Exception $e) {
            return [
                'total_skema' => 0,
                'total_pra_pendaftaran' => 0,
                'total_pendaftaran' => 0,
                'total_sertifikat' => 0,
                'total_asesor' => 0,
                'total_audit_logs' => 0,
            ];
        }
    }

    /**
     * Generate document hash for integrity
     */
    protected function generateDocumentHash(): string
    {
        $data = json_encode([
            'lsp' => $this->lspInfo,
            'system' => $this->systemInfo,
            'timestamp' => now()->toIso8601String(),
            'user' => auth()->id(),
        ]);

        return hash('sha256', $data);
    }

    /**
     * Log package generation to audit log
     */
    protected function logPackageGeneration(array $documentData): void
    {
        try {
            AuditLog::create([
                'user_id' => auth()->id(),
                'event' => 'audit_final_package_generated',
                'auditable_type' => 'App\Models\AuditPackage',
                'auditable_id' => 0,
                'old_values' => null,
                'new_values' => [
                    'document_number' => $documentData['document_number'],
                    'generated_at' => $documentData['generated_at'],
                    'hash' => $documentData['hash'],
                ],
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('AuditPackageController: Failed to log generation', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check if user can access audit package
     */
    protected function canAccessAuditPackage(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasRole')) {
            return $user->hasRole(['super_admin', 'admin', 'Super Admin', 'Admin']);
        }

        if (method_exists($user, 'roles') && $user->roles) {
            $roleNames = $user->roles->pluck('name')->toArray();
            return count(array_intersect($roleNames, ['super_admin', 'admin', 'Super Admin', 'Admin'])) > 0;
        }

        return true;
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

        if (method_exists($user, 'roles') && $user->roles) {
            return $user->roles->pluck('name')->first() ?? 'User';
        }

        return $user->userRole?->name ?? 'User';
    }
}

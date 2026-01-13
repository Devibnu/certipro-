<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PendaftaranSertifikasi;
use App\Models\Sertifikat;
use App\Models\SkemaSertifikasi;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditEvidenceController extends Controller
{
    /**
     * Generate Audit Evidence PDF
     * 
     * Dokumen resmi untuk audit BNSP yang berisi:
     * - Identitas Sistem
     * - Arsitektur & Keamanan
     * - Alur Sertifikasi
     * - Bukti Audit Log
     * - Bukti Asesmen & Keputusan
     * - Keamanan Sertifikat
     */
    public function generatePdf(Request $request)
    {
        // Collect all data for the evidence document
        $data = $this->collectEvidenceData($request);
        
        $pdf = Pdf::loadView('pdf.audit-evidence', $data);
        
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
            'dpi' => 150,
        ]);
        
        $filename = 'Audit_Evidence_CertiPro_' . now()->format('Y-m-d_His') . '.pdf';
        
        return $pdf->download($filename);
    }
    
    /**
     * Preview Audit Evidence PDF in browser
     */
    public function preview(Request $request)
    {
        $data = $this->collectEvidenceData($request);
        
        $pdf = Pdf::loadView('pdf.audit-evidence', $data);
        
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
            'dpi' => 150,
        ]);
        
        return $pdf->stream('Audit_Evidence_CertiPro_Preview.pdf');
    }
    
    /**
     * Collect all evidence data for PDF generation
     */
    private function collectEvidenceData(Request $request): array
    {
        // 1. System Identity
        $systemInfo = [
            'nama_sistem' => 'CertiPro',
            'versi' => config('app.version', '1.0.0'),
            'url' => config('app.url'),
            'nama_lsp' => config('certipro.nama_lsp'),
            'alamat' => config('certipro.alamat'),
            'telepon' => config('certipro.telepon'),
            'email' => config('certipro.email'),
            'website' => config('certipro.website'),
            'ketua_lsp' => config('certipro.ketua_lsp'),
            'nomor_lisensi' => config('certipro.nomor_lisensi'),
            'kota_terbit' => config('certipro.kota_terbit'),
        ];
        
        // 2. Statistics
        $statistics = [
            'total_users' => User::count(),
            'total_asesi' => User::where('role', 'asesi')->count(),
            'total_asesor' => User::where('role', 'asesor')->count(),
            'total_admin' => User::whereIn('role', ['super admin', 'admin'])->count(),
            'total_komite' => User::where('role', 'komite_teknis')->count(),
            'total_skema' => SkemaSertifikasi::count(),
            'total_pendaftaran' => PendaftaranSertifikasi::count(),
            'total_sertifikat' => Sertifikat::count(),
            'sertifikat_aktif' => Sertifikat::where('status', 'aktif')->count(),
            'total_audit_log' => AuditLog::count(),
            'audit_log_today' => AuditLog::whereDate('created_at', today())->count(),
            'audit_log_week' => AuditLog::whereDate('created_at', '>=', now()->subDays(7))->count(),
        ];
        
        // 3. Role Distribution
        $roleDistribution = User::select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->pluck('count', 'role')
            ->toArray();
        
        // 4. Recent Audit Logs (sample for evidence)
        $recentLogs = AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
        
        // 5. Certification Process Statistics
        $certificationStats = [
            'pendaftaran_diajukan' => PendaftaranSertifikasi::where('status', 'diajukan')->count(),
            'pendaftaran_diverifikasi' => PendaftaranSertifikasi::where('status', 'diverifikasi')->count(),
            'pendaftaran_asesmen' => PendaftaranSertifikasi::where('status', 'asesmen')->count(),
            'pendaftaran_kompeten' => PendaftaranSertifikasi::where('status', 'kompeten')->count(),
            'pendaftaran_belum_kompeten' => PendaftaranSertifikasi::where('status', 'belum_kompeten')->count(),
        ];
        
        // 6. Event Types Distribution
        $eventDistribution = AuditLog::select('action', DB::raw('count(*) as count'))
            ->groupBy('action')
            ->pluck('count', 'action')
            ->toArray();
        
        // 7. Module Activity Distribution
        $moduleDistribution = AuditLog::select('module', DB::raw('count(*) as count'))
            ->groupBy('module')
            ->pluck('count', 'module')
            ->toArray();
        
        // 8. Active Users in Last 7 Days
        $activeUsers = AuditLog::whereDate('created_at', '>=', now()->subDays(7))
            ->distinct('user_id')
            ->count('user_id');
        
        // 9. Critical Events Count
        $criticalEvents = AuditLog::whereIn('action', [
            AuditLog::ACTION_DELETE,
            AuditLog::ACTION_REVOKE,
            AuditLog::ACTION_REJECT,
        ])->count();
        
        // 10. Document metadata
        $metadata = [
            'generated_at' => now(),
            'generated_by' => auth()->user()->name ?? 'System',
            'document_hash' => hash('sha256', json_encode([
                $systemInfo,
                $statistics,
                now()->toDateTimeString(),
            ])),
            'document_id' => 'AE-' . now()->format('Ymd-His') . '-' . strtoupper(substr(uniqid(), -6)),
        ];
        
        // 11. Sample Certificates (for evidence)
        $sampleCertificates = Sertifikat::with(['pendaftaranSertifikasi.skemaSertifikasi', 'pendaftaranSertifikasi.user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return [
            'systemInfo' => $systemInfo,
            'statistics' => $statistics,
            'roleDistribution' => $roleDistribution,
            'recentLogs' => $recentLogs,
            'certificationStats' => $certificationStats,
            'eventDistribution' => $eventDistribution,
            'moduleDistribution' => $moduleDistribution,
            'activeUsers' => $activeUsers,
            'criticalEvents' => $criticalEvents,
            'metadata' => $metadata,
            'sampleCertificates' => $sampleCertificates,
        ];
    }
}

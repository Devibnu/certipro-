<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\AuditLog;
use App\Models\KeputusanSertifikasi;
use App\Models\PendaftaranSertifikasi;
use App\Models\PraPendaftaran;
use App\Models\Sertifikat;
use App\Models\SkemaSertifikasi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * ============================================================================
 * Dashboard Controller - LSP CertiPro
 * ============================================================================
 * 
 * Dashboard operasional untuk Admin LSP yang menampilkan:
 * - KPI utama proses sertifikasi
 * - Alur sertifikasi dengan jumlah data per tahap
 * - Aktivitas sistem terbaru (audit log)
 * - Alert/notifikasi penting
 * 
 * Compliance: ISO 17024:2012, BNSP Pedoman 201
 * 
 * @see ISO 17024:2012 Clause 5.1 - Management Representative
 * @see ISO 17024:2012 Clause 8.6 - Records Management
 * ============================================================================
 */
class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return redirect()->route('adminui.login');
        }

        // =================================================================
        // A. KPI UTAMA - PRA-PENDAFTARAN
        // =================================================================
        $praPendaftaranStats = $this->getPraPendaftaranStats();

        // =================================================================
        // B. KPI UTAMA - PENDAFTARAN SERTIFIKASI
        // =================================================================
        $pendaftaranStats = $this->getPendaftaranStats();

        // =================================================================
        // C. KPI UTAMA - ASESMEN
        // =================================================================
        $asesmenStats = $this->getAsesmenStats();

        // =================================================================
        // D. KPI UTAMA - SERTIFIKAT
        // =================================================================
        $sertifikatStats = $this->getSertifikatStats();

        // =================================================================
        // E. ALUR SERTIFIKASI (FLOW VISUAL)
        // =================================================================
        $flowStats = $this->getCertificationFlowStats();

        // =================================================================
        // F. AKTIVITAS TERBARU (AUDIT LOG)
        // =================================================================
        $recentActivities = $this->getRecentActivities();

        // =================================================================
        // G. ALERTS / ATTENTION BOX
        // =================================================================
        $alerts = $this->getAlerts();

        // =================================================================
        // H. STATISTIK TAMBAHAN
        // =================================================================
        $additionalStats = $this->getAdditionalStats();

        return view('adminui.dashboard-lsp', compact(
            'praPendaftaranStats',
            'pendaftaranStats',
            'asesmenStats',
            'sertifikatStats',
            'flowStats',
            'recentActivities',
            'alerts',
            'additionalStats'
        ));
    }

    /**
     * Get Pra-Pendaftaran statistics.
     */
    private function getPraPendaftaranStats(): array
    {
        $total = PraPendaftaran::count();
        $menungguVerifikasi = PraPendaftaran::where('status', PraPendaftaran::STATUS_MENUNGGU_VERIFIKASI)->count();
        $diterima = PraPendaftaran::where('status', PraPendaftaran::STATUS_DITERIMA)->count();
        $ditolak = PraPendaftaran::where('status', PraPendaftaran::STATUS_DITOLAK)->count();

        // Trend 7 hari terakhir
        $newLast7Days = PraPendaftaran::where('created_at', '>=', Carbon::now()->subDays(7))->count();

        return [
            'total' => $total,
            'menunggu_verifikasi' => $menungguVerifikasi,
            'diterima' => $diterima,
            'ditolak' => $ditolak,
            'new_7_days' => $newLast7Days,
        ];
    }

    /**
     * Get Pendaftaran Sertifikasi statistics.
     */
    private function getPendaftaranStats(): array
    {
        $total = PendaftaranSertifikasi::count();
        $siapAsesmen = PendaftaranSertifikasi::where('status', PendaftaranSertifikasi::STATUS_SIAP_ASESMEN)->count();
        $dalamAsesmen = PendaftaranSertifikasi::whereHas('asesmen', function ($q) {
            $q->where('status', Asesmen::STATUS_PROSES);
        })->count();
        $menungguKeputusan = PendaftaranSertifikasi::where('status', PendaftaranSertifikasi::STATUS_MENUNGGU_KEPUTUSAN)->count();
        $diverifikasi = PendaftaranSertifikasi::where('status', PendaftaranSertifikasi::STATUS_DIVERIFIKASI)->count();

        // Trend 7 hari terakhir
        $newLast7Days = PendaftaranSertifikasi::where('created_at', '>=', Carbon::now()->subDays(7))->count();

        return [
            'total' => $total,
            'siap_asesmen' => $siapAsesmen,
            'dalam_asesmen' => $dalamAsesmen,
            'menunggu_keputusan' => $menungguKeputusan,
            'diverifikasi' => $diverifikasi,
            'new_7_days' => $newLast7Days,
        ];
    }

    /**
     * Get Asesmen statistics.
     */
    private function getAsesmenStats(): array
    {
        $total = Asesmen::count();
        $proses = Asesmen::where('status', Asesmen::STATUS_PROSES)->count();
        $selesai = Asesmen::where('status', Asesmen::STATUS_SELESAI)->count();
        $sampled = Asesmen::where('is_sampled', true)->count();

        // Asesmen yang disampling tapi belum ada keputusan
        $sampledPendingKeputusan = Asesmen::where('is_sampled', true)
            ->whereDoesntHave('pendaftaran.keputusan')
            ->count();

        // Trend 7 hari terakhir
        $newLast7Days = Asesmen::where('created_at', '>=', Carbon::now()->subDays(7))->count();

        return [
            'total' => $total,
            'proses' => $proses,
            'selesai' => $selesai,
            'sampled' => $sampled,
            'sampled_pending_keputusan' => $sampledPendingKeputusan,
            'new_7_days' => $newLast7Days,
        ];
    }

    /**
     * Get Sertifikat statistics.
     */
    private function getSertifikatStats(): array
    {
        $total = Sertifikat::count();
        $today = Carbon::today();
        
        // Aktif = tanggal_berlaku_sampai >= hari ini
        $aktif = Sertifikat::where('tanggal_berlaku_sampai', '>=', $today)->count();
        
        // Kadaluarsa = tanggal_berlaku_sampai < hari ini
        $kadaluarsa = Sertifikat::where('tanggal_berlaku_sampai', '<', $today)->count();
        
        // Akan kadaluarsa dalam 30 hari
        $akanKadaluarsa = Sertifikat::whereBetween('tanggal_berlaku_sampai', [
            $today,
            $today->copy()->addDays(30)
        ])->count();

        // Terbit bulan ini
        $terbitBulanIni = Sertifikat::whereYear('tanggal_terbit', $today->year)
            ->whereMonth('tanggal_terbit', $today->month)
            ->count();

        return [
            'total' => $total,
            'aktif' => $aktif,
            'kadaluarsa' => $kadaluarsa,
            'akan_kadaluarsa_30_hari' => $akanKadaluarsa,
            'terbit_bulan_ini' => $terbitBulanIni,
        ];
    }

    /**
     * Get Certification Flow Statistics.
     * Menampilkan jumlah data di setiap tahap alur sertifikasi.
     */
    private function getCertificationFlowStats(): array
    {
        // Tahap 1: Pra-Pendaftaran (menunggu verifikasi)
        $tahap1 = PraPendaftaran::where('status', PraPendaftaran::STATUS_MENUNGGU_VERIFIKASI)->count();

        // Tahap 2: Verifikasi Admin (pendaftaran baru/diajukan)
        $tahap2 = PendaftaranSertifikasi::whereIn('status', [
            PendaftaranSertifikasi::STATUS_DRAFT,
            PendaftaranSertifikasi::STATUS_DIAJUKAN,
            PendaftaranSertifikasi::STATUS_DIVERIFIKASI
        ])->count();

        // Tahap 3: Asesmen Asesor (siap asesmen + dalam proses)
        $tahap3 = PendaftaranSertifikasi::where('status', PendaftaranSertifikasi::STATUS_SIAP_ASESMEN)->count()
            + Asesmen::where('status', Asesmen::STATUS_PROSES)->count();

        // Tahap 4: Keputusan Komite (menunggu keputusan)
        $tahap4 = PendaftaranSertifikasi::where('status', PendaftaranSertifikasi::STATUS_MENUNGGU_KEPUTUSAN)->count();

        // Tahap 5: Sertifikat Terbit (kompeten final dengan sertifikat)
        $tahap5 = Sertifikat::count();

        return [
            'pra_pendaftaran' => $tahap1,
            'verifikasi_admin' => $tahap2,
            'asesmen_asesor' => $tahap3,
            'keputusan_komite' => $tahap4,
            'sertifikat_terbit' => $tahap5,
        ];
    }

    /**
     * Get Recent Activities from Audit Log.
     */
    private function getRecentActivities(): \Illuminate\Support\Collection
    {
        return AuditLog::with('user.userRole')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'waktu' => $log->created_at->format('d M Y H:i'),
                    'waktu_relative' => $log->created_at->diffForHumans(),
                    'modul' => $this->formatModuleName($log->module),
                    'aksi' => $log->description,
                    'user_name' => $log->user?->name ?? 'System',
                    'user_role' => $log->user?->userRole?->display_name ?? '-',
                    'ip_address' => $log->ip_address,
                ];
            });
    }

    /**
     * Format module name for display.
     */
    private function formatModuleName(?string $module): string
    {
        $labels = [
            'pra_pendaftaran' => 'Pra-Pendaftaran',
            'pendaftaran' => 'Pendaftaran',
            'asesmen' => 'Asesmen',
            'keputusan' => 'Keputusan',
            'sertifikat' => 'Sertifikat',
            'evidence' => 'Evidence',
            'sampling' => 'Sampling',
            'access_control' => 'Akses Kontrol',
            'users' => 'Users',
            'skema' => 'Skema',
            'cms' => 'CMS',
        ];

        return $labels[$module] ?? ucfirst($module ?? 'Sistem');
    }

    /**
     * Get Alerts / Attention items.
     */
    private function getAlerts(): array
    {
        $alerts = [];

        // Alert 1: Pra-pendaftaran menunggu verifikasi > 3 hari
        $praPendaftaranLama = PraPendaftaran::where('status', PraPendaftaran::STATUS_MENUNGGU_VERIFIKASI)
            ->where('created_at', '<', Carbon::now()->subDays(3))
            ->count();
        
        if ($praPendaftaranLama > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'fas fa-clock',
                'title' => 'Pra-Pendaftaran Menunggu',
                'message' => "{$praPendaftaranLama} pra-pendaftaran menunggu verifikasi lebih dari 3 hari",
                'link' => route('adminui.pra-pendaftaran.index'),
                'link_text' => 'Lihat Daftar',
            ];
        }

        // Alert 2: Asesmen selesai tapi belum ada keputusan > 5 hari
        $asesmenPendingKeputusan = Asesmen::where('status', Asesmen::STATUS_SELESAI)
            ->where('updated_at', '<', Carbon::now()->subDays(5))
            ->whereHas('pendaftaran', function ($q) {
                $q->where('status', PendaftaranSertifikasi::STATUS_MENUNGGU_KEPUTUSAN);
            })
            ->count();

        if ($asesmenPendingKeputusan > 0) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'fas fa-exclamation-triangle',
                'title' => 'Keputusan Tertunda',
                'message' => "{$asesmenPendingKeputusan} asesmen selesai menunggu keputusan > 5 hari",
                'link' => route('adminui.keputusan.index'),
                'link_text' => 'Proses Keputusan',
            ];
        }

        // Alert 3: Asesmen disampling tapi belum diputus
        $sampledPending = Asesmen::where('is_sampled', true)
            ->where('status', Asesmen::STATUS_SELESAI)
            ->whereHas('pendaftaran', function ($q) {
                $q->whereDoesntHave('keputusan');
            })
            ->count();

        if ($sampledPending > 0) {
            $alerts[] = [
                'type' => 'info',
                'icon' => 'fas fa-search',
                'title' => 'Sampling Audit Pending',
                'message' => "{$sampledPending} asesmen sampling menunggu review Komite Teknis",
                'link' => route('adminui.asesmen.index', ['filter' => 'sampled']),
                'link_text' => 'Lihat Sampling',
            ];
        }

        // Alert 4: Sertifikat akan kadaluarsa dalam 30 hari
        $akanKadaluarsa = Sertifikat::whereBetween('tanggal_berlaku_sampai', [
            Carbon::today(),
            Carbon::today()->addDays(30)
        ])->count();

        if ($akanKadaluarsa > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'fas fa-calendar-times',
                'title' => 'Sertifikat Akan Kadaluarsa',
                'message' => "{$akanKadaluarsa} sertifikat akan kadaluarsa dalam 30 hari ke depan",
                'link' => route('adminui.sertifikat.index'),
                'link_text' => 'Lihat Sertifikat',
            ];
        }

        return $alerts;
    }

    /**
     * Get Additional Statistics.
     */
    private function getAdditionalStats(): array
    {
        // Skema Sertifikasi Aktif (kolom 'aktif' adalah boolean)
        $skemaAktif = SkemaSertifikasi::where('aktif', true)->count();

        // Tingkat kelulusan (kompeten dari total keputusan)
        $totalKeputusan = KeputusanSertifikasi::count();
        $kompeten = KeputusanSertifikasi::where('keputusan', KeputusanSertifikasi::KEPUTUSAN_KOMPETEN)->count();
        $tingkatKelulusan = $totalKeputusan > 0 ? round(($kompeten / $totalKeputusan) * 100, 1) : 0;

        // Asesmen bulan ini
        $asesmenBulanIni = Asesmen::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();

        // Pendaftaran bulan ini
        $pendaftaranBulanIni = PendaftaranSertifikasi::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();

        return [
            'skema_aktif' => $skemaAktif,
            'tingkat_kelulusan' => $tingkatKelulusan,
            'total_keputusan' => $totalKeputusan,
            'total_kompeten' => $kompeten,
            'asesmen_bulan_ini' => $asesmenBulanIni,
            'pendaftaran_bulan_ini' => $pendaftaranBulanIni,
        ];
    }
}

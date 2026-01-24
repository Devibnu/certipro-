<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PendaftaranSertifikasi;
use App\Models\PraPendaftaran;
use App\Models\SkemaSertifikasi;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PendaftaranSertifikasiAdminController extends Controller
{
    /**
     * Display a listing of all pendaftaran.
     */
    public function index(Request $request)
    {
        $query = PendaftaranSertifikasi::with(['user', 'skemaSertifikasi']);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }
        
        // Filter by skema
        if ($request->filled('skema_sertifikasi_id')) {
            $query->where('skema_sertifikasi_id', $request->skema_sertifikasi_id);
        }
        
        // Search by nomor pendaftaran or nama asesi
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_pendaftaran', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        $pendaftaran = $query->orderBy('created_at', 'desc')
                              ->paginate(15)
                              ->withQueryString();
        
        $skemaList = SkemaSertifikasi::orderBy('nama_skema')->get();
        $statusLabels = PendaftaranSertifikasi::statusLabels();
        
        // Get pra-pendaftaran yang DITERIMA tapi belum dibuatkan pendaftaran sertifikasi
        $praPendaftaranReady = PraPendaftaran::where('status', PraPendaftaran::STATUS_DITERIMA)
            ->whereDoesntHave('pendaftaranSertifikasi')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('adminui.pendaftaran-sertifikasi.index', compact('pendaftaran', 'skemaList', 'statusLabels', 'praPendaftaranReady'));
    }

    /**
     * ========================================================================
     * NEW: Create Pendaftaran Sertifikasi dari Pra-Pendaftaran yang DITERIMA
     * dengan menetapkan Skema Sertifikasi
     * ========================================================================
     */
    public function createFromPraPendaftaran(Request $request, $praPendaftaranId)
    {
        $praPendaftaran = PraPendaftaran::findOrFail($praPendaftaranId);

        // Guard 1: Status harus DITERIMA
        if ($praPendaftaran->status !== PraPendaftaran::STATUS_DITERIMA) {
            return redirect()->back()->with('error', 
                'Hanya pra-pendaftaran dengan status DITERIMA yang dapat dibuatkan pendaftaran sertifikasi.'
            );
        }

        // Guard 2: Cek apakah sudah ada pendaftaran sertifikasi (idempotent)
        if ($praPendaftaran->hasPendaftaranSertifikasi()) {
            return redirect()->back()->with('error', 
                'Pendaftaran sertifikasi sudah dibuat untuk pra-pendaftaran ini: ' . 
                $praPendaftaran->pendaftaranSertifikasi->nomor_pendaftaran
            );
        }

        // Validasi: Skema sertifikasi WAJIB dipilih
        $validated = $request->validate([
            'skema_sertifikasi_id' => 'required|exists:skema_sertifikasi,id',
        ], [
            'skema_sertifikasi_id.required' => 'Skema sertifikasi WAJIB dipilih saat membuat pendaftaran.',
            'skema_sertifikasi_id.exists' => 'Skema sertifikasi tidak valid.',
        ]);

        try {
            DB::beginTransaction();

            // 1. CREATE USER ACCOUNT (jika belum ada) - Race condition safe
            $user = User::where('email', $praPendaftaran->email)
                ->lockForUpdate()
                ->first();
            
            if (!$user) {
                try {
                    $user = User::create([
                        'email' => $praPendaftaran->email,
                        'name' => $praPendaftaran->nama_lengkap,
                        'password' => bcrypt('password123'),
                        'role_id' => 1, // Role: asesi
                        'phone' => $praPendaftaran->no_hp,
                    ]);
                    $isNewUser = true;
                } catch (\Illuminate\Database\QueryException $e) {
                    if ($e->getCode() == 23000) {
                        $user = User::where('email', $praPendaftaran->email)->first();
                        $isNewUser = false;
                    } else {
                        throw $e;
                    }
                }
            } else {
                $isNewUser = false;
            }

            // 2. Generate nomor pendaftaran
            $year = date('Y');
            $lastNumber = PendaftaranSertifikasi::whereYear('created_at', $year)->count() + 1;
            $nomorPendaftaran = 'REG' . $year . str_pad($lastNumber, 4, '0', STR_PAD_LEFT);

            // 3. CREATE PENDAFTARAN SERTIFIKASI dengan Skema langsung SIAP_ASESMEN
            $pendaftaran = PendaftaranSertifikasi::create([
                'pra_pendaftaran_id' => $praPendaftaran->id,
                'user_id' => $user->id,
                'skema_sertifikasi_id' => $validated['skema_sertifikasi_id'],
                'nama_lengkap' => $praPendaftaran->nama_lengkap,
                'email' => $praPendaftaran->email,
                'no_hp' => $praPendaftaran->no_hp,
                'tipe_peserta' => $praPendaftaran->tipe_peserta,
                'nik' => $praPendaftaran->nik,
                'nim' => $praPendaftaran->nim,
                'institusi' => $praPendaftaran->institusi,
                'nomor_pendaftaran' => $nomorPendaftaran,
                'tanggal_daftar' => now(),
                'status' => PendaftaranSertifikasi::STATUS_SIAP_ASESMEN, // Langsung SIAP_ASESMEN karena skema sudah ditetapkan
                'catatan_admin' => 'Pendaftaran dibuat dari pra-pendaftaran yang telah diverifikasi dengan skema: ' . 
                    SkemaSertifikasi::find($validated['skema_sertifikasi_id'])->nama_skema,
            ]);

            // 4. KIRIM EMAIL: Skema Ditetapkan - Siap Asesmen
            try {
                \Mail::to($pendaftaran->email)->send(
                    new \App\Mail\SertifikasiSkemaDitetapkan($pendaftaran)
                );
                \Log::info('[PendaftaranAdmin] Email Skema Ditetapkan sent', [
                    'pendaftaran_id' => $pendaftaran->id,
                    'skema_id' => $validated['skema_sertifikasi_id'],
                ]);
            } catch (\Exception $emailError) {
                \Log::error('[PendaftaranAdmin] Failed to send Skema Ditetapkan email', [
                    'pendaftaran_id' => $pendaftaran->id,
                    'error' => $emailError->getMessage(),
                ]);
            }

            // 5. KIRIM EMAIL KREDENSIAL (hanya jika user baru)
            if ($isNewUser) {
                try {
                    \Mail::to($user->email)->send(
                        new \App\Mail\UserCredentials($user, $pendaftaran, 'password123')
                    );
                } catch (\Exception $emailError) {
                    \Log::error('[PendaftaranAdmin] Failed to send credentials email', [
                        'user_id' => $user->id,
                        'error' => $emailError->getMessage(),
                    ]);
                }
            }

            // 6. Audit log
            AuditLog::log(
                AuditLog::ACTION_CREATE,
                AuditLog::MODULE_PENDAFTARAN,
                "Pendaftaran sertifikasi dibuat dari pra-pendaftaran: {$praPendaftaran->nomor_pra_pendaftaran} dengan skema ditetapkan",
                $pendaftaran,
                null,
                $pendaftaran->toArray(),
                [
                    'event' => 'pendaftaran_created_with_skema',
                    'pra_pendaftaran_id' => $praPendaftaran->id,
                    'skema_id' => $validated['skema_sertifikasi_id'],
                    'created_by' => auth()->id(),
                ]
            );

            DB::commit();

            $skema = SkemaSertifikasi::find($validated['skema_sertifikasi_id']);
            return redirect()->route('adminui.pendaftaran-sertifikasi.show', $pendaftaran->id)
                ->with('success', 
                    "Pendaftaran sertifikasi berhasil dibuat dengan nomor: {$pendaftaran->nomor_pendaftaran}. " .
                    "Skema '{$skema->nama_skema}' telah ditetapkan. Status: SIAP ASESMEN."
                );

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('[PendaftaranAdmin] Failed to create pendaftaran from pra-pendaftaran', [
                'pra_pendaftaran_id' => $praPendaftaranId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 
                'Gagal membuat pendaftaran sertifikasi: ' . $e->getMessage()
            );
        }
    }

    /**
     * Display the specified pendaftaran.
     */
    public function show($id)
    {
        $pendaftaran = PendaftaranSertifikasi::with(['user', 'skemaSertifikasi.unitKompetensi'])
            ->findOrFail($id);
        
        // Get active skema list for dropdown
        $skemaList = SkemaSertifikasi::where('aktif', true)
            ->orderBy('nama_skema')
            ->get();
        
        return view('adminui.pendaftaran-sertifikasi.show', compact('pendaftaran', 'skemaList'));
    }

    /**
     * Assign skema sertifikasi ke pendaftaran.
     */
    public function assignSkema(Request $request, $id)
    {
        $pendaftaran = PendaftaranSertifikasi::findOrFail($id);
        
        // Validasi
        $validated = $request->validate([
            'skema_sertifikasi_id' => 'required|exists:skema_sertifikasi,id',
        ], [
            'skema_sertifikasi_id.required' => 'Skema sertifikasi wajib dipilih.',
            'skema_sertifikasi_id.exists' => 'Skema sertifikasi tidak valid.',
        ]);
        
        // Update pendaftaran
        $pendaftaran->update([
            'skema_sertifikasi_id' => $validated['skema_sertifikasi_id'],
            'status' => PendaftaranSertifikasi::STATUS_SIAP_ASESMEN,
        ]);
        
        $skema = SkemaSertifikasi::find($validated['skema_sertifikasi_id']);
        
        // EVENT 1: KIRIM EMAIL - SKEMA DITETAPKAN (SIAP ASESMEN)
        try {
            \Mail::to($pendaftaran->email)->send(
                new \App\Mail\SertifikasiSkemaDitetapkan($pendaftaran)
            );
            \Log::info('Email Skema Ditetapkan sent', [
                'pendaftaran_id' => $pendaftaran->id,
                'email' => $pendaftaran->email,
                'skema_id' => $skema->id,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send Skema Ditetapkan email', [
                'pendaftaran_id' => $pendaftaran->id,
                'error' => $e->getMessage(),
            ]);
        }
        
        return redirect()
            ->back()
            ->with('success', "Skema '{$skema->nama_skema}' berhasil ditetapkan. Status diubah ke SIAP ASESMEN.");
    }

    /**
     * Verifikasi pendaftaran.
     */
    public function verifikasi(Request $request, $id)
    {
        $pendaftaran = PendaftaranSertifikasi::findOrFail($id);
        
        if (!$pendaftaran->isDiajukan()) {
            return back()->with('error', 'Pendaftaran tidak dalam status diajukan.');
        }
        
        $pendaftaran->update([
            'status' => PendaftaranSertifikasi::STATUS_DIVERIFIKASI,
            'catatan_admin' => $request->catatan_admin,
        ]);
        
        return redirect()
            ->route('adminui.pendaftaran-sertifikasi.index')
            ->with('success', "Pendaftaran {$pendaftaran->nomor_pendaftaran} berhasil diverifikasi.");
    }

    /**
     * Tolak pendaftaran.
     */
    public function tolak(Request $request, $id)
    {
        $pendaftaran = PendaftaranSertifikasi::findOrFail($id);
        
        if (!$pendaftaran->isDiajukan()) {
            return back()->with('error', 'Pendaftaran tidak dalam status diajukan.');
        }
        
        $validated = $request->validate([
            'catatan_admin' => 'required|string|max:1000',
        ], [
            'catatan_admin.required' => 'Alasan penolakan wajib diisi.',
        ]);
        
        $pendaftaran->update([
            'status' => PendaftaranSertifikasi::STATUS_DITOLAK,
            'catatan_admin' => $validated['catatan_admin'],
        ]);
        
        return redirect()
            ->route('adminui.pendaftaran-sertifikasi.index')
            ->with('success', "Pendaftaran {$pendaftaran->nomor_pendaftaran} telah ditolak.");
    }

    /**
     * Export Audit Evidence PDF for Pendaftaran Sertifikasi.
     * 
     * Menghasilkan dokumen PDF bukti audit resmi yang mencakup:
     * - Identitas peserta/asesi
     * - Informasi skema sertifikasi
     * - Timeline status pendaftaran
     * - Riwayat audit log
     * - Informasi integritas data (hash, UUID)
     * 
     * Sesuai dengan BNSP & ISO 17024 untuk keperluan audit/arsip.
     */
    public function exportAuditEvidence(Request $request, $id)
    {
        $pendaftaran = PendaftaranSertifikasi::with([
            'praPendaftaran',
            'user',
            'skemaSertifikasi.unitKompetensi',
            'asesmen',
            'keputusan',
            'sertifikat',
        ])->findOrFail($id);

        // Get related audit logs for this pendaftaran
        $auditLogs = AuditLog::where(function ($query) use ($pendaftaran) {
            $query->where('model_type', PendaftaranSertifikasi::class)
                  ->where('model_id', $pendaftaran->id);
        })->orWhere(function ($query) use ($pendaftaran) {
            $query->where('reference_number', $pendaftaran->nomor_pendaftaran);
        })->orderBy('created_at', 'desc')->take(20)->get();

        // Generate document metadata
        $documentUuid = Str::uuid()->toString();
        $documentNumber = 'AUD-PEND-' . date('Ymd') . '-' . str_pad($pendaftaran->id, 5, '0', STR_PAD_LEFT);
        $generatedAt = now();
        
        // Create integrity hash
        $hashData = json_encode([
            'pendaftaran_id' => $pendaftaran->id,
            'nomor_pendaftaran' => $pendaftaran->nomor_pendaftaran,
            'status' => $pendaftaran->status,
            'created_at' => $pendaftaran->created_at->toIso8601String(),
            'updated_at' => $pendaftaran->updated_at->toIso8601String(),
            'document_uuid' => $documentUuid,
            'generated_at' => $generatedAt->toIso8601String(),
        ]);
        $integrityHash = hash('sha256', $hashData);

        // Build status timeline
        $statusTimeline = $this->buildStatusTimeline($pendaftaran);

        // Log PDF generation
        AuditLog::log(
            AuditLog::ACTION_VIEW,
            AuditLog::MODULE_PENDAFTARAN,
            "Mengunduh PDF Audit Evidence untuk pendaftaran {$pendaftaran->nomor_pendaftaran}",
            $pendaftaran,
            null,
            null,
            [
                'event' => 'audit_pdf_generated_pendaftaran',
                'document_uuid' => $documentUuid,
                'document_number' => $documentNumber,
                'integrity_hash' => $integrityHash,
            ]
        );

        // Generate PDF
        $pdf = Pdf::loadView('audit.pdf.pendaftaran-sertifikasi', [
            'pendaftaran' => $pendaftaran,
            'auditLogs' => $auditLogs,
            'documentNumber' => $documentNumber,
            'documentUuid' => $documentUuid,
            'integrityHash' => $integrityHash,
            'generatedAt' => $generatedAt,
            'statusTimeline' => $statusTimeline,
        ]);

        // PDF settings
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif',
        ]);

        $filename = "Audit_Evidence_{$pendaftaran->nomor_pendaftaran}_{$generatedAt->format('Ymd_His')}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Build status timeline for pendaftaran.
     */
    private function buildStatusTimeline(PendaftaranSertifikasi $pendaftaran): array
    {
        $timeline = [];
        
        // Step 1: Pendaftaran Dibuat
        $timeline[] = [
            'step' => 1,
            'label' => 'Pendaftaran Dibuat',
            'status' => 'completed',
            'date' => $pendaftaran->created_at,
            'icon' => 'fas fa-file-alt',
            'description' => 'Pendaftaran sertifikasi berhasil dibuat di sistem',
        ];

        // Step 2: Diajukan
        if (in_array($pendaftaran->status, [
            PendaftaranSertifikasi::STATUS_DIAJUKAN,
            PendaftaranSertifikasi::STATUS_DIVERIFIKASI,
            PendaftaranSertifikasi::STATUS_SIAP_ASESMEN,
            PendaftaranSertifikasi::STATUS_MENUNGGU_KEPUTUSAN,
            PendaftaranSertifikasi::STATUS_KOMPETEN_FINAL,
            PendaftaranSertifikasi::STATUS_BELUM_KOMPETEN_FINAL,
        ])) {
            $timeline[] = [
                'step' => 2,
                'label' => 'Diajukan',
                'status' => 'completed',
                'date' => $pendaftaran->updated_at,
                'icon' => 'fas fa-paper-plane',
                'description' => 'Pendaftaran telah diajukan untuk verifikasi',
            ];
        } elseif ($pendaftaran->status === PendaftaranSertifikasi::STATUS_DITOLAK) {
            $timeline[] = [
                'step' => 2,
                'label' => 'Ditolak',
                'status' => 'rejected',
                'date' => $pendaftaran->updated_at,
                'icon' => 'fas fa-times-circle',
                'description' => $pendaftaran->catatan_admin ?? 'Pendaftaran ditolak oleh admin',
            ];
        } else {
            $timeline[] = [
                'step' => 2,
                'label' => 'Menunggu Pengajuan',
                'status' => 'pending',
                'date' => null,
                'icon' => 'fas fa-clock',
                'description' => 'Menunggu pengajuan oleh peserta',
            ];
        }

        // Step 3: Diverifikasi / Siap Asesmen
        if (in_array($pendaftaran->status, [
            PendaftaranSertifikasi::STATUS_DIVERIFIKASI,
            PendaftaranSertifikasi::STATUS_SIAP_ASESMEN,
            PendaftaranSertifikasi::STATUS_MENUNGGU_KEPUTUSAN,
            PendaftaranSertifikasi::STATUS_KOMPETEN_FINAL,
            PendaftaranSertifikasi::STATUS_BELUM_KOMPETEN_FINAL,
        ])) {
            $timeline[] = [
                'step' => 3,
                'label' => 'Diverifikasi / Siap Asesmen',
                'status' => 'completed',
                'date' => $pendaftaran->updated_at,
                'icon' => 'fas fa-check-circle',
                'description' => 'Pendaftaran telah diverifikasi dan siap untuk asesmen',
            ];
        } elseif ($pendaftaran->status !== PendaftaranSertifikasi::STATUS_DITOLAK) {
            $timeline[] = [
                'step' => 3,
                'label' => 'Verifikasi',
                'status' => 'pending',
                'date' => null,
                'icon' => 'fas fa-clock',
                'description' => 'Menunggu verifikasi admin',
            ];
        }

        // Step 4: Asesmen (if exists)
        if ($pendaftaran->asesmen) {
            $timeline[] = [
                'step' => 4,
                'label' => 'Asesmen Selesai',
                'status' => 'completed',
                'date' => $pendaftaran->asesmen->created_at,
                'icon' => 'fas fa-clipboard-check',
                'description' => 'Proses asesmen telah dilaksanakan',
            ];
        } elseif ($pendaftaran->isSiapAsesmen()) {
            $timeline[] = [
                'step' => 4,
                'label' => 'Menunggu Asesmen',
                'status' => 'in_progress',
                'date' => null,
                'icon' => 'fas fa-hourglass-half',
                'description' => 'Menunggu pelaksanaan asesmen',
            ];
        }

        // Step 5: Keputusan (if exists)
        if ($pendaftaran->keputusan) {
            $timeline[] = [
                'step' => 5,
                'label' => 'Keputusan: ' . ($pendaftaran->keputusan->hasil === 'kompeten' ? 'Kompeten' : 'Belum Kompeten'),
                'status' => $pendaftaran->keputusan->hasil === 'kompeten' ? 'completed' : 'rejected',
                'date' => $pendaftaran->keputusan->created_at,
                'icon' => $pendaftaran->keputusan->hasil === 'kompeten' ? 'fas fa-trophy' : 'fas fa-redo',
                'description' => 'Keputusan sertifikasi telah ditetapkan oleh komite teknis',
            ];
        }

        // Step 6: Sertifikat (if exists)
        if ($pendaftaran->sertifikat) {
            $timeline[] = [
                'step' => 6,
                'label' => 'Sertifikat Diterbitkan',
                'status' => 'completed',
                'date' => $pendaftaran->sertifikat->tanggal_terbit,
                'icon' => 'fas fa-certificate',
                'description' => 'Sertifikat kompetensi telah diterbitkan',
            ];
        }

        return $timeline;
    }
}

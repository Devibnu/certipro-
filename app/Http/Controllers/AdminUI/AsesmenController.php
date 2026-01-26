<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\AsesmenDetail;
use App\Models\AuditLog;
use App\Models\Kuk;
use App\Models\PendaftaranSertifikasi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AsesmenController extends Controller
{
    /**
     * Display list of pendaftaran ready for assessment (status = siap_asesmen)
     * Also shows completed asesmens
     * 
     * CRITICAL: ZERO TOLERANCE untuk 500 error.
     * HARUS bisa tampilkan halaman meskipun ada data corrupt.
     */
    public function index(Request $request)
    {
        try {
            \Log::info('[ASESMEN INDEX] Loading index page', [
                'user_id' => auth()->id(),
                'search' => $request->search ?? null,
                'sampling' => $request->sampling ?? null,
            ]);

            // ============================================================
            // STEP 1: Query pendaftaran siap asesmen dengan DEFENSIVE MODE
            // ============================================================
            $pendaftarans = collect();
            try {
                $query = PendaftaranSertifikasi::query();
                
                // Load relations SAFELY - jika gagal, relation jadi null (bukan crash)
                try {
                    $query->with(['user', 'praPendaftaran', 'skemaSertifikasi']);
                } catch (\Throwable $e) {
                    \Log::warning('[ASESMEN INDEX] Failed to eager load pendaftaran relations', [
                        'error' => $e->getMessage(),
                    ]);
                    // Continue without eager loading - akan load on-demand
                }
                
                $query->where('status', PendaftaranSertifikasi::STATUS_SIAP_ASESMEN);
                
                // Search functionality dengan error handling
                if ($request->filled('search')) {
                    $search = $request->search;
                    try {
                        $query->where(function ($q) use ($search) {
                            $q->where('nomor_pendaftaran', 'like', "%{$search}%")
                                ->orWhereHas('user', function ($q2) use ($search) {
                                    $q2->where('name', 'like', "%{$search}%");
                                })
                                ->orWhereHas('skemaSertifikasi', function ($q2) use ($search) {
                                    $q2->where('nama_skema', 'like', "%{$search}%");
                                });
                        });
                    } catch (\Throwable $e) {
                        \Log::error('[ASESMEN INDEX] Search query failed', [
                            'search' => $search,
                            'error' => $e->getMessage(),
                        ]);
                        // Continue without search filter
                    }
                }
                
                $pendaftarans = $query->orderBy('created_at', 'desc')->paginate(10);
                
            } catch (\Throwable $e) {
                \Log::error('[ASESMEN INDEX] Failed to load pendaftarans', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                // Set empty collection agar view tidak crash
                $pendaftarans = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
            }

            // ============================================================
            // STEP 2: Query completed asesmens dengan DEFENSIVE MODE
            // ============================================================
            $asesmens = collect();
            try {
                $asesmenQuery = Asesmen::query();
                
                // Load relations SAFELY
                try {
                    $asesmenQuery->with([
                        'pendaftaran.user', 
                        'pendaftaran.praPendaftaran',
                        'pendaftaran.skemaSertifikasi',
                        'asesor',
                        'sampledByUser',
                    ]);
                } catch (\Throwable $e) {
                    \Log::warning('[ASESMEN INDEX] Failed to eager load asesmen relations', [
                        'error' => $e->getMessage(),
                    ]);
                    // Continue without eager loading
                }
                
                // Filter by sampling status dengan error handling
                if ($request->filled('sampling')) {
                    try {
                        if ($request->sampling === 'sampled') {
                            $asesmenQuery->sampled();
                        } elseif ($request->sampling === 'not_sampled') {
                            $asesmenQuery->notSampled();
                        }
                    } catch (\Throwable $e) {
                        \Log::error('[ASESMEN INDEX] Sampling filter failed', [
                            'sampling' => $request->sampling,
                            'error' => $e->getMessage(),
                        ]);
                        // Continue without sampling filter
                    }
                }
                
                $asesmens = $asesmenQuery->orderBy('created_at', 'desc')
                    ->paginate(10, ['*'], 'asesmen_page');
                    
            } catch (\Throwable $e) {
                \Log::error('[ASESMEN INDEX] Failed to load asesmens', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                // Set empty paginator agar view tidak crash
                $asesmens = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1, [
                    'path' => $request->url(),
                    'pageName' => 'asesmen_page',
                ]);
            }

            // ============================================================
            // STEP 3: Return view dengan data (kosong atau berisi)
            // ============================================================
            return view('adminui.asesmen.index', compact('pendaftarans', 'asesmens'));
            
        } catch (\Throwable $e) {
            // LAST RESORT: Jika SEMUA gagal, tampilkan halaman kosong
            \Log::critical('[ASESMEN INDEX] CRITICAL ERROR - Complete failure', [
                'url' => $request->fullUrl(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);
            
            // Return view dengan data kosong tapi halaman tetap bisa dibuka
            return view('adminui.asesmen.index', [
                'pendaftarans' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10),
                'asesmens' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1, [
                    'path' => $request->url(),
                    'pageName' => 'asesmen_page',
                ]),
            ])->with('error', 'Terjadi kesalahan saat memuat data. Tim teknis telah diberitahu.');
        }
    }

    /**
     * Show assessment form for a specific pendaftaran
     * 
     * CRITICAL: Handle missing relations gracefully
     */
    public function mulaiAsesmen($pendaftaranId)
    {
        try {
            \Log::info('[ASESMEN MULAI] Starting assessment form', [
                'pendaftaran_id' => $pendaftaranId,
                'user_id' => auth()->id(),
            ]);

            // Load pendaftaran dengan error handling
            $pendaftaran = PendaftaranSertifikasi::find($pendaftaranId);
            
            if (!$pendaftaran) {
                \Log::warning('[ASESMEN MULAI] Pendaftaran not found', [
                    'pendaftaran_id' => $pendaftaranId,
                ]);
                return redirect()->route('adminui.asesmen.index')
                    ->with('error', 'Data pendaftaran tidak ditemukan.');
            }

            // Load relations safely
            try {
                $pendaftaran->load('user', 'skemaSertifikasi.unitKompetensi.kuks');
            } catch (\Throwable $e) {
                \Log::error('[ASESMEN MULAI] Failed to load relations', [
                    'pendaftaran_id' => $pendaftaranId,
                    'error' => $e->getMessage(),
                ]);
                // Continue - akan handle di validation
            }
            
            // Validate status is siap_asesmen
            if ($pendaftaran->status !== PendaftaranSertifikasi::STATUS_SIAP_ASESMEN) {
                \Log::warning('[ASESMEN MULAI] Invalid status', [
                    'pendaftaran_id' => $pendaftaranId,
                    'status' => $pendaftaran->status,
                ]);
                return redirect()->route('adminui.asesmen.index')
                    ->with('error', 'Pendaftaran ini tidak dalam status siap asesmen.');
            }
            
            // Check if asesmen already exists
            try {
                $existingAsesmen = Asesmen::where('pendaftaran_id', $pendaftaran->id)->first();
                if ($existingAsesmen) {
                    \Log::info('[ASESMEN MULAI] Asesmen already exists, redirecting', [
                        'asesmen_id' => $existingAsesmen->id,
                    ]);
                    return redirect()->route('adminui.asesmen.show', $existingAsesmen->id)
                        ->with('info', 'Asesmen untuk pendaftaran ini sudah dilakukan.');
                }
            } catch (\Throwable $e) {
                \Log::error('[ASESMEN MULAI] Error checking existing asesmen', [
                    'pendaftaran_id' => $pendaftaranId,
                    'error' => $e->getMessage(),
                ]);
                // Continue - better to show form than crash
            }
            
            // Load unit kompetensi dengan defensive mode
            $unitKompetensi = collect();
            try {
                if ($pendaftaran->skemaSertifikasi) {
                    $unitKompetensi = $pendaftaran->skemaSertifikasi->unitKompetensi()->with('kuks')->get();
                }
            } catch (\Throwable $e) {
                \Log::error('[ASESMEN MULAI] Failed to load unit kompetensi', [
                    'pendaftaran_id' => $pendaftaranId,
                    'skema_id' => optional($pendaftaran->skemaSertifikasi)->id,
                    'error' => $e->getMessage(),
                ]);
                // Return empty collection - form will show empty state
            }

            // Validate kita punya data minimum untuk asesmen
            if ($unitKompetensi->isEmpty()) {
                \Log::warning('[ASESMEN MULAI] No unit kompetensi found', [
                    'pendaftaran_id' => $pendaftaranId,
                    'skema_id' => optional($pendaftaran->skemaSertifikasi)->id,
                ]);
                return redirect()->route('adminui.asesmen.index')
                    ->with('error', 'Tidak ada unit kompetensi yang tersedia untuk skema ini. Silakan hubungi administrator.');
            }
            
            return view('adminui.asesmen.form', compact('pendaftaran', 'unitKompetensi'));
            
        } catch (\Throwable $e) {
            \Log::critical('[ASESMEN MULAI] CRITICAL ERROR', [
                'pendaftaran_id' => $pendaftaranId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->route('adminui.asesmen.index')
                ->with('error', 'Terjadi kesalahan sistem. Tim teknis telah diberitahu.');
        }
    }

    /**
     * Save assessment results
     */
    public function simpanAsesmen(Request $request, $pendaftaranId)
    {
        $pendaftaran = PendaftaranSertifikasi::with([
            'skemaSertifikasi.unitKompetensi.kuks'
        ])->findOrFail($pendaftaranId);
        
        // Validate status is siap_asesmen
        if ($pendaftaran->status !== PendaftaranSertifikasi::STATUS_SIAP_ASESMEN) {
            return redirect()->route('adminui.asesmen.index')
                ->with('error', 'Pendaftaran ini tidak dalam status siap asesmen.');
        }
        
        // Validate request
        $request->validate([
            'metode_asesmen' => 'required|in:observasi,portofolio,wawancara',
            'catatan_asesor' => 'nullable|string|max:2000',
            'hasil' => 'required|array',
            'hasil.*' => 'required|in:kompeten,belum_kompeten',
            'catatan' => 'nullable|array',
            'catatan.*' => 'nullable|string|max:1000',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Create asesmen record
            $asesmen = Asesmen::create([
                'pendaftaran_id' => $pendaftaran->id,
                'asesor_id' => Auth::id(),
                'tanggal_asesmen' => now(),
                'metode_asesmen' => $request->metode_asesmen,
                'catatan_asesor' => $request->catatan_asesor,
                'status' => Asesmen::STATUS_SELESAI,
            ]);
            
            // Track if any KUK is belum_kompeten
            $hasBelumKompeten = false;
            
            // Create asesmen detail for each KUK
            foreach ($request->hasil as $kukId => $hasil) {
                // Get KUK with unit kompetensi
                $kuk = Kuk::with('unitKompetensi')->findOrFail($kukId);
                
                AsesmenDetail::create([
                    'asesmen_id' => $asesmen->id,
                    'unit_kompetensi_id' => $kuk->unit_kompetensi_id,
                    'kuk_id' => $kukId,
                    'hasil' => $hasil,
                    'catatan' => $request->catatan[$kukId] ?? null,
                ]);
                
                if ($hasil === AsesmenDetail::HASIL_BELUM_KOMPETEN) {
                    $hasBelumKompeten = true;
                }
            }
            
            // Update pendaftaran status based on assessment result
            if ($hasBelumKompeten) {
                $pendaftaran->update([
                    'status' => PendaftaranSertifikasi::STATUS_BELUM_KOMPETEN
                ]);
            } else {
                $pendaftaran->update([
                    'status' => PendaftaranSertifikasi::STATUS_MENUNGGU_KEPUTUSAN
                ]);
            }
            
            DB::commit();
            
            // EVENT 2: KIRIM EMAIL - ASESMEN SELESAI
            try {
                \Mail::to($pendaftaran->email)->send(
                    new \App\Mail\AsesmenSelesai($asesmen)
                );
                \Log::info('Email Asesmen Selesai sent', [
                    'asesmen_id' => $asesmen->id,
                    'pendaftaran_id' => $pendaftaran->id,
                    'email' => $pendaftaran->email,
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to send Asesmen Selesai email', [
                    'asesmen_id' => $asesmen->id,
                    'error' => $e->getMessage(),
                ]);
            }
            
            return redirect()->route('adminui.asesmen.show', $asesmen->id)
                ->with('success', 'Asesmen berhasil disimpan.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show assessment details (read-only)
     * 
     * CRITICAL: ZERO TOLERANCE untuk 500 error.
     * SEMUA error harus ditangani dengan graceful degradation.
     */
    public function show($id)
    {
        try {
            // ============================================================
            // STEP 1: Load asesmen dengan ULTRA-SAFE query
            // ============================================================
            \Log::info('[ASESMEN DETAIL] Loading asesmen', ['id' => $id]);
            
            // First, get base asesmen
            $asesmen = Asesmen::find($id);
            
            if (!$asesmen) {
                \Log::warning('[ASESMEN DETAIL] Asesmen not found', ['id' => $id]);
                return view('adminui.asesmen.error', [
                    'title' => 'Asesmen Tidak Ditemukan',
                    'message' => 'Data asesmen dengan ID ' . $id . ' tidak ditemukan dalam sistem.',
                    'backUrl' => route('adminui.asesmen.index'),
                ]);
            }
            
            // Load each relation INDIVIDUALLY with error handling
            try {
                $asesmen->load('pendaftaran.user', 'pendaftaran.praPendaftaran', 'pendaftaran.skemaSertifikasi');
            } catch (\Throwable $e) {
                \Log::error('[ASESMEN DETAIL] Failed to load pendaftaran relations', [
                    'asesmen_id' => $id,
                    'error' => $e->getMessage(),
                ]);
            }
            
            try {
                $asesmen->load('asesor');
            } catch (\Throwable $e) {
                \Log::error('[ASESMEN DETAIL] Failed to load asesor', [
                    'asesmen_id' => $id,
                    'error' => $e->getMessage(),
                ]);
            }
            
            try {
                $asesmen->load('details.unitKompetensi', 'details.kuk');
            } catch (\Throwable $e) {
                \Log::error('[ASESMEN DETAIL] Failed to load details', [
                    'asesmen_id' => $id,
                    'error' => $e->getMessage(),
                ]);
            }
            
            try {
                $asesmen->load('evidences.uploader', 'evidences.kuk');
            } catch (\Throwable $e) {
                \Log::error('[ASESMEN DETAIL] Failed to load evidences', [
                    'asesmen_id' => $id,
                    'error' => $e->getMessage(),
                ]);
            }
            
            try {
                $asesmen->load('sampledByUser', 'pendaftaran.keputusan');
            } catch (\Throwable $e) {
                \Log::error('[ASESMEN DETAIL] Failed to load sampling/keputusan', [
                    'asesmen_id' => $id,
                    'error' => $e->getMessage(),
                ]);
            }
            
            // ============================================================
            // STEP 2: Validasi data critical dengan detailed logging
            // ============================================================
            $errors = [];
            
            if (!$asesmen->pendaftaran) {
                $errors[] = 'Relasi pendaftaran tidak ditemukan';
                \Log::error('[ASESMEN DETAIL] Missing pendaftaran', [
                    'asesmen_id' => $id,
                    'pendaftaran_id' => $asesmen->pendaftaran_id,
                ]);
            }
            
            if ($asesmen->pendaftaran && !$asesmen->pendaftaran->skemaSertifikasi) {
                \Log::warning('[ASESMEN DETAIL] Missing skema sertifikasi', [
                    'asesmen_id' => $id,
                    'pendaftaran_id' => $asesmen->pendaftaran_id,
                    'skema_id' => $asesmen->pendaftaran->skema_sertifikasi_id ?? null,
                ]);
            }
            
            if (!$asesmen->asesor) {
                \Log::warning('[ASESMEN DETAIL] Missing asesor', [
                    'asesmen_id' => $id,
                    'asesor_id' => $asesmen->asesor_id,
                ]);
            }
            
            // Jika ada critical error (pendaftaran missing), tampilkan error page
            if (!empty($errors)) {
                return view('adminui.asesmen.error', [
                    'title' => 'Data Asesmen Tidak Lengkap',
                    'message' => 'Data asesmen belum lengkap atau terjadi inkonsistensi data: ' . implode(', ', $errors),
                    'details' => $errors,
                    'backUrl' => route('adminui.asesmen.index'),
                ]);
            }
            
            // ============================================================
            // STEP 4: Group details dengan error handling per item
            // ============================================================
            $detailsByUnit = collect();
            
            try {
                if ($asesmen->details && $asesmen->details->count() > 0) {
                    // Filter out details with NULL unit_kompetensi_id
                    $validDetails = $asesmen->details->filter(function ($detail) use ($id) {
                        if (!$detail->unit_kompetensi_id) {
                            \Log::warning('[ASESMEN DETAIL] Detail without unit kompetensi', [
                                'asesmen_id' => $id,
                                'detail_id' => $detail->id,
                            ]);
                            return false;
                        }
                        return true;
                    });
                    
                    $detailsByUnit = $validDetails->groupBy('unit_kompetensi_id');
                }
            } catch (\Throwable $e) {
                \Log::error('[ASESMEN DETAIL] Error grouping details', [
                    'asesmen_id' => $id,
                    'error' => $e->getMessage(),
                ]);
                $detailsByUnit = collect();
            }
            
            // Warning jika tidak ada detail penilaian
            if ($detailsByUnit->isEmpty()) {
                \Log::warning('[ASESMEN DETAIL] No valid details found', ['id' => $id]);
                session()->flash('warning', 'Detail penilaian belum tersedia atau belum lengkap untuk asesmen ini.');
            }
            
            \Log::info('[ASESMEN DETAIL] Successfully loaded', [
                'id' => $id,
                'has_pendaftaran' => $asesmen->pendaftaran ? 'yes' : 'no',
                'details_count' => $detailsByUnit->count(),
            ]);
            
            // ============================================================
            // STEP 5: Return view dengan data yang sudah divalidasi
            // ============================================================
            return view('adminui.asesmen.show', compact('asesmen', 'detailsByUnit'));
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Seharusnya tidak terjadi karena kita pakai find(), tapi tetap handle
            \Log::error('[ASESMEN DETAIL ERROR] Model not found', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            return view('adminui.asesmen.error', [
                'title' => 'Asesmen Tidak Ditemukan',
                'message' => 'Data asesmen tidak ditemukan dalam sistem.',
                'backUrl' => route('adminui.asesmen.index'),
            ]);
            
        } catch (\Throwable $e) {
            // CATCH ALL - Menangkap semua jenis error termasuk fatal error
            \Log::error('[ASESMEN DETAIL ERROR] Critical error', [
                'asesmen_id' => $id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // TETAP tampilkan halaman, JANGAN redirect
            return view('adminui.asesmen.error', [
                'title' => 'Terjadi Kesalahan',
                'message' => 'Terjadi kesalahan saat memuat detail asesmen. Tim teknis telah diberitahu.',
                'technical' => config('app.debug') ? $e->getMessage() : null,
                'backUrl' => route('adminui.asesmen.index'),
            ]);
        }
    }

    /**
     * Export Audit Evidence PDF for Asesmen Kompetensi.
     * 
     * Menghasilkan dokumen PDF bukti audit resmi yang mencakup:
     * - Identitas peserta/asesi
     * - Identitas asesor
     * - Rincian penilaian per Unit Kompetensi dan KUK
     * - Rekap hasil asesmen
     * - Riwayat audit log
     * - Informasi integritas data (hash, UUID)
     * 
     * Sesuai dengan BNSP & ISO 17024 untuk keperluan audit/arsip.
     * 
     * DEFENSIVE CODING: Menangani edge case data tidak lengkap.
     */
    public function exportAuditEvidence(Request $request, $id)
    {
        try {
            $asesmen = Asesmen::with([
                'pendaftaran.user',
                'pendaftaran.praPendaftaran',
                'pendaftaran.skemaSertifikasi.unitKompetensi.kuks',
                'asesor',
                'details.unitKompetensi',
                'details.kuk',
                'evidences.uploader', // Load evidence for Audit PDF
                'evidences.kuk',
                'sampledByUser', // Load sampling user for QC section in PDF
            ])->findOrFail($id);
            
            // Validasi data critical
            if (!$asesmen->pendaftaran) {
                return redirect()->route('adminui.asesmen.show', $id)
                    ->with('error', 'Tidak dapat mengekspor PDF. Data pendaftaran tidak lengkap.');
            }

            // Group details by unit kompetensi
            $detailsByUnit = $asesmen->details->groupBy('unit_kompetensi_id');
            
            // Group evidence by KUK
            $evidenceByKuk = $asesmen->evidences->groupBy('kuk_id');

            // Calculate summary statistics
            $summary = $this->calculateAsesmenSummary($asesmen, $detailsByUnit);

            // Get related audit logs
            $auditLogs = AuditLog::where(function ($query) use ($asesmen) {
                $query->where('model_type', Asesmen::class)
                      ->where('model_id', $asesmen->id);
            })->orWhere(function ($query) use ($asesmen) {
                if ($asesmen->pendaftaran) {
                    $query->where('reference_number', $asesmen->pendaftaran->nomor_pendaftaran);
                }
            })->orderBy('created_at', 'desc')->take(20)->get();

            // Generate document metadata
            $documentUuid = Str::uuid()->toString();
            $documentNumber = 'AUD-ASM-' . date('Ymd') . '-' . str_pad($asesmen->id, 5, '0', STR_PAD_LEFT);
            $generatedAt = now();
            
            // Create integrity hash
            $hashData = json_encode([
                'asesmen_id' => $asesmen->id,
                'pendaftaran_id' => $asesmen->pendaftaran_id,
                'nomor_pendaftaran' => $asesmen->pendaftaran?->nomor_pendaftaran,
                'asesor_id' => $asesmen->asesor_id,
                'tanggal_asesmen' => optional($asesmen->tanggal_asesmen)->toIso8601String(),
                'status' => $asesmen->status,
                'total_kompeten' => $summary['total_kompeten'] ?? 0,
                'total_belum_kompeten' => $summary['total_belum_kompeten'] ?? 0,
                'document_uuid' => $documentUuid,
                'generated_at' => $generatedAt->toIso8601String(),
            ]);
            $integrityHash = hash('sha256', $hashData);

            // Log PDF generation
            AuditLog::log(
                AuditLog::ACTION_VIEW,
                AuditLog::MODULE_ASESMEN,
                "Mengunduh PDF Audit Evidence Asesmen untuk pendaftaran {$asesmen->pendaftaran?->nomor_pendaftaran}",
                $asesmen,
                null,
                null,
                [
                    'event' => 'audit_pdf_generated_asesmen',
                    'document_uuid' => $documentUuid,
                    'document_number' => $documentNumber,
                    'integrity_hash' => $integrityHash,
                    'asesor_id' => $asesmen->asesor_id,
                    'nomor_pendaftaran' => $asesmen->pendaftaran?->nomor_pendaftaran,
                ]
            );

            // Generate PDF
            $pdf = Pdf::loadView('audit.pdf.asesmen-kompetensi', [
                'asesmen' => $asesmen,
                'detailsByUnit' => $detailsByUnit,
                'evidenceByKuk' => $evidenceByKuk,
                'summary' => $summary,
                'auditLogs' => $auditLogs,
                'documentNumber' => $documentNumber,
                'documentUuid' => $documentUuid,
                'integrityHash' => $integrityHash,
                'generatedAt' => $generatedAt,
            ]);

            // PDF settings
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

            $nomorPendaftaran = $asesmen->pendaftaran?->nomor_pendaftaran ?? 'UNKNOWN';
            $filename = "Audit_Evidence_Asesmen_{$nomorPendaftaran}_{$generatedAt->format('Ymd_His')}.pdf";

            return $pdf->download($filename);
        
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('adminui.asesmen.index')
                ->with('error', 'Asesmen tidak ditemukan.');
        } catch (\Exception $e) {
            \Log::error('Error exporting asesmen audit PDF', [
                'asesmen_id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            return redirect()->route('adminui.asesmen.show', $id)
                ->with('error', 'Terjadi kesalahan saat mengekspor PDF audit.');
        }
    }

    /**
     * Calculate asesmen summary statistics.
     */
    private function calculateAsesmenSummary(Asesmen $asesmen, $detailsByUnit): array
    {
        $totalUnit = $detailsByUnit->count();
        $totalKuk = $asesmen->details->count();
        $totalKompeten = $asesmen->details->where('hasil', AsesmenDetail::HASIL_KOMPETEN)->count();
        $totalBelumKompeten = $asesmen->details->where('hasil', AsesmenDetail::HASIL_BELUM_KOMPETEN)->count();

        // Calculate unit-level competency
        $unitKompeten = 0;
        $unitBelumKompeten = 0;
        
        foreach ($detailsByUnit as $unitId => $details) {
            $hasBelumKompeten = $details->where('hasil', AsesmenDetail::HASIL_BELUM_KOMPETEN)->count() > 0;
            if ($hasBelumKompeten) {
                $unitBelumKompeten++;
            } else {
                $unitKompeten++;
            }
        }

        // Determine conclusion
        $kesimpulan = $totalBelumKompeten === 0 ? 'LAYAK DITETAPKAN' : 'TIDAK LAYAK DITETAPKAN';

        return [
            'total_unit' => $totalUnit,
            'total_kuk' => $totalKuk,
            'total_kompeten' => $totalKompeten,
            'total_belum_kompeten' => $totalBelumKompeten,
            'unit_kompeten' => $unitKompeten,
            'unit_belum_kompeten' => $unitBelumKompeten,
            'kesimpulan' => $kesimpulan,
            'persentase_kompeten' => $totalKuk > 0 ? round(($totalKompeten / $totalKuk) * 100, 1) : 0,
        ];
    }
}

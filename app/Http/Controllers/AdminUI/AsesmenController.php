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
     */
    public function index(Request $request)
    {
        // Query for pendaftaran siap asesmen
        $query = PendaftaranSertifikasi::with(['user', 'praPendaftaran', 'skemaSertifikasi'])
            ->where('status', PendaftaranSertifikasi::STATUS_SIAP_ASESMEN);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_pendaftaran', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('skemaSertifikasi', function ($q2) use ($search) {
                        $q2->where('nama_skema', 'like', "%{$search}%");
                    });
            });
        }
        
        $pendaftarans = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Query for completed asesmens
        $asesmenQuery = Asesmen::with([
            'pendaftaran.user', 
            'pendaftaran.praPendaftaran',
            'pendaftaran.skemaSertifikasi',
            'asesor',
            'sampledByUser',
        ]);
        
        // Filter by sampling status (for Komite Teknis)
        if ($request->filled('sampling')) {
            if ($request->sampling === 'sampled') {
                $asesmenQuery->sampled();
            } elseif ($request->sampling === 'not_sampled') {
                $asesmenQuery->notSampled();
            }
        }
        
        $asesmens = $asesmenQuery->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'asesmen_page');
        
        return view('adminui.asesmen.index', compact('pendaftarans', 'asesmens'));
    }

    /**
     * Show assessment form for a specific pendaftaran
     */
    public function mulaiAsesmen($pendaftaranId)
    {
        $pendaftaran = PendaftaranSertifikasi::with([
            'user', 
            'skemaSertifikasi.unitKompetensi.kuks'
        ])->findOrFail($pendaftaranId);
        
        // Validate status is siap_asesmen
        if ($pendaftaran->status !== PendaftaranSertifikasi::STATUS_SIAP_ASESMEN) {
            return redirect()->route('adminui.asesmen.index')
                ->with('error', 'Pendaftaran ini tidak dalam status siap asesmen.');
        }
        
        // Check if asesmen already exists
        $existingAsesmen = Asesmen::where('pendaftaran_id', $pendaftaran->id)->first();
        if ($existingAsesmen) {
            return redirect()->route('adminui.asesmen.show', $existingAsesmen->id)
                ->with('info', 'Asesmen untuk pendaftaran ini sudah dilakukan.');
        }
        
        $unitKompetensi = $pendaftaran->skemaSertifikasi 
            ? $pendaftaran->skemaSertifikasi->unitKompetensi()->with('kuks')->get() 
            : collect();
        
        return view('adminui.asesmen.form', compact('pendaftaran', 'unitKompetensi'));
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
     */
    public function show($id)
    {
        $asesmen = Asesmen::with([
            'pendaftaran.user',
            'pendaftaran.praPendaftaran',
            'pendaftaran.skemaSertifikasi',
            'asesor',
            'details.unitKompetensi',
            'details.kuk',
            'evidences.uploader', // Load evidence with uploader for display
            'evidences.kuk',
            'sampledByUser', // Load sampling user for QC section
            'pendaftaran.keputusan', // Check if keputusan is locked
        ])->findOrFail($id);
        
        // Group details by unit kompetensi
        $detailsByUnit = $asesmen->details->groupBy('unit_kompetensi_id');
        
        return view('adminui.asesmen.show', compact('asesmen', 'detailsByUnit'));
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
     */
    public function exportAuditEvidence(Request $request, $id)
    {
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
            'tanggal_asesmen' => $asesmen->tanggal_asesmen?->toIso8601String(),
            'status' => $asesmen->status,
            'total_kompeten' => $summary['total_kompeten'],
            'total_belum_kompeten' => $summary['total_belum_kompeten'],
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

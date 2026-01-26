<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\AsesmenDetail;
use App\Models\AuditLog;
use App\Models\KeputusanSertifikasi;
use App\Models\PendaftaranSertifikasi;
use App\Models\Sertifikat;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class KeputusanSertifikasiController extends Controller
{
    /**
     * Display list of pendaftaran waiting for decision (status = menunggu_keputusan)
     */
    public function index(Request $request)
    {
        $query = PendaftaranSertifikasi::with(['user', 'skemaSertifikasi', 'asesmen.asesor'])
            ->where('status', PendaftaranSertifikasi::STATUS_MENUNGGU_KEPUTUSAN);
        
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
        
        return view('adminui.keputusan.index', compact('pendaftarans'));
    }

    /**
     * Show assessment review and decision form for a specific pendaftaran
     */
    public function show($pendaftaranId)
    {
        $pendaftaran = PendaftaranSertifikasi::with([
            'user', 
            'skemaSertifikasi',
            'asesmen.asesor',
            'asesmen.details.unitKompetensi',
            'asesmen.details.kuk',
            'keputusan.penetap'
        ])->findOrFail($pendaftaranId);
        
        // Check if there's already a locked decision
        $existingKeputusan = $pendaftaran->keputusan;
        if ($existingKeputusan && $existingKeputusan->isLocked()) {
            return view('adminui.keputusan.show', [
                'pendaftaran' => $pendaftaran,
                'asesmen' => $pendaftaran->asesmen,
                'detailsByUnit' => $pendaftaran->asesmen ? $pendaftaran->asesmen->details->groupBy('unit_kompetensi_id') : collect(),
                'keputusan' => $existingKeputusan,
                'isLocked' => true,
            ]);
        }
        
        // Validate status is menunggu_keputusan
        if ($pendaftaran->status !== PendaftaranSertifikasi::STATUS_MENUNGGU_KEPUTUSAN) {
            return redirect()->route('adminui.keputusan.index')
                ->with('error', 'Pendaftaran ini tidak dalam status menunggu keputusan.');
        }
        
        // Ensure asesmen exists
        if (!$pendaftaran->asesmen) {
            return redirect()->route('adminui.keputusan.index')
                ->with('error', 'Asesmen belum dilakukan untuk pendaftaran ini.');
        }
        
        $asesmen = $pendaftaran->asesmen;
        $detailsByUnit = $asesmen->details->groupBy('unit_kompetensi_id');
        
        return view('adminui.keputusan.show', [
            'pendaftaran' => $pendaftaran,
            'asesmen' => $asesmen,
            'detailsByUnit' => $detailsByUnit,
            'keputusan' => $existingKeputusan,
            'isLocked' => false,
        ]);
    }

    /**
     * Save the final decision
     */
    public function simpan(Request $request, $pendaftaranId)
    {
        $pendaftaran = PendaftaranSertifikasi::with(['asesmen', 'keputusan'])
            ->findOrFail($pendaftaranId);
        
        // Check if already locked
        if ($pendaftaran->keputusan && $pendaftaran->keputusan->isLocked()) {
            return redirect()->route('adminui.keputusan.show', $pendaftaran->id)
                ->with('error', 'Keputusan sudah dikunci dan tidak dapat diubah.');
        }
        
        // Validate status
        if ($pendaftaran->status !== PendaftaranSertifikasi::STATUS_MENUNGGU_KEPUTUSAN) {
            return redirect()->route('adminui.keputusan.index')
                ->with('error', 'Pendaftaran ini tidak dalam status menunggu keputusan.');
        }
        
        // Validate request
        $request->validate([
            'keputusan' => 'required|in:kompeten,belum_kompeten',
            'catatan_komite' => 'nullable|string|max:2000',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Create or update keputusan record
            $keputusan = KeputusanSertifikasi::updateOrCreate(
                ['pendaftaran_id' => $pendaftaran->id],
                [
                    'asesmen_id' => $pendaftaran->asesmen->id,
                    'keputusan' => $request->keputusan,
                    'catatan_komite' => $request->catatan_komite,
                    'ditetapkan_oleh' => Auth::id(),
                    'tanggal_keputusan' => now(),
                    'is_locked' => true, // Lock immediately after save
                ]
            );
            
            // Update pendaftaran status based on decision
            $newStatus = $request->keputusan === KeputusanSertifikasi::KEPUTUSAN_KOMPETEN
                ? PendaftaranSertifikasi::STATUS_KOMPETEN_FINAL
                : PendaftaranSertifikasi::STATUS_BELUM_KOMPETEN_FINAL;
            
            $pendaftaran->update(['status' => $newStatus]);
            
            DB::commit();
            
            // ======================================================
            // EMAIL: KIRIM LANGSUNG - KEPUTUSAN (KOMPETEN / BELUM KOMPETEN)
            // Direct Mail::to()->send() - NO EVENTS/LISTENERS
            // ======================================================
            try {
                // Load asesmen relation for email
                $keputusan->load('asesmen.pendaftaran.skemaSertifikasi');
                
                if ($request->keputusan === KeputusanSertifikasi::KEPUTUSAN_KOMPETEN) {
                    \Mail::to($pendaftaran->email)->send(
                        new \App\Mail\KeputusanKompeten($keputusan->asesmen)
                    );
                    \Log::info('[EMAIL SENT] Keputusan KOMPETEN', [
                        'keputusan_id' => $keputusan->id,
                        'pendaftaran_id' => $pendaftaran->id,
                        'email' => $pendaftaran->email,
                    ]);
                } else {
                    \Mail::to($pendaftaran->email)->send(
                        new \App\Mail\KeputusanBelumKompeten($keputusan->asesmen)
                    );
                    \Log::info('[EMAIL SENT] Keputusan BELUM KOMPETEN', [
                        'keputusan_id' => $keputusan->id,
                        'pendaftaran_id' => $pendaftaran->id,
                        'email' => $pendaftaran->email,
                    ]);
                }
            } catch (\Throwable $e) {
                // ⚠️ EMAIL GAGAL TIDAK BOLEH MEMBATALKAN PROSES
                \Log::error('[EMAIL FAILED] Keputusan', [
                    'keputusan_id' => $keputusan->id,
                    'keputusan' => $request->keputusan,
                    'email' => $pendaftaran->email,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
            
            return redirect()->route('adminui.keputusan.show', $pendaftaran->id)
                ->with('success', 'Keputusan sertifikasi berhasil disimpan dan dikunci. Email telah dikirim ke peserta.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Export Audit Evidence PDF for Keputusan Sertifikasi & Sertifikat.
     * 
     * Menghasilkan dokumen PDF bukti audit resmi yang mencakup:
     * - Identitas peserta/asesi
     * - Ringkasan hasil asesmen
     * - Keputusan komite teknis (FINAL & LOCKED)
     * - Informasi sertifikat (jika kompeten)
     * - Riwayat audit log
     * - Informasi integritas data (hash, UUID)
     * 
     * Sesuai dengan BNSP & ISO 17024 untuk keperluan audit/arsip.
     * Menutup rantai audit dari pendaftaran sampai sertifikat.
     */
    public function exportAuditEvidence(Request $request, $pendaftaranId)
    {
        $pendaftaran = PendaftaranSertifikasi::with([
            'user',
            'praPendaftaran',
            'skemaSertifikasi.unitKompetensi.kuks',
            'asesmen.asesor',
            'asesmen.details.unitKompetensi',
            'asesmen.details.kuk',
            'keputusan.penetap',
            'sertifikat.penerbit',
        ])->findOrFail($pendaftaranId);

        $keputusan = $pendaftaran->keputusan;
        $asesmen = $pendaftaran->asesmen;
        $sertifikat = $pendaftaran->sertifikat;

        // Validate keputusan exists
        if (!$keputusan) {
            return redirect()->back()
                ->with('error', 'Keputusan sertifikasi belum ditetapkan untuk pendaftaran ini.');
        }

        // Calculate asesmen summary
        $asesmenSummary = $asesmen ? $this->calculateAsesmenSummary($asesmen) : null;

        // Get related audit logs
        $referenceNumbers = [$pendaftaran->nomor_pendaftaran];
        if ($sertifikat) {
            $referenceNumbers[] = $sertifikat->nomor_sertifikat;
        }

        $auditLogs = AuditLog::where(function ($query) use ($pendaftaran, $keputusan) {
            $query->where('model_type', PendaftaranSertifikasi::class)
                  ->where('model_id', $pendaftaran->id);
        })->orWhere(function ($query) use ($keputusan) {
            $query->where('model_type', KeputusanSertifikasi::class)
                  ->where('model_id', $keputusan->id);
        })->orWhere(function ($query) use ($sertifikat) {
            if ($sertifikat) {
                $query->where('model_type', Sertifikat::class)
                      ->where('model_id', $sertifikat->id);
            }
        })->orWhereIn('reference_number', $referenceNumbers)
          ->orderBy('created_at', 'desc')
          ->take(25)
          ->get();

        // Generate document metadata
        $documentUuid = Str::uuid()->toString();
        $documentNumber = 'AUD-KPT-' . date('Ymd') . '-' . str_pad($pendaftaran->id, 5, '0', STR_PAD_LEFT);
        $generatedAt = now();
        
        // Create integrity hash
        $hashData = json_encode([
            'pendaftaran_id' => $pendaftaran->id,
            'nomor_pendaftaran' => $pendaftaran->nomor_pendaftaran,
            'keputusan_id' => $keputusan->id,
            'keputusan' => $keputusan->keputusan,
            'is_locked' => $keputusan->is_locked,
            'tanggal_keputusan' => $keputusan->tanggal_keputusan?->toIso8601String(),
            'nomor_sertifikat' => $sertifikat?->nomor_sertifikat,
            'sertifikat_uuid' => $sertifikat?->uuid,
            'document_uuid' => $documentUuid,
            'generated_at' => $generatedAt->toIso8601String(),
        ]);
        $integrityHash = hash('sha256', $hashData);

        // Generate QR code for sertifikat verification (if exists)
        $qrCodeBase64 = null;
        if ($sertifikat) {
            try {
                $verificationUrl = $sertifikat->getVerificationUrl();
                $qrCodeBase64 = base64_encode(
                    QrCode::format('svg')
                        ->size(120)
                        ->errorCorrection('H')
                        ->generate($verificationUrl)
                );
            } catch (\Exception $e) {
                // QR code generation failed, continue without it
                $qrCodeBase64 = null;
            }
        }

        // Log PDF generation
        AuditLog::log(
            AuditLog::ACTION_VIEW,
            AuditLog::MODULE_KEPUTUSAN,
            "Mengunduh PDF Audit Evidence Keputusan & Sertifikat untuk pendaftaran {$pendaftaran->nomor_pendaftaran}",
            $keputusan,
            null,
            null,
            [
                'event' => 'audit_pdf_generated_keputusan_sertifikat',
                'document_uuid' => $documentUuid,
                'document_number' => $documentNumber,
                'integrity_hash' => $integrityHash,
                'nomor_pendaftaran' => $pendaftaran->nomor_pendaftaran,
                'nomor_sertifikat' => $sertifikat?->nomor_sertifikat,
                'komite_teknis_id' => $keputusan->ditetapkan_oleh,
            ]
        );

        // Generate PDF
        $pdf = Pdf::loadView('audit.pdf.keputusan-sertifikat', [
            'pendaftaran' => $pendaftaran,
            'keputusan' => $keputusan,
            'asesmen' => $asesmen,
            'asesmenSummary' => $asesmenSummary,
            'sertifikat' => $sertifikat,
            'auditLogs' => $auditLogs,
            'documentNumber' => $documentNumber,
            'documentUuid' => $documentUuid,
            'integrityHash' => $integrityHash,
            'generatedAt' => $generatedAt,
            'qrCodeBase64' => $qrCodeBase64,
        ]);

        // PDF settings
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif',
        ]);

        $filename = "Audit_Evidence_Keputusan_{$pendaftaran->nomor_pendaftaran}_{$generatedAt->format('Ymd_His')}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Calculate asesmen summary statistics.
     */
    private function calculateAsesmenSummary(Asesmen $asesmen): array
    {
        $details = $asesmen->details;
        $detailsByUnit = $details->groupBy('unit_kompetensi_id');

        $totalUnit = $detailsByUnit->count();
        $totalKuk = $details->count();
        $totalKompeten = $details->where('hasil', AsesmenDetail::HASIL_KOMPETEN)->count();
        $totalBelumKompeten = $details->where('hasil', AsesmenDetail::HASIL_BELUM_KOMPETEN)->count();

        // Calculate unit-level competency
        $unitKompeten = 0;
        $unitBelumKompeten = 0;
        
        foreach ($detailsByUnit as $unitId => $unitDetails) {
            $hasBelumKompeten = $unitDetails->where('hasil', AsesmenDetail::HASIL_BELUM_KOMPETEN)->count() > 0;
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

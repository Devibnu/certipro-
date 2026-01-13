<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranSertifikasi;
use App\Models\Sertifikat;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SertifikatController extends Controller
{
    /**
     * Display list of certificates
     */
    public function index(Request $request)
    {
        $query = Sertifikat::with(['pendaftaran.user', 'pendaftaran.skemaSertifikasi', 'penerbit']);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_sertifikat', 'like', "%{$search}%")
                    ->orWhere('nama_peserta', 'like', "%{$search}%")
                    ->orWhere('skema_sertifikasi', 'like', "%{$search}%");
            });
        }
        
        $sertifikats = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Get pendaftaran that are kompeten_final but don't have certificate yet
        $pendaftaranKompeten = PendaftaranSertifikasi::with(['user', 'skemaSertifikasi', 'keputusan'])
            ->where('status', PendaftaranSertifikasi::STATUS_KOMPETEN_FINAL)
            ->whereDoesntHave('sertifikat')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('adminui.sertifikat.index', compact('sertifikats', 'pendaftaranKompeten'));
    }

    /**
     * Issue certificate for a pendaftaran
     */
    public function terbitkan($pendaftaranId)
    {
        $pendaftaran = PendaftaranSertifikasi::with(['user', 'skemaSertifikasi', 'sertifikat'])
            ->findOrFail($pendaftaranId);
        
        // Validate status is kompeten_final
        if ($pendaftaran->status !== PendaftaranSertifikasi::STATUS_KOMPETEN_FINAL) {
            return redirect()->route('adminui.sertifikat.index')
                ->with('error', 'Sertifikat hanya dapat diterbitkan untuk pendaftaran dengan status Kompeten (Final).');
        }
        
        // Check if certificate already exists
        if ($pendaftaran->sertifikat) {
            return redirect()->route('adminui.sertifikat.show', $pendaftaran->sertifikat->id)
                ->with('info', 'Sertifikat sudah diterbitkan sebelumnya.');
        }
        
        DB::beginTransaction();
        
        try {
            // Generate nomor sertifikat
            $nomorSertifikat = Sertifikat::generateNomorSertifikat();
            
            // Calculate validity period (3 years from now)
            $tanggalTerbit = now();
            $tanggalBerlakuSampai = now()->addYears(3);
            
            // Create sertifikat record first to get ID
            $sertifikat = Sertifikat::create([
                'pendaftaran_id' => $pendaftaran->id,
                'nomor_sertifikat' => $nomorSertifikat,
                'nama_peserta' => $pendaftaran->user->name,
                'skema_sertifikasi' => $pendaftaran->skemaSertifikasi->nama_skema,
                'tanggal_terbit' => $tanggalTerbit,
                'tanggal_berlaku_sampai' => $tanggalBerlakuSampai,
                'diterbitkan_oleh' => Auth::id(),
            ]);
            
            // Generate QR Code
            $verificationUrl = $sertifikat->getVerificationUrl();
            $qrCodeFileName = 'qr_' . $sertifikat->id . '_' . time() . '.svg';
            $qrCodePath = 'sertifikat/qrcodes/' . $qrCodeFileName;
            
            // Generate QR code as SVG
            $qrCode = QrCode::format('svg')
                ->size(200)
                ->margin(1)
                ->generate($verificationUrl);
            
            // Save QR code to storage
            Storage::disk('public')->put($qrCodePath, $qrCode);
            
            // Generate PDF
            $pdfFileName = 'sertifikat_' . $sertifikat->id . '_' . time() . '.pdf';
            $pdfPath = 'sertifikat/pdf/' . $pdfFileName;
            
            // Get QR code for PDF (base64)
            $qrCodeBase64 = base64_encode($qrCode);
            
            // Nama Ketua LSP (bisa diambil dari config atau database)
            $ketuaLsp = config('certipro.ketua_lsp', 'Dr. Ahmad Hidayat, M.Kom');
            
            $pdf = Pdf::loadView('pdf.sertifikat-bnsp', [
                'sertifikat' => $sertifikat,
                'pendaftaran' => $pendaftaran,
                'qrCodeBase64' => $qrCodeBase64,
                'ketuaLsp' => $ketuaLsp,
            ]);
            
            // A4 Portrait untuk format BNSP resmi
            $pdf->setPaper('A4', 'portrait');
            
            // Save PDF to storage
            Storage::disk('public')->put($pdfPath, $pdf->output());
            
            // Update sertifikat with file paths
            $sertifikat->update([
                'qr_code' => $qrCodePath,
                'file_pdf' => $pdfPath,
            ]);
            
            DB::commit();
            
            return redirect()->route('adminui.sertifikat.show', $sertifikat->id)
                ->with('success', 'Sertifikat berhasil diterbitkan dengan nomor: ' . $nomorSertifikat);
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('adminui.sertifikat.index')
                ->with('error', 'Terjadi kesalahan saat menerbitkan sertifikat: ' . $e->getMessage());
        }
    }

    /**
     * Show certificate details
     */
    public function show($id)
    {
        $sertifikat = Sertifikat::with([
            'pendaftaran.user',
            'pendaftaran.skemaSertifikasi',
            'pendaftaran.keputusan.penetap',
            'penerbit'
        ])->findOrFail($id);
        
        return view('adminui.sertifikat.show', compact('sertifikat'));
    }

    /**
     * Download certificate PDF
     */
    public function download($id)
    {
        $sertifikat = Sertifikat::findOrFail($id);
        
        if (!$sertifikat->file_pdf || !Storage::disk('public')->exists($sertifikat->file_pdf)) {
            return redirect()->back()->with('error', 'File PDF sertifikat tidak ditemukan.');
        }
        
        $fileName = 'Sertifikat_' . str_replace('/', '-', $sertifikat->nomor_sertifikat) . '.pdf';
        
        return Storage::disk('public')->download($sertifikat->file_pdf, $fileName);
    }

    /**
     * Preview certificate PDF in browser
     */
    public function preview($id)
    {
        $sertifikat = Sertifikat::with(['pendaftaran.skemaSertifikasi', 'penerbit'])->findOrFail($id);
        
        // Generate QR Code for preview
        $verificationUrl = $sertifikat->getVerificationUrl();
        $qrCode = QrCode::format('svg')
            ->size(200)
            ->margin(1)
            ->generate($verificationUrl);
        $qrCodeBase64 = base64_encode($qrCode);
        
        // Nama Ketua LSP
        $ketuaLsp = config('certipro.ketua_lsp', 'Dr. Ahmad Hidayat, M.Kom');
        
        $pdf = Pdf::loadView('pdf.sertifikat-bnsp', [
            'sertifikat' => $sertifikat,
            'pendaftaran' => $sertifikat->pendaftaran,
            'qrCodeBase64' => $qrCodeBase64,
            'ketuaLsp' => $ketuaLsp,
        ]);
        
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->stream('Sertifikat_Preview_' . $sertifikat->nomor_sertifikat . '.pdf');
    }

    /**
     * Regenerate certificate PDF (for template updates)
     */
    public function regenerate($id)
    {
        $sertifikat = Sertifikat::with(['pendaftaran.skemaSertifikasi', 'penerbit'])->findOrFail($id);
        
        try {
            // Delete old PDF if exists
            if ($sertifikat->file_pdf && Storage::disk('public')->exists($sertifikat->file_pdf)) {
                Storage::disk('public')->delete($sertifikat->file_pdf);
            }
            
            // Delete old QR if exists
            if ($sertifikat->qr_code && Storage::disk('public')->exists($sertifikat->qr_code)) {
                Storage::disk('public')->delete($sertifikat->qr_code);
            }
            
            // Generate new QR Code
            $verificationUrl = $sertifikat->getVerificationUrl();
            $qrCodeFileName = 'qr_' . $sertifikat->id . '_' . time() . '.svg';
            $qrCodePath = 'sertifikat/qrcodes/' . $qrCodeFileName;
            
            $qrCode = QrCode::format('svg')
                ->size(200)
                ->margin(1)
                ->generate($verificationUrl);
            
            Storage::disk('public')->put($qrCodePath, $qrCode);
            
            // Generate new PDF
            $pdfFileName = 'sertifikat_' . $sertifikat->id . '_' . time() . '.pdf';
            $pdfPath = 'sertifikat/pdf/' . $pdfFileName;
            
            $qrCodeBase64 = base64_encode($qrCode);
            
            // Nama Ketua LSP
            $ketuaLsp = config('certipro.ketua_lsp', 'Dr. Ahmad Hidayat, M.Kom');
            
            $pdf = Pdf::loadView('pdf.sertifikat-bnsp', [
                'sertifikat' => $sertifikat,
                'pendaftaran' => $sertifikat->pendaftaran,
                'qrCodeBase64' => $qrCodeBase64,
                'ketuaLsp' => $ketuaLsp,
            ]);
            
            $pdf->setPaper('A4', 'portrait');
            
            Storage::disk('public')->put($pdfPath, $pdf->output());
            
            // Update sertifikat
            $sertifikat->update([
                'qr_code' => $qrCodePath,
                'file_pdf' => $pdfPath,
            ]);
            
            return redirect()->route('adminui.sertifikat.show', $sertifikat->id)
                ->with('success', 'Sertifikat berhasil di-regenerate dengan template terbaru.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal regenerate sertifikat: ' . $e->getMessage());
        }
    }

    /**
     * Generate QR Code for certificate
     *
     * @param Sertifikat $sertifikat
     * @return string QR code as SVG string
     */
    protected function generateQrCode(Sertifikat $sertifikat): string
    {
        return QrCode::format('svg')
            ->size(200)
            ->margin(1)
            ->generate($sertifikat->getVerificationUrl());
    }

    /**
     * Get PDF data for rendering
     *
     * @param Sertifikat $sertifikat
     * @param string $qrCodeBase64
     * @return array
     */
    protected function getPdfData(Sertifikat $sertifikat, string $qrCodeBase64): array
    {
        return [
            'sertifikat' => $sertifikat,
            'pendaftaran' => $sertifikat->pendaftaran,
            'qrCodeBase64' => $qrCodeBase64,
            'ketuaLsp' => config('certipro.ketua_lsp', 'Dr. Ahmad Hidayat, M.Kom'),
        ];
    }

    /**
     * Render PDF with standard settings
     *
     * @param array $data
     * @return \Barryvdh\DomPDF\PDF
     */
    protected function renderPdf(array $data)
    {
        $pdf = Pdf::loadView('pdf.sertifikat-bnsp', $data);
        
        $pdf->setPaper(
            config('certipro.pdf.paper_size', 'A4'),
            config('certipro.pdf.orientation', 'portrait')
        );
        
        return $pdf;
    }
}

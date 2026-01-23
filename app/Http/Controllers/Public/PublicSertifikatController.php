<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Sertifikat;
use Illuminate\Http\Request;

/**
 * PublicSertifikatController
 * 
 * Handles public certificate verification (no authentication required).
 * Allows anyone to verify certificate authenticity via QR code or manual entry.
 */
class PublicSertifikatController extends Controller
{
    /**
     * Verify certificate by ID (primary method for QR code).
     * 
     * This route is accessed via QR code scan.
     * URL format: /sertifikat/verify/{id}
     * 
     * @param int $id Certificate ID
     * @return \Illuminate\View\View
     */
    public function verify($id)
    {
        // Find certificate by ID
        $sertifikat = Sertifikat::with([
            'pendaftaran.user',
            'pendaftaran.skemaSertifikasi',
            'pendaftaran.keputusan.penetap',
            'penerbit'
        ])->find($id);
        
        // Not found
        if (!$sertifikat) {
            return view('public.sertifikat.verify', [
                'found' => false,
                'message' => 'Sertifikat tidak ditemukan. Pastikan kode QR valid.',
            ]);
        }
        
        // Found - show certificate info
        return view('public.sertifikat.verify', [
            'found' => true,
            'sertifikat' => $sertifikat,
            'isValid' => $sertifikat->tanggal_berlaku_sampai >= now(),
            'verificationUrl' => route('public.sertifikat.verify', $sertifikat->id),
        ]);
    }
    
    /**
     * Verify certificate by nomor sertifikat (legacy/manual method).
     * 
     * This route is used when user manually enters certificate number.
     * URL format: /sertifikat/verifikasi/{nomor}
     * 
     * @param string $nomor Certificate number (e.g., CERT/CTP/2026/000123)
     * @return \Illuminate\View\View
     */
    public function verifyByNumber($nomor)
    {
        // Decode URL-encoded nomor
        $nomor = urldecode($nomor);
        
        // Find certificate by nomor
        $sertifikat = Sertifikat::with([
            'pendaftaran.user',
            'pendaftaran.skemaSertifikasi',
            'pendaftaran.keputusan.penetap',
            'penerbit'
        ])->where('nomor_sertifikat', $nomor)->first();
        
        // Not found
        if (!$sertifikat) {
            return view('public.sertifikat.verify', [
                'found' => false,
                'message' => 'Sertifikat dengan nomor "' . $nomor . '" tidak ditemukan.',
            ]);
        }
        
        // Found
        return view('public.sertifikat.verify', [
            'found' => true,
            'sertifikat' => $sertifikat,
            'isValid' => $sertifikat->isValid(),
            'securityHash' => $sertifikat->security_hash,
        ]);
    }
    
    /**
     * Show verification search form.
     * 
     * Allows users to manually enter certificate number for verification.
     */
    public function searchForm()
    {
        return view('public.sertifikat.search');
    }
    
    /**
     * Process verification search.
     */
    public function search(Request $request)
    {
        $request->validate([
            'nomor_sertifikat' => 'required|string|max:100',
        ]);
        
        $nomor = $request->nomor_sertifikat;
        
        // Remove whitespace and normalize
        $nomor = trim($nomor);
        $nomor = str_replace(' ', '', $nomor);
        
        return redirect()->route('public.sertifikat.verifikasi', $nomor);
    }
    
    /**
     * Download verified certificate PDF.
     * 
     * Note: This is public route but requires valid UUID to prevent enumeration.
     */
    public function downloadPublic($uuid)
    {
        $sertifikat = Sertifikat::where('uuid', $uuid)->firstOrFail();
        
        // Check if PDF exists
        if (!$sertifikat->file_pdf || !\Storage::disk('public')->exists($sertifikat->file_pdf)) {
            abort(404, 'File PDF tidak tersedia');
        }
        
        // Track download for analytics
        \Log::info('Public certificate download', [
            'sertifikat_id' => $sertifikat->id,
            'nomor' => $sertifikat->nomor_sertifikat,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        
        // Download
        $fileName = 'Sertifikat_' . str_replace('/', '-', $sertifikat->nomor_sertifikat) . '.pdf';
        
        return \Storage::disk('public')->download($sertifikat->file_pdf, $fileName);
    }
}

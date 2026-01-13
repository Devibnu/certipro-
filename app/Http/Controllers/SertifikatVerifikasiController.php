<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Sertifikat;
use Illuminate\Http\Request;

class SertifikatVerifikasiController extends Controller
{
    /**
     * Public search page for certificate verification
     */
    public function search(Request $request)
    {
        $nomor = $request->input('nomor');
        $sertifikat = null;
        $searched = false;
        
        if ($nomor) {
            $searched = true;
            $sertifikat = Sertifikat::with([
                'pendaftaran.user',
                'pendaftaran.skemaSertifikasi',
                'penerbit'
            ])->where('nomor_sertifikat', 'like', "%{$nomor}%")
              ->orWhere('uuid', $nomor)
              ->first();
            
            // Log public verification access
            if ($sertifikat) {
                AuditLog::logVerifikasiPublik($sertifikat);
            }
        }
        
        return view('public.sertifikat-search', [
            'sertifikat' => $sertifikat,
            'searched' => $searched,
            'query' => $nomor,
        ]);
    }

    /**
     * Verify certificate by UUID (from QR Code)
     */
    public function verifyByUuid($uuid)
    {
        $sertifikat = Sertifikat::with([
            'pendaftaran.user',
            'pendaftaran.skemaSertifikasi',
            'penerbit'
        ])->where('uuid', $uuid)->first();
        
        if (!$sertifikat) {
            return view('public.sertifikat-verifikasi', [
                'found' => false,
                'nomor_sertifikat' => $uuid,
            ]);
        }
        
        // Log public verification access
        AuditLog::logVerifikasiPublik($sertifikat);
        
        return view('public.sertifikat-verifikasi', [
            'found' => true,
            'sertifikat' => $sertifikat,
            'nomor_sertifikat' => $sertifikat->nomor_sertifikat,
        ]);
    }

    /**
     * Public verification page for certificate by nomor_sertifikat
     */
    public function verifikasi($nomor_sertifikat)
    {
        // Decode the nomor_sertifikat from URL
        $nomorSertifikat = urldecode($nomor_sertifikat);
        
        // Find the certificate
        $sertifikat = Sertifikat::with([
            'pendaftaran.user',
            'pendaftaran.skemaSertifikasi',
            'penerbit'
        ])->where('nomor_sertifikat', $nomorSertifikat)->first();
        
        if (!$sertifikat) {
            return view('public.sertifikat-verifikasi', [
                'found' => false,
                'nomor_sertifikat' => $nomorSertifikat,
            ]);
        }
        
        // Log public verification access
        AuditLog::logVerifikasiPublik($sertifikat);
        
        return view('public.sertifikat-verifikasi', [
            'found' => true,
            'sertifikat' => $sertifikat,
            'nomor_sertifikat' => $nomorSertifikat,
        ]);
    }
}

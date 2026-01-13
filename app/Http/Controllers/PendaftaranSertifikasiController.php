<?php

namespace App\Http\Controllers;

use App\Models\PendaftaranSertifikasi;
use App\Models\SkemaSertifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PendaftaranSertifikasiController extends Controller
{
    /**
     * Display a listing of the asesi's pendaftaran.
     */
    public function index()
    {
        $user = Auth::user();
        
        $pendaftaran = PendaftaranSertifikasi::with('skemaSertifikasi')
            ->byUser($user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        $skemaList = SkemaSertifikasi::aktif()->orderBy('nama_skema')->get();
        
        // Get skema IDs that user already registered
        $registeredSkemaIds = PendaftaranSertifikasi::byUser($user->id)
            ->whereNotIn('status', [PendaftaranSertifikasi::STATUS_DITOLAK])
            ->pluck('skema_sertifikasi_id')
            ->toArray();
        
        return view('pendaftaran-sertifikasi.index', compact('pendaftaran', 'skemaList', 'registeredSkemaIds'));
    }

    /**
     * Store a newly created pendaftaran.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'skema_sertifikasi_id' => 'required|exists:skema_sertifikasi,id',
        ], [
            'skema_sertifikasi_id.required' => 'Skema Sertifikasi wajib dipilih.',
            'skema_sertifikasi_id.exists' => 'Skema Sertifikasi tidak valid.',
        ]);
        
        // Check if user already registered for this skema (except ditolak)
        $existingPendaftaran = PendaftaranSertifikasi::byUser($user->id)
            ->where('skema_sertifikasi_id', $validated['skema_sertifikasi_id'])
            ->whereNotIn('status', [PendaftaranSertifikasi::STATUS_DITOLAK])
            ->first();
        
        if ($existingPendaftaran) {
            return back()->with('error', 'Anda sudah terdaftar pada skema sertifikasi ini.');
        }
        
        // Check if skema is active
        $skema = SkemaSertifikasi::find($validated['skema_sertifikasi_id']);
        if (!$skema || !$skema->aktif) {
            return back()->with('error', 'Skema Sertifikasi tidak aktif.');
        }
        
        // Create pendaftaran with status diajukan
        PendaftaranSertifikasi::create([
            'user_id' => $user->id,
            'skema_sertifikasi_id' => $validated['skema_sertifikasi_id'],
            'nomor_pendaftaran' => PendaftaranSertifikasi::generateNomorPendaftaran(),
            'tanggal_daftar' => now()->toDateString(),
            'status' => PendaftaranSertifikasi::STATUS_DIAJUKAN,
        ]);
        
        return redirect()
            ->route('pendaftaran-sertifikasi.index')
            ->with('success', 'Pendaftaran sertifikasi berhasil diajukan. Silakan tunggu verifikasi dari admin.');
    }
}

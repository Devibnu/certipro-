<?php

namespace App\Http\Controllers;

use App\Models\PraPendaftaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PraPendaftaranController extends Controller
{
    /**
     * Tampilkan form pra-pendaftaran
     */
    public function create()
    {
        return view('public.daftar');
    }

    /**
     * Simpan data pra-pendaftaran
     */
    public function store(Request $request)
    {
        // Validasi dasar
        $rules = [
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'no_hp' => 'required|string|max:20',
            'tipe_peserta' => 'required|in:umum,kampus',
            'institusi' => 'nullable|string|max:255',
            'upload_identitas' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];

        // Validasi kondisional berdasarkan tipe peserta
        if ($request->tipe_peserta === 'umum') {
            $rules['nik'] = 'required|string|size:16';
            $rules['nim'] = 'nullable|string|max:50';
        } else {
            $rules['nik'] = 'nullable|string|size:16';
            $rules['nim'] = 'required|string|max:50';
        }

        $validated = $request->validate($rules, [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'no_hp.required' => 'Nomor HP wajib diisi',
            'tipe_peserta.required' => 'Tipe peserta wajib dipilih',
            'nik.required' => 'NIK wajib diisi untuk peserta umum',
            'nik.size' => 'NIK harus 16 digit',
            'nim.required' => 'NIM wajib diisi untuk peserta kampus',
            'upload_identitas.mimes' => 'File harus berformat JPG, PNG, atau PDF',
            'upload_identitas.max' => 'Ukuran file maksimal 2MB',
        ]);

        // Handle file upload
        if ($request->hasFile('upload_identitas')) {
            $file = $request->file('upload_identitas');
            $filename = time() . '_' . $file->getClientOriginalName();
            $validated['upload_identitas'] = $file->storeAs('pra-pendaftaran', $filename, 'public');
        }

        // Set status default
        $validated['status'] = 'baru';

        // Generate nomor pendaftaran
        $validated['nomor_pra_pendaftaran'] = self::generateNomorPraPendaftaran();

        // Simpan data
        $praPendaftaran = PraPendaftaran::create($validated);

        // Redirect ke halaman sukses dengan data pendaftaran
        return redirect()->route('pendaftaran.sukses')
            ->with('pendaftaran', [
                'nomor' => $praPendaftaran->nomor_pra_pendaftaran,
                'nama' => $praPendaftaran->nama_lengkap,
                'email' => $praPendaftaran->email,
                'status' => $praPendaftaran->status,
                'created_at' => $praPendaftaran->created_at->format('d F Y, H:i'),
            ]);
    }

    /**
     * Tampilkan halaman sukses pendaftaran
     */
    public function sukses()
    {
        // Jika tidak ada data pendaftaran di session, redirect ke form daftar
        if (!session()->has('pendaftaran')) {
            return redirect()->route('daftar');
        }

        $pendaftaran = session('pendaftaran');
        
        return view('public.pendaftaran-sukses', compact('pendaftaran'));
    }

    /**
     * Generate nomor pra-pendaftaran unik
     */
    public static function generateNomorPraPendaftaran(): string
    {
        $prefix = 'PRA';
        $year = date('Y');
        $month = date('m');

        // Get last number for this month
        $lastRecord = PraPendaftaran::where('nomor_pra_pendaftaran', 'like', "{$prefix}{$year}{$month}%")
            ->orderBy('nomor_pra_pendaftaran', 'desc')
            ->first();

        if ($lastRecord && $lastRecord->nomor_pra_pendaftaran) {
            $lastNumber = (int) substr($lastRecord->nomor_pra_pendaftaran, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . $year . $month . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}

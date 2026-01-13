<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\PendaftaranSertifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StatusPendaftaranController extends Controller
{
    /**
     * Show the public status check form.
     */
    public function index()
    {
        return view('status-pendaftaran.index');
    }

    /**
     * Search and display registration status.
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'required|string|min:5|max:100',
        ], [
            'search.required' => 'Masukkan nomor pendaftaran atau email.',
            'search.min' => 'Minimal 5 karakter.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $search = trim($request->input('search'));

        // Search by nomor pendaftaran or email
        $pendaftaran = PendaftaranSertifikasi::with(['skemaSertifikasi', 'sertifikat'])
            ->where('nomor_pendaftaran', $search)
            ->orWhere('email', $search)
            ->first();

        if (!$pendaftaran) {
            // Log the failed search attempt (for security monitoring)
            AuditLog::log(
                AuditLog::ACTION_VIEW,
                AuditLog::MODULE_PENDAFTARAN,
                "Pencarian status publik gagal: {$search} tidak ditemukan",
                null,
                null,
                ['search_query' => $search],
                [
                    'event' => 'public_status_search_failed',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            );

            return back()
                ->with('error', 'Data tidak ditemukan. Pastikan nomor pendaftaran atau email sudah benar.')
                ->withInput();
        }

        // Log successful search
        AuditLog::log(
            AuditLog::ACTION_VIEW,
            AuditLog::MODULE_PENDAFTARAN,
            "Pencarian status publik berhasil: {$pendaftaran->nomor_pendaftaran}",
            $pendaftaran,
            null,
            null,
            [
                'event' => 'public_status_search_success',
                'ip_address' => $request->ip(),
                'status_peserta' => $pendaftaran->status_peserta,
            ]
        );

        return view('status-pendaftaran.result', compact('pendaftaran'));
    }

    /**
     * API endpoint for status check (optional).
     */
    public function apiCheck(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nomor_pendaftaran' => 'required_without:email|string',
            'email' => 'required_without:nomor_pendaftaran|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = PendaftaranSertifikasi::query();

        if ($request->filled('nomor_pendaftaran')) {
            $query->where('nomor_pendaftaran', $request->input('nomor_pendaftaran'));
        }

        if ($request->filled('email')) {
            $query->where('email', $request->input('email'));
        }

        $pendaftaran = $query->first();

        if (!$pendaftaran) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'nomor_pendaftaran' => $pendaftaran->nomor_pendaftaran,
                'nama' => $pendaftaran->asesi_name,
                'skema' => $pendaftaran->skemaSertifikasi->nama_skema ?? null,
                'status' => $pendaftaran->status_peserta,
                'status_label' => $pendaftaran->status_peserta_label,
                'pesan' => $pendaftaran->pesan_status_peserta ?? PendaftaranSertifikasi::getStatusPesertaMessage($pendaftaran->status_peserta),
                'tanggal_update' => $pendaftaran->status_peserta_updated_at?->format('d M Y H:i') ?? $pendaftaran->updated_at->format('d M Y H:i'),
                'tanggal_daftar' => $pendaftaran->tanggal_daftar->format('d M Y'),
            ],
        ]);
    }
}

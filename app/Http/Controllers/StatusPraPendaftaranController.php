<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\PraPendaftaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StatusPraPendaftaranController extends Controller
{
    /**
     * Show the public status check form.
     */
    public function index()
    {
        return view('status-pra-pendaftaran.index');
    }

    /**
     * Search and display pra-pendaftaran status.
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'required|string|min:5|max:100',
        ], [
            'search.required' => 'Masukkan nomor pra-pendaftaran atau email.',
            'search.min' => 'Minimal 5 karakter.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $search = trim($request->input('search'));

        // Search by nomor pra-pendaftaran or email
        $praPendaftaran = PraPendaftaran::with(['pendaftaranSertifikasi', 'statusUpdatedBy'])
            ->where('nomor_pra_pendaftaran', $search)
            ->orWhere('email', $search)
            ->first();

        if (!$praPendaftaran) {
            // Log the failed search attempt (for security monitoring)
            AuditLog::log(
                AuditLog::ACTION_VIEW,
                'pra_pendaftaran',
                "Pencarian status pra-pendaftaran gagal: {$search} tidak ditemukan",
                null,
                null,
                ['search_query' => $search],
                [
                    'event' => 'public_pra_status_search_failed',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            );

            return back()
                ->with('error', 'Data tidak ditemukan. Pastikan nomor pra-pendaftaran atau email sudah benar.')
                ->withInput();
        }

        // Log successful search
        AuditLog::log(
            AuditLog::ACTION_VIEW,
            'pra_pendaftaran',
            "Pencarian status pra-pendaftaran berhasil: {$praPendaftaran->nomor_pra_pendaftaran}",
            $praPendaftaran,
            null,
            null,
            [
                'event' => 'public_pra_status_search_success',
                'ip_address' => $request->ip(),
                'status' => $praPendaftaran->status,
            ]
        );

        return view('status-pra-pendaftaran.result', compact('praPendaftaran'));
    }

    /**
     * API endpoint for status check (optional).
     */
    public function apiCheck(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nomor_pra_pendaftaran' => 'required_without:email|string',
            'email' => 'required_without:nomor_pra_pendaftaran|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = PraPendaftaran::query();

        if ($request->filled('nomor_pra_pendaftaran')) {
            $query->where('nomor_pra_pendaftaran', $request->nomor_pra_pendaftaran);
        }

        if ($request->filled('email')) {
            $query->where('email', $request->email);
        }

        $praPendaftaran = $query->first();

        if (!$praPendaftaran) {
            return response()->json([
                'success' => false,
                'message' => 'Data pra-pendaftaran tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'nomor_pra_pendaftaran' => $praPendaftaran->nomor_pra_pendaftaran,
                'nama_lengkap' => $praPendaftaran->nama_lengkap,
                'status' => $praPendaftaran->status,
                'status_label' => $praPendaftaran->status_public_label,
                'status_color' => $praPendaftaran->status_color,
                'status_message' => $praPendaftaran->status_message,
                'tanggal_daftar' => $praPendaftaran->created_at->format('d F Y'),
                'can_proceed' => $praPendaftaran->canProceedToSertifikasi(),
                'alasan_penolakan' => $praPendaftaran->status === 'ditolak' ? $praPendaftaran->alasan_penolakan : null,
            ],
        ]);
    }
}

<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PraPendaftaran;
use App\Models\PendaftaranSertifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PraPendaftaranAdminController extends Controller
{
    /**
     * Tampilkan daftar pra-pendaftaran
     */
    public function index(Request $request)
    {
        $query = PraPendaftaran::query()->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by tipe peserta
        if ($request->filled('tipe_peserta')) {
            $query->where('tipe_peserta', $request->tipe_peserta);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%")
                  ->orWhere('nomor_pra_pendaftaran', 'like', "%{$search}%");
            });
        }

        $pendaftaran = $query->paginate(15)->withQueryString();
        
        $statusLabels = PraPendaftaran::statusLabels();
        $tipePesertaLabels = PraPendaftaran::tipePesertaLabels();
        
        // ========================================================================
        // NO SKEMA LIST - Pra-Pendaftaran is NOT about skema assignment
        // ========================================================================
        // Removed: $skemaList = SkemaSertifikasi::orderBy('nama_skema')->get();
        // Reason: Violates single responsibility principle
        // Skema assignment happens in Pendaftaran Sertifikasi module ONLY
        // ========================================================================

        return view('adminui.pra-pendaftaran.index', compact(
            'pendaftaran', 
            'statusLabels', 
            'tipePesertaLabels'
            // Removed: 'skemaList' - NO skema logic in pra-pendaftaran
        ));
    }

    /**
     * Tampilkan detail pra-pendaftaran
     */
    public function show($id)
    {
        $data = PraPendaftaran::findOrFail($id);
        $statusLabels = PraPendaftaran::statusLabels();
        
        // Log admin viewing the detail
        AuditLog::log(
            AuditLog::ACTION_VIEW,
            AuditLog::MODULE_PRA_PENDAFTARAN,
            "Admin membuka detail pra-pendaftaran: {$data->nama_lengkap}",
            $data,
            null,
            null,
            [
                'event' => 'admin_view_detail',
                'nomor_pra_pendaftaran' => $data->nomor_pra_pendaftaran,
                'current_status' => $data->status,
            ]
        );
        
        return view('adminui.pra-pendaftaran.show', compact('data', 'statusLabels'));
    }

    /**
     * ========================================================================
     * UPDATE STATUS - ONLY 3 VALID TRANSITIONS (GUARD CONDITIONS)
     * ========================================================================
     * Valid transitions:
     * 1. MENUNGGU_VERIFIKASI → DITERIMA (Admin approves)
     * 2. MENUNGGU_VERIFIKASI → DITOLAK (Admin rejects)
     * 
     * TIDAK BOLEH:
     * ❌ Auto-create pendaftaran sertifikasi
     * ❌ Pilih skema di sini
     * ❌ Ubah ke status SIAP_ASESMEN
     * ❌ Ada status DIPROSES (ambiguous)
     * ========================================================================
     */
    public function updateStatus(Request $request, $id)
    {
        // ========================================================================
        // VALIDATION: ONLY 3 VALID STATUSES
        // ========================================================================
        $request->validate([
            'status' => 'required|in:menunggu_verifikasi,diterima,ditolak',
            'alasan_penolakan' => 'required_if:status,ditolak|nullable|string|max:1000',
        ], [
            'status.required' => 'Status wajib dipilih',
            'status.in' => 'Status tidak valid. Hanya boleh: MENUNGGU_VERIFIKASI, DITERIMA, atau DITOLAK',
            'alasan_penolakan.required_if' => 'Alasan penolakan wajib diisi jika status DITOLAK.',
        ]);

        $data = PraPendaftaran::findOrFail($id);
        $oldStatus = $data->status;
        $newStatus = $request->status;
        
        // ========================================================================
        // GUARD 1: Prevent invalid status transitions
        // ========================================================================
        if ($data->status === PraPendaftaran::STATUS_DITERIMA && $newStatus !== PraPendaftaran::STATUS_DITERIMA) {
            return redirect()->back()->with('error', 
                '❌ Pra-pendaftaran yang sudah DITERIMA tidak dapat diubah statusnya. '
                . 'Jika ingin membatalkan, lakukan di modul Pendaftaran Sertifikasi.'
            );
        }
        
        if ($data->status === PraPendaftaran::STATUS_DITOLAK && $newStatus !== PraPendaftaran::STATUS_DITOLAK) {
            return redirect()->back()->with('error', 
                '❌ Pra-pendaftaran yang sudah DITOLAK tidak dapat diubah statusnya. '
                . 'Peserta harus submit ulang pra-pendaftaran baru.'
            );
        }
        
        // Prepare update data
        $updateData = [
            'status' => $newStatus,
            'status_updated_at' => now(),
            'status_updated_by' => auth()->id(),
        ];
        
        // Add rejection reason if status is ditolak
        if ($newStatus === PraPendaftaran::STATUS_DITOLAK) {
            $updateData['alasan_penolakan'] = $request->alasan_penolakan;
            
            // Log the rejection reason specifically
            AuditLog::log(
                AuditLog::ACTION_REJECT,
                AuditLog::MODULE_PRA_PENDAFTARAN,
                "Admin TOLAK pra-pendaftaran: {$data->nama_lengkap}. Alasan: {$request->alasan_penolakan}",
                $data,
                ['alasan_penolakan' => $data->alasan_penolakan],
                ['alasan_penolakan' => $request->alasan_penolakan],
                [
                    'event' => 'admin_rejection',
                    'nomor_pra_pendaftaran' => $data->nomor_pra_pendaftaran,
                    'alasan_penolakan' => $request->alasan_penolakan,
                ]
            );
        }
        
        // Update the record (this will trigger Observer for email notification)
        $data->update($updateData);

        // ========================================================================
        // GUARD 2: NO AUTO-CREATE pendaftaran sertifikasi
        // ========================================================================
        // Prinsip: Pra-Pendaftaran ≠ Pendaftaran Sertifikasi
        // Jika status DITERIMA:
        //   - STOP process di sini
        //   - Email: "Menunggu penetapan skema"
        //   - Admin harus ke modul Pendaftaran Sertifikasi untuk assign skema
        // ========================================================================
        
        if ($newStatus === PraPendaftaran::STATUS_DITERIMA) {
            return redirect()->back()->with('success', 
                '✅ Pra-Pendaftaran DITERIMA. '
                . 'Langkah selanjutnya: Buka modul "Pendaftaran Sertifikasi" untuk menetapkan skema sertifikasi. '
                . 'Email pemberitahuan telah dikirim ke peserta.'
            );
        }
        
        if ($newStatus === PraPendaftaran::STATUS_DITOLAK) {
            return redirect()->back()->with('success', 
                '❌ Pra-Pendaftaran DITOLAK. Email pemberitahuan dengan alasan penolakan telah dikirim ke peserta.'
            );
        }

        return redirect()->back()->with('success', 'Status berhasil diperbarui.');
    }
    
    // ========================================================================
    // METHOD buatPendaftaranSertifikasi() REMOVED
    // ========================================================================
    // Reason: Violates clean architecture (separation of concerns)
    // 
    // Pra-Pendaftaran module responsibility:
    //   - Verify documents only
    //   - Update status (DITERIMA/DITOLAK)
    //   - Send email notification
    // 
    // Pendaftaran Sertifikasi module responsibility:
    //   - Create pendaftaran record from approved Pra-Pendaftaran
    //   - Assign skema sertifikasi
    //   - Generate nomor pendaftaran
    //   - Create user account if needed
    // 
    // Admin workflow:
    //   1. Verify Pra-Pendaftaran (this module)
    //   2. Go to Pendaftaran Sertifikasi module
    //   3. Click "Buat dari Pra-Pendaftaran"
    //   4. Select skema
    //   5. Submit
    // 
    // This enforces explicit skema assignment and prevents:
    //   ❌ Auto-create without skema
    //   ❌ Ambiguous redirect flows
    //   ❌ Admin confusion about "where to assign skema?"
    // ========================================================================
}

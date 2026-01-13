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

        return view('adminui.pra-pendaftaran.index', compact(
            'pendaftaran', 
            'statusLabels', 
            'tipePesertaLabels'
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
     * Update status pra-pendaftaran
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:baru,diproses,diterima,ditolak',
            'alasan_penolakan' => 'required_if:status,ditolak|nullable|string|max:1000',
        ], [
            'alasan_penolakan.required_if' => 'Alasan penolakan wajib diisi jika status DITOLAK.',
        ]);

        $data = PraPendaftaran::findOrFail($id);
        $oldStatus = $data->status;
        $newStatus = $request->status;
        
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
                "Admin memberikan alasan penolakan untuk pra-pendaftaran: {$data->nama_lengkap}",
                $data,
                ['alasan_penolakan' => $data->alasan_penolakan],
                ['alasan_penolakan' => $request->alasan_penolakan],
                [
                    'event' => 'admin_rejection_reason',
                    'nomor_pra_pendaftaran' => $data->nomor_pra_pendaftaran,
                    'alasan_penolakan' => $request->alasan_penolakan,
                ]
            );
        }
        
        // Update the record (this will trigger Observer for status change notification)
        $data->update($updateData);

        // Jika status berubah menjadi DITERIMA, otomatis buat pendaftaran sertifikasi
        if ($newStatus === PraPendaftaran::STATUS_DITERIMA && $oldStatus !== PraPendaftaran::STATUS_DITERIMA) {
            if (!$data->hasPendaftaranSertifikasi()) {
                return $this->buatPendaftaranSertifikasi($id);
            }
        }

        return redirect()->back()->with('success', 'Status berhasil diperbarui menjadi ' . strtoupper($newStatus));
    }

    /**
     * Buat pendaftaran sertifikasi dari pra-pendaftaran
     */
    public function buatPendaftaranSertifikasi($id)
    {
        $praPendaftaran = PraPendaftaran::findOrFail($id);

        // Validasi status harus DITERIMA
        if ($praPendaftaran->status !== PraPendaftaran::STATUS_DITERIMA) {
            return redirect()->back()->with('error', 'Hanya pra-pendaftaran dengan status DITERIMA yang dapat dibuatkan pendaftaran sertifikasi.');
        }

        // Cek apakah sudah ada pendaftaran sertifikasi
        if ($praPendaftaran->hasPendaftaranSertifikasi()) {
            return redirect()->back()->with('error', 'Pendaftaran sertifikasi sudah dibuat untuk pra-pendaftaran ini.');
        }

        try {
            DB::beginTransaction();

            // Generate nomor pendaftaran: REG + YYYY + XXXX
            $year = date('Y');
            $lastNumber = PendaftaranSertifikasi::whereYear('created_at', $year)
                ->count() + 1;
            $nomorPendaftaran = 'REG' . $year . str_pad($lastNumber, 4, '0', STR_PAD_LEFT);

            // Buat record pendaftaran sertifikasi
            $pendaftaran = PendaftaranSertifikasi::create([
                'pra_pendaftaran_id' => $praPendaftaran->id,
                'nama_lengkap' => $praPendaftaran->nama_lengkap,
                'email' => $praPendaftaran->email,
                'no_hp' => $praPendaftaran->no_hp,
                'tipe_peserta' => $praPendaftaran->tipe_peserta,
                'nik' => $praPendaftaran->nik,
                'nim' => $praPendaftaran->nim,
                'institusi' => $praPendaftaran->institusi,
                'nomor_pendaftaran' => $nomorPendaftaran,
                'tanggal_daftar' => now(),
                'status' => 'draft',
            ]);

            // Log the creation of pendaftaran sertifikasi
            AuditLog::log(
                AuditLog::ACTION_CREATE,
                AuditLog::MODULE_PENDAFTARAN,
                "Pendaftaran sertifikasi dibuat dari pra-pendaftaran: {$praPendaftaran->nomor_pra_pendaftaran}",
                $pendaftaran,
                null,
                $pendaftaran->toArray(),
                [
                    'event' => 'pendaftaran_created_from_pra',
                    'nomor_pra_pendaftaran' => $praPendaftaran->nomor_pra_pendaftaran,
                    'nomor_pendaftaran' => $nomorPendaftaran,
                ]
            );

            DB::commit();

            return redirect()
                ->route('adminui.pendaftaran-sertifikasi.show', $pendaftaran->id)
                ->with('success', 'Pendaftaran sertifikasi berhasil dibuat dengan nomor: ' . $nomorPendaftaran);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log the error
            AuditLog::log(
                AuditLog::ACTION_CREATE,
                AuditLog::MODULE_PENDAFTARAN,
                "Gagal membuat pendaftaran sertifikasi dari pra-pendaftaran: {$praPendaftaran->nomor_pra_pendaftaran}",
                $praPendaftaran,
                null,
                null,
                [
                    'event' => 'pendaftaran_creation_failed',
                    'nomor_pra_pendaftaran' => $praPendaftaran->nomor_pra_pendaftaran,
                    'error' => $e->getMessage(),
                ]
            );
            
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}

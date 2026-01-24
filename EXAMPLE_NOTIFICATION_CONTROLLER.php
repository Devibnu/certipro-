<?php

namespace App\Http\Controllers\Examples;

use App\Events\KeputusanDitetapkan;
use App\Events\PendaftaranDiajukan;
use App\Events\PraPendaftaranVerified;
use App\Events\SertifikatDiterbitkan;
use App\Http\Controllers\Controller;
use App\Models\KeputusanSertifikasi;
use App\Models\PendaftaranSertifikasi;
use App\Models\PraPendaftaran;
use App\Models\Sertifikat;
use Illuminate\Http\Request;

/**
 * Contoh cara trigger notification events dari controller
 */
class NotificationExampleController extends Controller
{
    /**
     * Contoh 1: Approve Pra-Pendaftaran (DITERIMA)
     */
    public function approvePraPendaftaran(Request $request, $id)
    {
        $praPendaftaran = PraPendaftaran::findOrFail($id);
        
        // Update status
        $praPendaftaran->update([
            'status' => 'diterima',
            'status_updated_at' => now(),
            'status_updated_by' => auth()->id(),
        ]);

        // 🔥 TRIGGER EVENT - Notification otomatis terkirim via Queue
        event(new PraPendaftaranVerified($praPendaftaran, 'diterima'));

        return redirect()->back()->with('success', 'Pra-pendaftaran disetujui. Notifikasi otomatis dikirim.');
    }

    /**
     * Contoh 2: Reject Pra-Pendaftaran (DITOLAK)
     */
    public function rejectPraPendaftaran(Request $request, $id)
    {
        $praPendaftaran = PraPendaftaran::findOrFail($id);
        
        $praPendaftaran->update([
            'status' => 'ditolak',
            'alasan_penolakan' => $request->alasan_penolakan,
            'status_updated_at' => now(),
            'status_updated_by' => auth()->id(),
        ]);

        // 🔥 TRIGGER EVENT
        event(new PraPendaftaranVerified($praPendaftaran, 'ditolak'));

        return redirect()->back()->with('success', 'Pra-pendaftaran ditolak. Notifikasi otomatis dikirim.');
    }

    /**
     * Contoh 3: Submit Pendaftaran Sertifikasi
     */
    public function submitPendaftaran(Request $request, $id)
    {
        $pendaftaran = PendaftaranSertifikasi::findOrFail($id);
        
        // Validasi data lengkap
        // ... validasi logic ...

        $pendaftaran->update([
            'status' => 'diajukan',
            'tanggal_ajuan' => now(),
        ]);

        // 🔥 TRIGGER EVENT
        event(new PendaftaranDiajukan($pendaftaran));

        return redirect()->back()->with('success', 'Pendaftaran berhasil diajukan. Notifikasi otomatis dikirim.');
    }

    /**
     * Contoh 4: Tetapkan Keputusan Kompetensi
     */
    public function tetapkanKeputusan(Request $request, $id)
    {
        $keputusan = KeputusanSertifikasi::findOrFail($id);
        
        $keputusan->update([
            'keputusan' => $request->keputusan, // 'kompeten' or 'belum_kompeten'
            'status' => 'final',
            'decided_at' => now(),
            'decided_by' => auth()->id(),
            'catatan' => $request->catatan,
        ]);

        // 🔥 TRIGGER EVENT
        event(new KeputusanDitetapkan($keputusan));

        return redirect()->back()->with('success', 'Keputusan berhasil ditetapkan. Notifikasi otomatis dikirim.');
    }

    /**
     * Contoh 5: Terbitkan Sertifikat
     */
    public function terbitkanSertifikat(Request $request, $id)
    {
        $sertifikat = Sertifikat::findOrFail($id);
        
        $sertifikat->update([
            'status' => 'terbit',
            'tanggal_terbit' => now(),
            'tanggal_berlaku_sampai' => now()->addYears(3),
        ]);

        // Generate PDF sertifikat
        // ... PDF generation logic ...

        // 🔥 TRIGGER EVENT - Email dengan link download PDF
        event(new SertifikatDiterbitkan($sertifikat));

        return redirect()->back()->with('success', 'Sertifikat berhasil diterbitkan. Notifikasi otomatis dikirim.');
    }

    /**
     * Contoh 6: Trigger manual dari Artisan Command (untuk testing)
     */
    public function testNotification(Request $request)
    {
        $praPendaftaran = PraPendaftaran::first();
        
        if (!$praPendaftaran) {
            return response()->json(['error' => 'No data found'], 404);
        }

        // Trigger event untuk testing
        event(new PraPendaftaranVerified($praPendaftaran, 'diterima'));

        return response()->json([
            'success' => true,
            'message' => 'Notification queued. Cek queue worker dan notification_logs table.',
        ]);
    }
}

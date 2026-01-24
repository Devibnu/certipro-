<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\PraPendaftaran;
use App\Models\PendaftaranSertifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

/**
 * Controller untuk handle "Lanjut Pendaftaran" dari email.
 * 
 * PRINSIP IDEMPOTENT:
 * - 1 Pra-Pendaftaran = 1 Pendaftaran Sertifikasi (ALWAYS)
 * - User TIDAK bisa daftar ulang
 * - Aman dari double click, refresh, retry
 * - Signed URL dengan expiry untuk security
 */
class ResumePendaftaranController extends Controller
{
    /**
     * Handle "Lanjut Pendaftaran" dari email.
     * 
     * Flow Logic:
     * 1. Validasi signed URL (security)
     * 2. Cek apakah pendaftaran sudah ada
     * 3. Jika SUDAH ADA → redirect ke detail (IDEMPOTENT)
     * 4. Jika BELUM ADA → buat 1x dengan DB lock → redirect ke detail
     * 
     * @param Request $request
     * @param string $praPendaftaranId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function lanjutPendaftaran(Request $request, $praPendaftaranId)
    {
        // SECURITY: Validasi signed URL
        if (!$request->hasValidSignature()) {
            abort(403, 'Link tidak valid atau sudah kadaluarsa. Silakan hubungi admin.');
        }

        try {
            // Load pra-pendaftaran with lock
            $praPendaftaran = PraPendaftaran::findOrFail($praPendaftaranId);

            // VALIDASI: Status HARUS diterima
            if ($praPendaftaran->status !== PraPendaftaran::STATUS_DITERIMA) {
                return redirect()
                    ->route('status-pra-pendaftaran.index')
                    ->with('error', 'Pra-pendaftaran belum diverifikasi. Status saat ini: ' . strtoupper($praPendaftaran->status));
            }

            // IDEMPOTENT CHECK: Apakah pendaftaran sudah ada?
            $pendaftaran = $praPendaftaran->pendaftaranSertifikasi;

            if ($pendaftaran) {
                // CASE 1: Pendaftaran SUDAH ADA → Arahkan ke detail (IDEMPOTENT)
                AuditLog::log(
                    AuditLog::ACTION_VIEW,
                    AuditLog::MODULE_PENDAFTARAN,
                    "User membuka pendaftaran existing dari email: {$pendaftaran->nomor_pendaftaran}",
                    $pendaftaran,
                    null,
                    null,
                    [
                        'event' => 'resume_existing_pendaftaran',
                        'pra_pendaftaran_id' => $praPendaftaran->id,
                        'source' => 'email_link',
                    ]
                );

                return redirect()
                    ->route('pendaftaran-sertifikasi.show', $pendaftaran->id)
                    ->with('info', 'Anda sudah memiliki pendaftaran sertifikasi. Silakan lanjutkan proses yang tersedia.');
            }

            // CASE 2: Pendaftaran BELUM ADA → Buat dengan DB transaction + lock
            DB::beginTransaction();

            try {
                // PESSIMISTIC LOCK: Cegah race condition (double click, concurrent request)
                $praPendaftaran = PraPendaftaran::where('id', $praPendaftaranId)
                    ->lockForUpdate()
                    ->first();

                // Double-check setelah lock (CRITICAL untuk idempotency)
                if ($praPendaftaran->hasPendaftaranSertifikasi()) {
                    DB::rollBack();
                    
                    $existing = $praPendaftaran->pendaftaranSertifikasi;
                    return redirect()
                        ->route('pendaftaran-sertifikasi.show', $existing->id)
                        ->with('info', 'Pendaftaran sertifikasi Anda sudah tersedia.');
                }

                // Generate nomor pendaftaran
                $nomorPendaftaran = $this->generateNomorPendaftaran();

                // Buat pendaftaran sertifikasi (status DIAJUKAN, bukan draft)
                $pendaftaran = PendaftaranSertifikasi::create([
                    'pra_pendaftaran_id' => $praPendaftaran->id,
                    'user_id' => null, // Will be set when user login/register
                    'nama_lengkap' => $praPendaftaran->nama_lengkap,
                    'email' => $praPendaftaran->email,
                    'no_hp' => $praPendaftaran->no_hp,
                    'tipe_peserta' => $praPendaftaran->tipe_peserta,
                    'nik' => $praPendaftaran->nik,
                    'nim' => $praPendaftaran->nim,
                    'institusi' => $praPendaftaran->institusi,
                    'nomor_pendaftaran' => $nomorPendaftaran,
                    'tanggal_daftar' => now(),
                    'status' => PendaftaranSertifikasi::STATUS_DIAJUKAN,
                    'catatan_admin' => 'Pendaftaran dibuat dari email "Lanjut Pendaftaran" (idempotent flow)',
                ]);

                // Log creation
                AuditLog::log(
                    AuditLog::ACTION_CREATE,
                    AuditLog::MODULE_PENDAFTARAN,
                    "Pendaftaran sertifikasi dibuat via email link: {$nomorPendaftaran}",
                    $pendaftaran,
                    null,
                    $pendaftaran->toArray(),
                    [
                        'event' => 'pendaftaran_created_from_email',
                        'pra_pendaftaran_id' => $praPendaftaran->id,
                        'source' => 'email_signed_link',
                    ]
                );

                DB::commit();

                return redirect()
                    ->route('pendaftaran-sertifikasi.show', $pendaftaran->id)
                    ->with('success', 'Pendaftaran sertifikasi berhasil dibuat. Silakan lengkapi data dan pilih skema.');

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            // Log error
            \Log::error('Error di ResumePendaftaranController::lanjutPendaftaran', [
                'pra_pendaftaran_id' => $praPendaftaranId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('status-pra-pendaftaran.index')
                ->with('error', 'Terjadi kesalahan. Silakan coba lagi atau hubungi admin.');
        }
    }

    /**
     * Tampilkan detail pendaftaran sertifikasi (public access dengan token).
     * 
     * Route ini untuk user yang BELUM LOGIN tapi punya signed URL dari email.
     * Menampilkan status, langkah selanjutnya, dan instruksi login/register.
     * 
     * @param Request $request
     * @param int $pendaftaranId
     * @return \Illuminate\View\View
     */
    public function showPendaftaran(Request $request, $pendaftaranId)
    {
        // SECURITY: Validasi signed URL
        if (!$request->hasValidSignature()) {
            abort(403, 'Link tidak valid atau sudah kadaluarsa. Silakan hubungi admin.');
        }

        try {
            $pendaftaran = PendaftaranSertifikasi::with(['praPendaftaran', 'skemaSertifikasi'])
                ->findOrFail($pendaftaranId);

            // Log view
            AuditLog::log(
                AuditLog::ACTION_VIEW,
                AuditLog::MODULE_PENDAFTARAN,
                "User melihat detail pendaftaran via public link: {$pendaftaran->nomor_pendaftaran}",
                $pendaftaran,
                null,
                null,
                [
                    'event' => 'view_pendaftaran_public',
                    'source' => 'email_signed_link',
                ]
            );

            return view('pendaftaran-sertifikasi.public-detail', compact('pendaftaran'));

        } catch (\Exception $e) {
            \Log::error('Error di ResumePendaftaranController::showPendaftaran', [
                'pendaftaran_id' => $pendaftaranId,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('status-pra-pendaftaran.index')
                ->with('error', 'Pendaftaran tidak ditemukan atau link sudah kadaluarsa.');
        }
    }

    /**
     * Generate signed URL untuk "Lanjut Pendaftaran".
     * 
     * URL berlaku 30 hari, tidak bisa ditebak, tidak bisa dipakai untuk pra-pendaftaran lain.
     * 
     * @param PraPendaftaran $praPendaftaran
     * @return string
     */
    public static function generateSignedUrl(PraPendaftaran $praPendaftaran): string
    {
        return URL::temporarySignedRoute(
            'pendaftaran.lanjut',
            now()->addDays(30), // Berlaku 30 hari
            ['praPendaftaranId' => $praPendaftaran->id]
        );
    }

    /**
     * Generate signed URL untuk detail pendaftaran (public access).
     * 
     * @param PendaftaranSertifikasi $pendaftaran
     * @return string
     */
    public static function generateDetailSignedUrl(PendaftaranSertifikasi $pendaftaran): string
    {
        return URL::temporarySignedRoute(
            'pendaftaran-sertifikasi.public-detail',
            now()->addDays(30),
            ['id' => $pendaftaran->id]
        );
    }

    /**
     * Generate nomor pendaftaran unik.
     * 
     * Format: REG + YYYY + XXXX (REG20260001)
     * 
     * @return string
     */
    private function generateNomorPendaftaran(): string
    {
        $year = date('Y');
        
        // CRITICAL: Lock table to prevent duplicate numbers
        $lastNumber = DB::table('pendaftaran_sertifikasi')
            ->whereYear('created_at', $year)
            ->lockForUpdate()
            ->count();

        return 'REG' . $year . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Cek apakah user sudah pernah daftar untuk skema tertentu.
     * 
     * VALIDASI HARD: Cegah duplicate registration untuk skema sama.
     * 
     * @param string $email
     * @param int $skemaId
     * @return bool
     */
    public static function hasActiveRegistration(string $email, int $skemaId): bool
    {
        return PendaftaranSertifikasi::where('email', $email)
            ->where('skema_sertifikasi_id', $skemaId)
            ->whereNotIn('status', [
                PendaftaranSertifikasi::STATUS_DITOLAK,
                PendaftaranSertifikasi::STATUS_BELUM_KOMPETEN,
            ])
            ->exists();
    }
}

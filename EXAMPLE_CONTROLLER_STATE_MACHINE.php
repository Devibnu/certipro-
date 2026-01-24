<?php

namespace App\Http\Controllers;

use App\Models\PendaftaranSertifikasi;
use App\Enums\PendaftaranStatus;
use App\Services\StateTransitionService;
use App\Exceptions\StateTransitionException;
use Illuminate\Http\Request;

/**
 * Example Controller Implementation with State Machine
 * 
 * This demonstrates how to use StateTransitionService in controllers
 */
class PendaftaranSertifikasiController extends Controller
{
    public function __construct(
        private StateTransitionService $stateService
    ) {}

    /**
     * Display pendaftaran with state-aware UI
     */
    public function show(PendaftaranSertifikasi $pendaftaran)
    {
        // Calculate what actions are allowed
        $guards = [
            'can_edit' => [
                'can' => !$this->stateService->isLocked($pendaftaran) && 
                         $pendaftaran->status === PendaftaranStatus::DRAFT->value,
                'reason' => $this->getEditBlockReason($pendaftaran),
            ],
            'can_submit' => [
                'can' => $this->canSubmit($pendaftaran),
                'reason' => $this->getSubmitBlockReason($pendaftaran),
            ],
            'can_verify' => [
                'can' => $pendaftaran->status === PendaftaranStatus::DIAJUKAN->value && 
                         auth()->user()->can('verify-pendaftaran'),
                'reason' => $this->getVerifyBlockReason($pendaftaran),
            ],
            'can_lock' => [
                'can' => $pendaftaran->status === PendaftaranStatus::DIVERIFIKASI->value && 
                         auth()->user()->can('lock-pendaftaran'),
                'reason' => $this->getLockBlockReason($pendaftaran),
            ],
        ];

        return view('pendaftaran.show', compact('pendaftaran', 'guards'));
    }

    /**
     * Submit pendaftaran (draft → diajukan)
     */
    public function submit(PendaftaranSertifikasi $pendaftaran)
    {
        try {
            $this->stateService->transitionPendaftaran(
                $pendaftaran,
                PendaftaranStatus::DIAJUKAN
            );

            return redirect()
                ->route('pendaftaran.show', $pendaftaran)
                ->with('success', 'Pendaftaran berhasil diajukan. Menunggu verifikasi admin.');

        } catch (StateTransitionException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Verify pendaftaran (diajukan → diverifikasi)
     */
    public function verify(PendaftaranSertifikasi $pendaftaran, Request $request)
    {
        $this->authorize('verify-pendaftaran');

        $validated = $request->validate([
            'catatan' => 'nullable|string|max:1000',
        ]);

        try {
            $this->stateService->transitionPendaftaran(
                $pendaftaran,
                PendaftaranStatus::DIVERIFIKASI,
                $validated['catatan'] ?? null
            );

            return redirect()
                ->route('pendaftaran.show', $pendaftaran)
                ->with('success', 'Pendaftaran berhasil diverifikasi.');

        } catch (StateTransitionException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject pendaftaran (diajukan → ditolak)
     */
    public function reject(PendaftaranSertifikasi $pendaftaran, Request $request)
    {
        $this->authorize('verify-pendaftaran');

        $validated = $request->validate([
            'alasan' => 'required|string|max:1000',
        ]);

        try {
            $this->stateService->transitionPendaftaran(
                $pendaftaran,
                PendaftaranStatus::DITOLAK,
                $validated['alasan']
            );

            return redirect()
                ->route('pendaftaran.index')
                ->with('success', 'Pendaftaran ditolak.');

        } catch (StateTransitionException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Lock pendaftaran for asesmen (diverifikasi → dikunci)
     */
    public function lock(PendaftaranSertifikasi $pendaftaran)
    {
        $this->authorize('lock-pendaftaran');

        try {
            // First transition to DIKUNCI
            $this->stateService->transitionPendaftaran(
                $pendaftaran,
                PendaftaranStatus::DIKUNCI
            );

            // Then create asesmen
            $asesmen = $this->stateService->createAsesmen($pendaftaran);

            return redirect()
                ->route('asesmen.show', $asesmen)
                ->with('success', 'Pendaftaran terkunci. Asesmen telah dibuat.');

        } catch (StateTransitionException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // === GUARD HELPERS ===

    private function canSubmit(PendaftaranSertifikasi $pendaftaran): bool
    {
        if ($pendaftaran->status !== PendaftaranStatus::DRAFT->value) {
            return false;
        }

        if ($this->stateService->isLocked($pendaftaran)) {
            return false;
        }

        if (!$pendaftaran->skema_sertifikasi_id) {
            return false;
        }

        if (!$pendaftaran->nama_lengkap || !$pendaftaran->email) {
            return false;
        }

        return true;
    }

    private function getEditBlockReason(PendaftaranSertifikasi $pendaftaran): ?string
    {
        if ($this->stateService->isLocked($pendaftaran)) {
            return 'Data telah terkunci: ' . ($pendaftaran->locked_reason ?? 'Tidak dapat diubah');
        }

        if ($pendaftaran->status !== PendaftaranStatus::DRAFT->value) {
            return 'Hanya pendaftaran dengan status DRAFT yang dapat diedit';
        }

        return null;
    }

    private function getSubmitBlockReason(PendaftaranSertifikasi $pendaftaran): ?string
    {
        if ($pendaftaran->status !== PendaftaranStatus::DRAFT->value) {
            return 'Hanya pendaftaran dengan status DRAFT yang dapat diajukan';
        }

        if ($this->stateService->isLocked($pendaftaran)) {
            return 'Data telah terkunci';
        }

        if (!$pendaftaran->skema_sertifikasi_id) {
            return 'Skema sertifikasi belum dipilih';
        }

        if (!$pendaftaran->nama_lengkap || !$pendaftaran->email) {
            return 'Data diri belum lengkap (nama dan email wajib)';
        }

        if (!$pendaftaran->nik || !$pendaftaran->tempat_lahir || !$pendaftaran->tanggal_lahir) {
            return 'Data identitas belum lengkap';
        }

        return null;
    }

    private function getVerifyBlockReason(PendaftaranSertifikasi $pendaftaran): ?string
    {
        if ($pendaftaran->status !== PendaftaranStatus::DIAJUKAN->value) {
            return 'Hanya pendaftaran dengan status DIAJUKAN yang dapat diverifikasi';
        }

        if (!auth()->user()->can('verify-pendaftaran')) {
            return 'Anda tidak memiliki izin untuk memverifikasi pendaftaran';
        }

        return null;
    }

    private function getLockBlockReason(PendaftaranSertifikasi $pendaftaran): ?string
    {
        if ($pendaftaran->status !== PendaftaranStatus::DIVERIFIKASI->value) {
            return 'Hanya pendaftaran dengan status DIVERIFIKASI yang dapat dikunci';
        }

        if (!$pendaftaran->jadwalAsesmen()->exists()) {
            return 'Jadwal asesmen belum dibuat';
        }

        if (!$pendaftaran->jadwalAsesmen->asesor_id) {
            return 'Asesor belum ditugaskan';
        }

        if (!auth()->user()->can('lock-pendaftaran')) {
            return 'Anda tidak memiliki izin untuk mengunci pendaftaran';
        }

        return null;
    }
}

<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * ============================================================================
 * SamplingController
 * ============================================================================
 * 
 * Handles Sampling Audit functionality for Quality Control (ISO 17024).
 * 
 * Purpose:
 * - Allow Komite Teknis to mark asesmen for sampling audit
 * - Provide documented quality control mechanism
 * - Demonstrate impartiality & quality assurance to auditors
 * 
 * SOP Narrative:
 * "Sebagai bagian dari pengendalian mutu, LSP melakukan sampling asesmen
 * secara berkala untuk memastikan konsistensi dan objektivitas penilaian."
 * 
 * @see ISO 17024:2012 Clause 4.3 (Impartiality)
 * @see ISO 17024:2012 Clause 9.4 (Internal audits)
 * ============================================================================
 */
class SamplingController extends Controller
{
    /**
     * Mark an asesmen for sampling audit.
     */
    public function mark(Request $request, $asesmenId): JsonResponse
    {
        $asesmen = Asesmen::with(['pendaftaran.keputusanSertifikasi'])->findOrFail($asesmenId);

        // Check if sampling can be modified
        if (!$asesmen->canModifySampling()) {
            $reason = $asesmen->isSelesai() 
                ? 'Keputusan sertifikasi sudah dikunci.'
                : 'Asesmen belum selesai.';
            
            return response()->json([
                'success' => false,
                'message' => "Sampling tidak dapat diubah. {$reason}",
            ], 403);
        }

        // Validate request
        $request->validate([
            'sampling_note' => 'nullable|string|max:2000',
        ]);

        // Check if already sampled
        if ($asesmen->isSampled()) {
            return response()->json([
                'success' => false,
                'message' => 'Asesmen sudah ditandai untuk sampling.',
            ], 422);
        }

        // Mark as sampled
        $asesmen->update([
            'is_sampled' => true,
            'sampled_at' => now(),
            'sampled_by' => Auth::id(),
            'sampling_note' => $request->input('sampling_note'),
        ]);

        // Audit log
        AuditLog::log(
            AuditLog::ACTION_SAMPLING_MARKED,
            AuditLog::MODULE_SAMPLING,
            "Menandai asesmen sebagai Sampling Audit untuk pendaftaran {$asesmen->pendaftaran?->nomor_pendaftaran}",
            $asesmen,
            null,
            [
                'is_sampled' => true,
                'sampled_at' => $asesmen->sampled_at->toIso8601String(),
                'sampled_by' => Auth::id(),
                'sampling_note' => $asesmen->sampling_note,
            ],
            [
                'event' => 'asesmen_marked_sampling',
                'asesmen_id' => $asesmen->id,
                'pendaftaran_id' => $asesmen->pendaftaran_id,
                'nomor_pendaftaran' => $asesmen->pendaftaran?->nomor_pendaftaran,
                'asesor_id' => $asesmen->asesor_id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Asesmen berhasil ditandai untuk Sampling Audit.',
            'data' => [
                'is_sampled' => true,
                'sampled_at' => $asesmen->sampled_at->format('d M Y H:i'),
                'sampled_by_name' => Auth::user()->name,
                'sampling_note' => $asesmen->sampling_note,
            ],
        ]);
    }

    /**
     * Unmark an asesmen from sampling audit.
     */
    public function unmark(Request $request, $asesmenId): JsonResponse
    {
        $asesmen = Asesmen::with(['pendaftaran.keputusanSertifikasi'])->findOrFail($asesmenId);

        // Check if sampling can be modified
        if (!$asesmen->canModifySampling()) {
            return response()->json([
                'success' => false,
                'message' => 'Sampling tidak dapat diubah. Keputusan sertifikasi sudah dikunci.',
            ], 403);
        }

        // Check if not sampled
        if (!$asesmen->isSampled()) {
            return response()->json([
                'success' => false,
                'message' => 'Asesmen tidak dalam status sampling.',
            ], 422);
        }

        // Store old values for audit
        $oldValues = [
            'is_sampled' => $asesmen->is_sampled,
            'sampled_at' => $asesmen->sampled_at?->toIso8601String(),
            'sampled_by' => $asesmen->sampled_by,
            'sampling_note' => $asesmen->sampling_note,
        ];

        // Unmark sampling
        $asesmen->update([
            'is_sampled' => false,
            'sampled_at' => null,
            'sampled_by' => null,
            'sampling_note' => null,
        ]);

        // Audit log
        AuditLog::log(
            AuditLog::ACTION_SAMPLING_UNMARKED,
            AuditLog::MODULE_SAMPLING,
            "Membatalkan Sampling Audit untuk pendaftaran {$asesmen->pendaftaran?->nomor_pendaftaran}",
            $asesmen,
            $oldValues,
            [
                'is_sampled' => false,
                'sampled_at' => null,
                'sampled_by' => null,
                'sampling_note' => null,
            ],
            [
                'event' => 'asesmen_unmarked_sampling',
                'asesmen_id' => $asesmen->id,
                'pendaftaran_id' => $asesmen->pendaftaran_id,
                'nomor_pendaftaran' => $asesmen->pendaftaran?->nomor_pendaftaran,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Sampling Audit dibatalkan.',
        ]);
    }

    /**
     * Update sampling note.
     */
    public function updateNote(Request $request, $asesmenId): JsonResponse
    {
        $asesmen = Asesmen::with(['pendaftaran.keputusanSertifikasi'])->findOrFail($asesmenId);

        // Check if sampling can be modified
        if (!$asesmen->canModifySampling()) {
            return response()->json([
                'success' => false,
                'message' => 'Catatan sampling tidak dapat diubah. Keputusan sertifikasi sudah dikunci.',
            ], 403);
        }

        // Must be sampled first
        if (!$asesmen->isSampled()) {
            return response()->json([
                'success' => false,
                'message' => 'Asesmen belum ditandai untuk sampling.',
            ], 422);
        }

        // Validate request
        $request->validate([
            'sampling_note' => 'required|string|max:2000',
        ], [
            'sampling_note.required' => 'Catatan sampling wajib diisi.',
            'sampling_note.max' => 'Catatan sampling maksimal 2000 karakter.',
        ]);

        $oldNote = $asesmen->sampling_note;
        $newNote = $request->input('sampling_note');

        // Update note
        $asesmen->update([
            'sampling_note' => $newNote,
        ]);

        // Audit log
        AuditLog::log(
            AuditLog::ACTION_SAMPLING_NOTE_UPDATED,
            AuditLog::MODULE_SAMPLING,
            "Mengupdate catatan Sampling Audit untuk pendaftaran {$asesmen->pendaftaran?->nomor_pendaftaran}",
            $asesmen,
            ['sampling_note' => $oldNote],
            ['sampling_note' => $newNote],
            [
                'event' => 'asesmen_sampling_note_updated',
                'asesmen_id' => $asesmen->id,
                'pendaftaran_id' => $asesmen->pendaftaran_id,
                'nomor_pendaftaran' => $asesmen->pendaftaran?->nomor_pendaftaran,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Catatan sampling berhasil diperbarui.',
            'data' => [
                'sampling_note' => $asesmen->sampling_note,
            ],
        ]);
    }

    /**
     * Get sampling status for an asesmen.
     */
    public function status(Request $request, $asesmenId): JsonResponse
    {
        $asesmen = Asesmen::with(['sampledByUser', 'pendaftaran.keputusanSertifikasi'])->findOrFail($asesmenId);

        return response()->json([
            'success' => true,
            'data' => [
                'is_sampled' => $asesmen->isSampled(),
                'sampled_at' => $asesmen->sampled_at?->format('d M Y H:i'),
                'sampled_by_name' => $asesmen->sampledByUser?->name,
                'sampling_note' => $asesmen->sampling_note,
                'can_modify' => $asesmen->canModifySampling(),
                'is_locked' => !$asesmen->canModifySampling(),
            ],
        ]);
    }
}

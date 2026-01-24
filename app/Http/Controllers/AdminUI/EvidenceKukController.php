<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\AuditLog;
use App\Models\EvidenceKuk;
use App\Models\Kuk;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * ============================================================================
 * EvidenceKukController
 * ============================================================================
 * 
 * Handles evidence upload/view/delete per KUK for asesmen process.
 * All files are stored in private storage (not publicly accessible).
 * 
 * Compliance: ISO 17024, BNSP
 * Security: Private storage, whitelist mime types, audit logging
 * ============================================================================
 */
class EvidenceKukController extends Controller
{
    /**
     * List evidence for a specific asesmen.
     */
    public function index(Request $request, $asesmenId): JsonResponse
    {
        $asesmen = Asesmen::with(['evidences.kuk', 'evidences.uploader'])->findOrFail($asesmenId);
        
        $evidences = $asesmen->evidences->map(function ($evidence) {
            return [
                'id' => $evidence->id,
                'kuk_id' => $evidence->kuk_id,
                'kuk_kode' => $evidence->kuk?->kode_kuk,
                'type' => $evidence->type,
                'type_label' => $evidence->type_label,
                'file_icon' => $evidence->file_icon,
                'display_name' => $evidence->display_name,
                'description' => $evidence->description,
                'file_size_human' => $evidence->file_size_human,
                'uploader_name' => $evidence->uploader?->name ?? 'Unknown',
                'created_at' => $evidence->created_at->format('d M Y H:i'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $evidences,
            'is_locked' => $asesmen->isLocked(),
        ]);
    }

    /**
     * List evidence for a specific KUK within an asesmen.
     */
    public function listByKuk(Request $request, $asesmenId, $kukId): JsonResponse
    {
        $asesmen = Asesmen::findOrFail($asesmenId);
        $kuk = Kuk::findOrFail($kukId);

        $evidences = EvidenceKuk::with('uploader')
            ->where('asesmen_id', $asesmenId)
            ->where('kuk_id', $kukId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($evidence) {
                return [
                    'id' => $evidence->id,
                    'type' => $evidence->type,
                    'type_label' => $evidence->type_label,
                    'file_icon' => $evidence->file_icon,
                    'display_name' => $evidence->display_name,
                    'description' => $evidence->description,
                    'file_size_human' => $evidence->file_size_human,
                    'link_url' => $evidence->link_url,
                    'uploader_name' => $evidence->uploader?->name ?? 'Unknown',
                    'created_at' => $evidence->created_at->format('d M Y H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $evidences,
            'is_locked' => $asesmen->isLocked(),
            'kuk' => [
                'id' => $kuk->id,
                'kode' => $kuk->kode_kuk,
                'pernyataan' => $kuk->pernyataan_unjuk_kerja,
            ],
        ]);
    }

    /**
     * Upload evidence file for a KUK.
     */
    public function uploadFile(Request $request, $asesmenId, $kukId): JsonResponse
    {
        // Validate asesmen and KUK
        $asesmen = Asesmen::findOrFail($asesmenId);
        $kuk = Kuk::findOrFail($kukId);

        // Check if asesmen is locked
        if ($asesmen->isLocked()) {
            return response()->json([
                'success' => false,
                'message' => 'Asesmen sudah dikunci. Evidence tidak dapat ditambahkan.',
            ], 403);
        }

        // Validate request
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:' . (EvidenceKuk::MAX_FILE_SIZE / 1024), // Convert to KB
                'mimes:' . implode(',', EvidenceKuk::ALLOWED_EXTENSIONS),
            ],
            'description' => 'nullable|string|max:500',
        ], [
            'file.required' => 'File evidence wajib diupload.',
            'file.max' => 'Ukuran file maksimal ' . EvidenceKuk::getMaxFileSizeMB() . ' MB.',
            'file.mimes' => 'Format file yang diizinkan: ' . EvidenceKuk::getAllowedExtensionsString(),
        ]);

        $file = $request->file('file');
        
        // Additional mime type validation
        if (!in_array($file->getMimeType(), EvidenceKuk::ALLOWED_MIME_TYPES)) {
            return response()->json([
                'success' => false,
                'message' => 'Tipe file tidak diizinkan.',
            ], 422);
        }

        // Generate unique filename (UUID based)
        $extension = $file->getClientOriginalExtension();
        $uuid = Str::uuid()->toString();
        $filename = "{$uuid}.{$extension}";
        $storagePath = EvidenceKuk::STORAGE_PATH . "/{$asesmenId}";

        // Store file in private storage
        $filePath = $file->storeAs($storagePath, $filename, EvidenceKuk::STORAGE_DISK);

        if (!$filePath) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan file. Silakan coba lagi.',
            ], 500);
        }

        // Create evidence record
        $evidence = EvidenceKuk::create([
            'asesmen_id' => $asesmen->id,
            'kuk_id' => $kuk->id,
            'uploaded_by' => Auth::id(),
            'type' => EvidenceKuk::TYPE_FILE,
            'file_path' => $filePath,
            'file_name_original' => $file->getClientOriginalName(),
            'file_mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'description' => $request->input('description'),
        ]);

        // Audit log
        AuditLog::log(
            AuditLog::ACTION_EVIDENCE_UPLOADED,
            AuditLog::MODULE_EVIDENCE,
            "Mengupload evidence file '{$evidence->file_name_original}' untuk KUK {$kuk->kode_kuk}",
            $evidence,
            null,
            null,
            [
                'event' => 'evidence_uploaded',
                'asesmen_id' => $asesmen->id,
                'kuk_id' => $kuk->id,
                'kuk_kode' => $kuk->kode_kuk,
                'file_name' => $evidence->file_name_original,
                'file_size' => $evidence->file_size,
                'file_mime' => $evidence->file_mime_type,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Evidence berhasil diupload.',
            'data' => [
                'id' => $evidence->id,
                'type' => $evidence->type,
                'type_label' => $evidence->type_label,
                'file_icon' => $evidence->file_icon,
                'display_name' => $evidence->display_name,
                'description' => $evidence->description,
                'file_size_human' => $evidence->file_size_human,
                'uploader_name' => Auth::user()->name,
                'created_at' => $evidence->created_at->format('d M Y H:i'),
            ],
        ]);
    }

    /**
     * Add evidence link for a KUK.
     */
    public function addLink(Request $request, $asesmenId, $kukId): JsonResponse
    {
        // Validate asesmen and KUK
        $asesmen = Asesmen::findOrFail($asesmenId);
        $kuk = Kuk::findOrFail($kukId);

        // Check if asesmen is locked
        if ($asesmen->isLocked()) {
            return response()->json([
                'success' => false,
                'message' => 'Asesmen sudah dikunci. Evidence tidak dapat ditambahkan.',
            ], 403);
        }

        // Validate request
        $request->validate([
            'link_url' => 'required|url|max:1000',
            'description' => 'nullable|string|max:500',
        ], [
            'link_url.required' => 'URL link wajib diisi.',
            'link_url.url' => 'Format URL tidak valid.',
            'link_url.max' => 'URL maksimal 1000 karakter.',
        ]);

        // Create evidence record
        $evidence = EvidenceKuk::create([
            'asesmen_id' => $asesmen->id,
            'kuk_id' => $kuk->id,
            'uploaded_by' => Auth::id(),
            'type' => EvidenceKuk::TYPE_LINK,
            'link_url' => $request->input('link_url'),
            'description' => $request->input('description'),
        ]);

        // Audit log
        AuditLog::log(
            AuditLog::ACTION_EVIDENCE_LINK_ADDED,
            AuditLog::MODULE_EVIDENCE,
            "Menambahkan evidence link untuk KUK {$kuk->kode_kuk}",
            $evidence,
            null,
            null,
            [
                'event' => 'evidence_link_added',
                'asesmen_id' => $asesmen->id,
                'kuk_id' => $kuk->id,
                'kuk_kode' => $kuk->kode_kuk,
                'link_url' => $evidence->link_url,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Evidence link berhasil ditambahkan.',
            'data' => [
                'id' => $evidence->id,
                'type' => $evidence->type,
                'type_label' => $evidence->type_label,
                'file_icon' => 'fas fa-link text-primary',
                'display_name' => $evidence->display_name,
                'description' => $evidence->description,
                'link_url' => $evidence->link_url,
                'uploader_name' => Auth::user()->name,
                'created_at' => $evidence->created_at->format('d M Y H:i'),
            ],
        ]);
    }

    /**
     * Download evidence file.
     */
    public function download(Request $request, $evidenceId): BinaryFileResponse|JsonResponse
    {
        $evidence = EvidenceKuk::with('asesmen')->findOrFail($evidenceId);

        if (!$evidence->isFile()) {
            return response()->json([
                'success' => false,
                'message' => 'Evidence ini bukan file.',
            ], 400);
        }

        if (!$evidence->fileExists()) {
            return response()->json([
                'success' => false,
                'message' => 'File tidak ditemukan.',
            ], 404);
        }

        // Audit log
        AuditLog::log(
            AuditLog::ACTION_EVIDENCE_DOWNLOADED,
            AuditLog::MODULE_EVIDENCE,
            "Mengunduh evidence file '{$evidence->file_name_original}'",
            $evidence,
            null,
            null,
            [
                'event' => 'evidence_downloaded',
                'asesmen_id' => $evidence->asesmen_id,
                'kuk_id' => $evidence->kuk_id,
                'file_name' => $evidence->file_name_original,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        return response()->download(
            $evidence->getFullPath(),
            $evidence->file_name_original,
            ['Content-Type' => $evidence->file_mime_type]
        );
    }

    /**
     * Delete evidence.
     */
    public function destroy(Request $request, $evidenceId): JsonResponse
    {
        $evidence = EvidenceKuk::with(['asesmen', 'kuk'])->findOrFail($evidenceId);

        // Check if asesmen is locked
        if ($evidence->asesmen->isLocked()) {
            return response()->json([
                'success' => false,
                'message' => 'Asesmen sudah dikunci. Evidence tidak dapat dihapus.',
            ], 403);
        }

        // Store info for audit log
        $logData = [
            'event' => 'evidence_deleted',
            'asesmen_id' => $evidence->asesmen_id,
            'kuk_id' => $evidence->kuk_id,
            'kuk_kode' => $evidence->kuk?->kode_kuk,
            'type' => $evidence->type,
            'file_name' => $evidence->file_name_original,
            'link_url' => $evidence->link_url,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        $displayName = $evidence->display_name;
        $kukKode = $evidence->kuk?->kode_kuk;

        // Delete evidence (file will be deleted via model boot)
        $evidence->delete();

        // Audit log
        AuditLog::log(
            AuditLog::ACTION_EVIDENCE_DELETED,
            AuditLog::MODULE_EVIDENCE,
            "Menghapus evidence '{$displayName}' dari KUK {$kukKode}",
            null,
            null,
            null,
            $logData
        );

        return response()->json([
            'success' => true,
            'message' => 'Evidence berhasil dihapus.',
        ]);
    }
}

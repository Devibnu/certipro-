<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Services\EmailAuditEvidenceService;
use Illuminate\Http\Request;

/**
 * ============================================================================
 * Controller: EmailAuditEvidenceController
 * ============================================================================
 * Handles PDF audit evidence generation for email notifications.
 * Used for BNSP/ISO 17024 compliance and audit purposes.
 * 
 * Routes:
 * - GET /adminui/audit/email/{type}/{id}/pdf - Download PDF
 * - GET /adminui/audit/email/{type}/{id}/preview - Stream/view PDF
 * 
 * Authorization: Super Admin, Admin only
 * ============================================================================
 */
class EmailAuditEvidenceController extends Controller
{
    protected EmailAuditEvidenceService $auditService;

    public function __construct(EmailAuditEvidenceService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Download audit evidence PDF
     * 
     * @route GET /adminui/audit/email/{type}/{id}/pdf
     */
    public function download(Request $request, string $type, int $id)
    {
        // Authorization check
        if (!$this->canAccessAuditEvidence()) {
            abort(403, 'Anda tidak memiliki akses untuk mengunduh bukti audit.');
        }

        // Generate PDF
        $result = $this->auditService->generatePdf($type, $id);

        if (!$result['success']) {
            return back()->with('error', $result['error']);
        }

        // Return PDF as download
        return $result['pdf']->download($result['filename']);
    }

    /**
     * Stream/Preview audit evidence PDF in browser
     * 
     * @route GET /adminui/audit/email/{type}/{id}/preview
     */
    public function preview(Request $request, string $type, int $id)
    {
        // Authorization check
        if (!$this->canAccessAuditEvidence()) {
            abort(403, 'Anda tidak memiliki akses untuk melihat bukti audit.');
        }

        // Generate PDF
        $result = $this->auditService->generatePdf($type, $id);

        if (!$result['success']) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'],
                ], 404);
            }
            return back()->with('error', $result['error']);
        }

        // Return PDF as stream (view in browser)
        return $result['pdf']->stream($result['filename']);
    }

    /**
     * Get supported reference types
     * 
     * @route GET /adminui/audit/email/types
     */
    public function types(Request $request)
    {
        if (!$this->canAccessAuditEvidence()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'types' => $this->auditService->getSupportedTypes(),
        ]);
    }

    /**
     * Check if user has permission to access audit evidence
     * Uses RBAC methods from User model
     */
    protected function canAccessAuditEvidence(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        // Super Admin or Admin can access
        return $user->isSuperAdmin() || $user->isAdmin();
    }
}

<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Services\EmailNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * ============================================================================
 * Controller: EmailPreviewController
 * ============================================================================
 * Handles email preview and resend functionality for Admin Panel.
 * 
 * Features:
 * - Preview email HTML without sending
 * - Resend email with confirmation
 * - View email history
 * 
 * Authorization: SUPER_ADMIN, ADMIN only
 * 
 * Compliance: BNSP, ISO 17024
 * ============================================================================
 */
class EmailPreviewController extends Controller
{
    /**
     * Allowed roles for email preview/resend
     */
    protected array $allowedRoles = [
        'super admin',
        'super_admin',
        'superadmin',
        'admin',
        'administrator',
    ];

    /**
     * EmailNotificationService instance
     */
    protected EmailNotificationService $emailService;

    /**
     * Create a new controller instance.
     */
    public function __construct(EmailNotificationService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Preview email HTML
     * 
     * GET /adminui/email/preview/{type}/{id}
     * 
     * @param Request $request
     * @param string $type Email type (pra-diterima, pra-ditolak, etc.)
     * @param int $id Model ID
     * @return JsonResponse
     */
    public function preview(Request $request, string $type, int $id): JsonResponse
    {
        // Authorization check
        if (!$this->authorizeAccess()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke fitur ini.',
            ], 403);
        }

        // Validate email type
        if (!in_array($type, $this->emailService->getSupportedTypes())) {
            return response()->json([
                'success' => false,
                'message' => 'Tipe email tidak valid.',
            ], 400);
        }

        // Get preview
        $result = $this->emailService->preview($type, $id);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['error'],
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'html' => $result['html'],
                'subject' => $result['subject'],
                'email_to' => $result['email_to'],
                'reference' => $result['reference'],
                'last_sent' => $result['last_sent'],
                'resend_count' => $result['resend_count'],
                'max_resend' => EmailNotificationService::MAX_RESEND_ATTEMPTS,
                'can_resend' => $result['resend_count'] < EmailNotificationService::MAX_RESEND_ATTEMPTS,
            ],
        ]);
    }

    /**
     * Resend email
     * 
     * POST /adminui/email/resend/{type}/{id}
     * 
     * @param Request $request
     * @param string $type Email type
     * @param int $id Model ID
     * @return JsonResponse
     */
    public function resend(Request $request, string $type, int $id): JsonResponse
    {
        // Authorization check
        if (!$this->authorizeAccess()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke fitur ini.',
            ], 403);
        }

        // Validate email type
        if (!in_array($type, $this->emailService->getSupportedTypes())) {
            return response()->json([
                'success' => false,
                'message' => 'Tipe email tidak valid.',
            ], 400);
        }

        // Validate request
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        // Resend email
        $result = $this->emailService->resend($type, $id, $request->input('reason'));

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ], $result['success'] ? 200 : 400);
    }

    /**
     * Get email history for a model
     * 
     * GET /adminui/email/history/{type}/{id}
     * 
     * @param Request $request
     * @param string $type Email type
     * @param int $id Model ID
     * @return JsonResponse
     */
    public function history(Request $request, string $type, int $id): JsonResponse
    {
        // Authorization check
        if (!$this->authorizeAccess()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke fitur ini.',
            ], 403);
        }

        // Get config and model
        $config = $this->emailService->getTypeConfig($type);
        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'Tipe email tidak valid.',
            ], 400);
        }

        $model = $config['model']::find($id);
        if (!$model) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        $history = $this->emailService->getEmailHistory($model);

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Get email status info for a model
     * 
     * GET /adminui/email/status/{type}/{id}
     * 
     * @param Request $request
     * @param string $type Email type
     * @param int $id Model ID
     * @return JsonResponse
     */
    public function status(Request $request, string $type, int $id): JsonResponse
    {
        // Authorization check
        if (!$this->authorizeAccess()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke fitur ini.',
            ], 403);
        }

        // Get config and model
        $config = $this->emailService->getTypeConfig($type);
        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'Tipe email tidak valid.',
            ], 400);
        }

        $model = $config['model']::find($id);
        if (!$model) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'has_been_sent' => $this->emailService->hasBeenSent($model, $type),
                'last_sent' => $this->emailService->getLastSentTime($model, $type),
                'resend_count' => $this->emailService->getResendCount($model, $type),
                'max_resend' => EmailNotificationService::MAX_RESEND_ATTEMPTS,
                'can_resend' => $this->emailService->getResendCount($model, $type) < EmailNotificationService::MAX_RESEND_ATTEMPTS,
            ],
        ]);
    }

    /**
     * Check if current user has access
     */
    protected function authorizeAccess(): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        // Check role - using multiple possible methods
        if (method_exists($user, 'hasRole')) {
            foreach ($this->allowedRoles as $role) {
                if ($user->hasRole($role)) {
                    return true;
                }
            }
        }

        // Check via role relationship
        if (method_exists($user, 'roles') && $user->roles) {
            $userRoles = $user->roles->pluck('name')->map(fn($r) => strtolower($r))->toArray();
            foreach ($this->allowedRoles as $role) {
                if (in_array(strtolower($role), $userRoles)) {
                    return true;
                }
            }
        }

        // Check via role attribute
        if (isset($user->role)) {
            if (in_array(strtolower($user->role), $this->allowedRoles)) {
                return true;
            }
        }

        // Check via is_admin flag
        if (isset($user->is_admin) && $user->is_admin) {
            return true;
        }

        // Check via is_super_admin flag
        if (isset($user->is_super_admin) && $user->is_super_admin) {
            return true;
        }

        return false;
    }
}

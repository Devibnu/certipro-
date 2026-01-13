<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AuditLog;
use App\Models\Role;
use Symfony\Component\HttpFoundation\Response;

/**
 * ============================================================================
 * CheckPermission Middleware - ISO 17024 & BNSP Compliant
 * ============================================================================
 * 
 * This middleware enforces permission-based access control for all critical
 * actions in the LSP certification platform. It is the source of truth for
 * access control - UI visibility must follow this middleware's logic.
 * 
 * DESIGN PRINCIPLES:
 * 1. Backend is source of truth - never rely on UI-only controls
 * 2. All denied access is logged for audit trail
 * 3. Super Admin bypass for full system access
 * 4. Standardized error responses for all denied access
 * 
 * USAGE IN ROUTES:
 * - Single permission: ->middleware('permission:pendaftaran.view')
 * - Multiple (OR): ->middleware('permission:pendaftaran.view|pendaftaran.manage')
 * - Legacy names still supported for backward compatibility
 * 
 * @see ISO 17024:2012 Clause 5.2 (Confidentiality)
 * @see BNSP Pedoman 201 (Keamanan Data)
 */
class CheckPermission
{
    /**
     * Audit action constant for access denied
     */
    const AUDIT_ACTION_ACCESS_DENIED = 'access_denied';
    
    /**
     * Module name for audit logging
     */
    const AUDIT_MODULE = 'access_control';

    /**
     * Handle an incoming request.
     * Expect a permission name as parameter, e.g. check.permission:pendaftaran.view
     * Also supports legacy menu names and OR logic with | separator
     * 
     * @param Request $request
     * @param Closure $next
     * @param string|null $permission Permission name(s), separated by | for OR logic
     * @return Response
     */
    public function handle(Request $request, Closure $next, $permission = null): Response
    {
        $user = Auth::user();

        // =============================================
        // 1. CHECK AUTHENTICATION
        // =============================================
        if (!$user) {
            return $this->handleUnauthenticated($request);
        }

        // =============================================
        // 2. SUPER ADMIN BYPASS
        // Super Admin has full access to everything
        // =============================================
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // =============================================
        // 3. NO PERMISSION SPECIFIED = ALLOW
        // Route accessible to all authenticated users
        // =============================================
        if (empty($permission)) {
            return $next($request);
        }

        // =============================================
        // 4. CHECK PERMISSION(S)
        // Supports OR logic with | separator
        // =============================================
        $requiredPermissions = array_map('trim', explode('|', $permission));
        
        // Refresh user data for accurate permission check
        $user->refresh();
        
        foreach ($requiredPermissions as $perm) {
            if ($user->hasPermission($perm)) {
                return $next($request);
            }
        }

        // =============================================
        // 5. ACCESS DENIED - LOG AND RESPOND
        // =============================================
        return $this->handleAccessDenied($request, $user, $requiredPermissions);
    }

    /**
     * Handle unauthenticated request
     * 
     * @param Request $request
     * @return Response
     */
    protected function handleUnauthenticated(Request $request): Response
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'error' => 'unauthenticated',
                'message' => 'Sesi Anda telah berakhir. Silakan login kembali.',
                'code' => 401,
            ], 401);
        }

        return redirect()->route('login')
            ->with('error', 'Silakan login terlebih dahulu.');
    }

    /**
     * Handle access denied - log for audit and return standardized response
     * 
     * @param Request $request
     * @param mixed $user
     * @param array $requiredPermissions
     * @return Response
     */
    protected function handleAccessDenied(Request $request, $user, array $requiredPermissions): Response
    {
        // Log for audit trail (ISO 17024 requirement)
        $this->logAccessDenied($request, $user, $requiredPermissions);
        
        // Prepare standardized error response
        $requestId = $this->generateRequestId();
        $errorData = [
            'success' => false,
            'error' => 'forbidden',
            'message' => 'Anda tidak memiliki izin untuk mengakses fitur ini.',
            'code' => 403,
            'required_permission' => implode(' atau ', $requiredPermissions),
            'timestamp' => now()->toIso8601String(),
            'request_id' => $requestId,
        ];

        // JSON response for API/AJAX
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($errorData, 403);
        }

        // HTML response for web requests
        return $this->renderAccessDeniedPage($request, $user, $requiredPermissions, $requestId);
    }

    /**
     * Log access denied event for audit trail
     * 
     * @param Request $request
     * @param mixed $user
     * @param array $requiredPermissions
     */
    protected function logAccessDenied(Request $request, $user, array $requiredPermissions): void
    {
        try {
            AuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role ?? 'unknown',
                'action' => self::AUDIT_ACTION_ACCESS_DENIED,
                'module' => self::AUDIT_MODULE,
                'description' => sprintf(
                    'Akses ditolak ke %s. Izin diperlukan: %s',
                    $request->path(),
                    implode(' atau ', $requiredPermissions)
                ),
                'metadata' => [
                    'required_permissions' => $requiredPermissions,
                    'user_permissions' => $this->getUserPermissions($user),
                    'user_roles' => $user->roles->pluck('name')->toArray(),
                    'route_name' => $request->route()?->getName(),
                    'route_action' => $request->route()?->getActionName(),
                    'referer' => $request->header('Referer'),
                    'request_id' => $this->generateRequestId(),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Log to file if database logging fails
            \Log::error('Failed to log access denied to audit_logs', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'required_permissions' => $requiredPermissions,
                'url' => $request->fullUrl(),
            ]);
        }
    }

    /**
     * Get user's current permissions for logging
     * 
     * @param mixed $user
     * @return array
     */
    protected function getUserPermissions($user): array
    {
        try {
            $rbacPermissions = $user->getAllPermissions()->pluck('name')->toArray();
            $legacyPermissions = is_array($user->permissions) ? $user->permissions : [];
            
            return [
                'rbac' => $rbacPermissions,
                'legacy' => $legacyPermissions,
            ];
        } catch (\Exception $e) {
            return ['error' => 'Could not retrieve permissions'];
        }
    }

    /**
     * Render access denied page for web requests
     * 
     * @param Request $request
     * @param mixed $user
     * @param array $requiredPermissions
     * @param string $requestId
     * @return Response
     */
    protected function renderAccessDeniedPage(Request $request, $user, array $requiredPermissions, string $requestId): Response
    {
        // Check if custom 403 view exists
        if (view()->exists('adminui.errors.403')) {
            return response()->view('adminui.errors.403', [
                'user' => $user,
                'required_permissions' => $requiredPermissions,
                'attempted_url' => $request->fullUrl(),
                'timestamp' => now(),
                'request_id' => $requestId,
            ], 403);
        }

        // Fallback to redirect with error message
        return redirect()->route('adminui.dashboard')
            ->with('error', 'Anda tidak memiliki izin untuk mengakses halaman tersebut. Hubungi administrator jika Anda memerlukan akses.');
    }

    /**
     * Generate unique request ID for tracking
     * 
     * @return string
     */
    protected function generateRequestId(): string
    {
        return 'REQ-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 12));
    }
}

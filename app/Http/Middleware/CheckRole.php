<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Expect role name(s) as parameter, e.g. check.role:asesor or check.role:admin|komite_teknis
     */
    public function handle(Request $request, Closure $next, string $roles = null)
    {
        $user = Auth::user();

        // If not authenticated, redirect to login
        if (!$user) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda harus login terlebih dahulu.'
                ], 401);
            }
            return redirect()->route('login');
        }

        // Super Admin bypass - full access
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // If no specific role required, allow
        if (!$roles) {
            return $next($request);
        }

        // Parse roles (separated by |)
        $allowedRoles = array_map('trim', explode('|', $roles));

        // Check if user has any of the required roles using new RBAC
        if ($user->hasAnyRole($allowedRoles)) {
            return $next($request);
        }

        // Not allowed - log for audit
        \Log::warning('Role check failed', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_roles' => $user->roles->pluck('name')->toArray(),
            'legacy_role' => $user->role,
            'required_roles' => $allowedRoles,
            'ip' => $request->ip(),
            'url' => $request->fullUrl(),
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk fitur ini.'
            ], 403);
        }

        return redirect()->route('adminui.dashboard')
            ->with('error', 'Anda tidak memiliki role yang diperlukan untuk mengakses halaman tersebut.');
    }
}

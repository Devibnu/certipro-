<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ============================================================================
 * PasswordResetLinkController
 * ============================================================================
 * Handles forgot password requests (self-service)
 * 
 * Compliance: ISO 17024, ISO 27001
 * Security: Generic responses to prevent email enumeration
 * ============================================================================
 */
class PasswordResetLinkController extends Controller
{
    protected PasswordResetService $passwordResetService;

    public function __construct(PasswordResetService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }

    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('adminui.auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     * Always returns generic message for security.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $result = $this->passwordResetService->sendResetSelfService(
            $request->input('email'),
            $request
        );

        // Always return success message for security (prevent email enumeration)
        return back()->with('status', $result['message']);
    }
}

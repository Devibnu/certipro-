<?php

namespace App\Services;

use App\Models\User;
use App\Models\AuditLog;
use App\Mail\PasswordResetByAdminMail;
use App\Mail\PasswordResetSelfServiceMail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

/**
 * ============================================================================
 * PasswordResetService
 * ============================================================================
 * Centralized service for all password reset operations
 * 
 * Compliance: ISO 17024, ISO 27001
 * Security: Rate limiting, audit logging, token-based reset
 * ============================================================================
 */
class PasswordResetService
{
    /**
     * Token expiry in minutes
     */
    const TOKEN_EXPIRY_MINUTES = 60;

    /**
     * Rate limit: max attempts per hour
     */
    const RATE_LIMIT_MAX_ATTEMPTS = 3;
    const RATE_LIMIT_DECAY_SECONDS = 3600; // 1 hour

    /**
     * Send password reset link initiated by admin
     * 
     * @param User $targetUser User to reset password for
     * @param User $admin Admin performing the action
     * @param Request $request Current request for IP/UA
     * @return array ['success' => bool, 'message' => string]
     */
    public function sendResetByAdmin(User $targetUser, User $admin, Request $request): array
    {
        // Validate admin role
        if (!$this->isAdminRole($admin)) {
            return [
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk melakukan aksi ini.'
            ];
        }

        // Prevent admin from resetting own password via this method
        if ($admin->id === $targetUser->id) {
            return [
                'success' => false,
                'message' => 'Gunakan fitur "Lupa Password" untuk mereset password Anda sendiri.'
            ];
        }

        // Rate limiting per target user
        $rateLimitKey = 'password-reset-admin:' . $targetUser->id;
        if (RateLimiter::tooManyAttempts($rateLimitKey, self::RATE_LIMIT_MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            $minutes = ceil($seconds / 60);
            return [
                'success' => false,
                'message' => "Terlalu banyak percobaan reset. Coba lagi dalam {$minutes} menit."
            ];
        }
        RateLimiter::hit($rateLimitKey, self::RATE_LIMIT_DECAY_SECONDS);

        try {
            // Generate token using Laravel Password Broker
            $token = Password::broker()->createToken($targetUser);
            
            // Build reset URL
            $resetLink = url(route('password.reset', [
                'token' => $token,
                'email' => $targetUser->email,
            ], false));

            // Queue email
            Mail::to($targetUser->email)->queue(new PasswordResetByAdminMail(
                nama: $targetUser->name,
                resetLink: $resetLink,
                expiresInMinutes: self::TOKEN_EXPIRY_MINUTES,
                adminName: $admin->name
            ));

            // Audit log - ISO 27001 compliance
            $this->logAudit(
                AuditLog::ACTION_PASSWORD_RESET_BY_ADMIN,
                $targetUser,
                "Admin '{$admin->name}' mengirim link reset password ke user '{$targetUser->name}' ({$targetUser->email})",
                [
                    'admin_id' => $admin->id,
                    'admin_name' => $admin->name,
                    'admin_email' => $admin->email,
                    'target_user_id' => $targetUser->id,
                    'target_user_email' => $targetUser->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            );

            // Audit log - email sent confirmation
            $this->logAudit(
                AuditLog::ACTION_PASSWORD_RESET_EMAIL_SENT,
                $targetUser,
                "Email reset password dikirim ke '{$targetUser->email}' oleh admin '{$admin->name}'",
                [
                    'admin_id' => $admin->id,
                    'target_user_id' => $targetUser->id,
                    'target_user_email' => $targetUser->email,
                    'expires_in_minutes' => self::TOKEN_EXPIRY_MINUTES,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            );

            return [
                'success' => true,
                'message' => 'Link reset password telah dikirim ke email ' . $this->maskEmail($targetUser->email)
            ];

        } catch (\Exception $e) {
            Log::error('Password reset by admin failed', [
                'admin_id' => $admin->id,
                'target_user_id' => $targetUser->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengirim email reset password. Silakan coba lagi nanti.'
            ];
        }
    }

    /**
     * Send password reset link for self-service (forgot password)
     * Always returns generic message to prevent email enumeration
     * 
     * @param string $email User email
     * @param Request $request Current request
     * @return array ['success' => bool, 'message' => string]
     */
    public function sendResetSelfService(string $email, Request $request): array
    {
        // Rate limiting per email
        $rateLimitKey = 'password-reset-self:' . Str::lower($email);
        if (RateLimiter::tooManyAttempts($rateLimitKey, self::RATE_LIMIT_MAX_ATTEMPTS)) {
            // Don't reveal rate limiting - use generic message
            return [
                'success' => true,
                'message' => 'Jika email terdaftar, link reset password telah dikirim.'
            ];
        }
        RateLimiter::hit($rateLimitKey, self::RATE_LIMIT_DECAY_SECONDS);

        try {
            $user = User::where('email', Str::lower($email))->first();
            
            if ($user) {
                // Generate token
                $token = Password::broker()->createToken($user);
                
                // Build reset URL
                $resetLink = url(route('password.reset', [
                    'token' => $token,
                    'email' => $user->email,
                ], false));

                // Queue email
                Mail::to($user->email)->queue(new PasswordResetSelfServiceMail(
                    nama: $user->name,
                    resetLink: $resetLink,
                    expiresInMinutes: self::TOKEN_EXPIRY_MINUTES
                ));

                // Audit log - password_reset_requested
                $this->logAudit(
                    AuditLog::ACTION_PASSWORD_RESET_REQUESTED,
                    $user,
                    "User '{$user->name}' meminta reset password",
                    [
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]
                );

                // Audit log - password_reset_email_sent
                $this->logAudit(
                    AuditLog::ACTION_PASSWORD_RESET_EMAIL_SENT,
                    $user,
                    "Email reset password dikirim ke '{$user->email}'",
                    [
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                        'expires_in_minutes' => self::TOKEN_EXPIRY_MINUTES,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]
                );
            }

            // Always return success to prevent email enumeration
            return [
                'success' => true,
                'message' => 'Jika email terdaftar, link reset password telah dikirim.'
            ];

        } catch (\Exception $e) {
            Log::error('Password reset self-service failed', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            // Generic message for security
            return [
                'success' => true,
                'message' => 'Jika email terdaftar, link reset password telah dikirim.'
            ];
        }
    }

    /**
     * Complete password reset
     * 
     * @param User $user User resetting password
     * @param string $newPassword New password
     * @param Request $request Current request
     * @return array ['success' => bool, 'message' => string]
     */
    public function completeReset(User $user, string $newPassword, Request $request): array
    {
        try {
            $user->forceFill([
                'password' => Hash::make($newPassword),
                'remember_token' => Str::random(60),
            ])->save();

            // Audit log
            $this->logAudit(
                AuditLog::ACTION_PASSWORD_RESET_BY_USER,
                $user,
                "User '{$user->name}' berhasil mereset password",
                [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            );

            return [
                'success' => true,
                'message' => 'Password berhasil diperbarui. Silakan login dengan password baru Anda.'
            ];

        } catch (\Exception $e) {
            Log::error('Password reset completion failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal memperbarui password. Silakan coba lagi.'
            ];
        }
    }

    /**
     * Check if user has admin role
     */
    private function isAdminRole(User $user): bool
    {
        // Use RBAC methods instead of legacy role column
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * Mask email for privacy
     */
    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return '***@***.***';
        }
        
        $name = $parts[0];
        $domain = $parts[1];
        
        $maskedName = substr($name, 0, 2) . str_repeat('*', max(strlen($name) - 2, 3));
        
        return $maskedName . '@' . $domain;
    }

    /**
     * Log audit trail
     */
    private function logAudit(string $action, User $user, string $description, array $metadata): void
    {
        try {
            AuditLog::log(
                $action,
                AuditLog::MODULE_USER,
                $description,
                $user,
                null,
                null,
                $metadata
            );
        } catch (\Exception $e) {
            Log::error('Audit log failed', [
                'action' => $action,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

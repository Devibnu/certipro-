<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Models\EmailSetting;
use App\Services\EmailSettingService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * EmailSettingController
 * 
 * Controller untuk mengelola Email Settings.
 * Hanya SUPER ADMIN yang dapat mengakses modul ini.
 * Semua perubahan dicatat ke Audit Log untuk kepatuhan BNSP/ISO 17024.
 * 
 * Routes:
 * - GET  /adminui/settings/email       - index()
 * - POST /adminui/settings/email       - update()
 * - POST /adminui/settings/email/test  - testEmail()
 * 
 * @package App\Http\Controllers\AdminUI
 */
class EmailSettingController extends Controller
{
    /**
     * @var EmailSettingService
     */
    protected EmailSettingService $emailService;

    /**
     * Allowed roles for accessing this controller.
     */
    protected array $allowedRoles = ['super admin', 'super_admin', 'superadmin'];

    /**
     * Constructor with dependency injection.
     * 
     * @param EmailSettingService $emailService
     */
    public function __construct(EmailSettingService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Display email settings form.
     * 
     * GET /adminui/settings/email
     * 
     * @return View
     */
    public function index(): View
    {
        // Role check handled by middleware(['role:super_admin'])
        // Get settings without cache for accurate form display
        $emailSetting = $this->emailService->getSettingsForEdit();
        
        // Convert to array format for view
        $settings = [
            'mail_driver' => $emailSetting->mail_driver ?? 'smtp',
            'mail_host' => $emailSetting->mail_host ?? '',
            'mail_port' => $emailSetting->mail_port ?? 587,
            'mail_encryption' => $emailSetting->mail_encryption ?? 'tls',
            'mail_username' => $emailSetting->mail_username ?? '',
            'mail_password' => $emailSetting->hasPassword() ? '********' : '',
            'mail_from_name' => $emailSetting->mail_from_name ?? 'CertiPro LSP',
            'mail_from_address' => $emailSetting->mail_from_address ?? 'noreply@certipro.id',
        ];

        // Field definitions for form
        $fields = [
            'mail_driver' => [
                'label' => 'Mail Driver',
                'type' => 'select',
                'options' => $this->emailService->getDriverOptions(),
            ],
            'mail_encryption' => [
                'label' => 'Encryption',
                'type' => 'select',
                'options' => $this->emailService->getEncryptionOptions(),
            ],
        ];

        $isConfigured = $this->emailService->isConfigured();

        return view('adminui.settings.email', compact('settings', 'fields', 'isConfigured', 'emailSetting'));
    }

    /**
     * Save/update email settings.
     * 
     * POST /adminui/settings/email
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        // Role check handled by middleware(['role:super_admin'])
        // Validate input
        $validated = $request->validate([
            'mail_driver' => 'required|in:' . implode(',', EmailSetting::DRIVERS),
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|integer|min:1|max:65535',
            'mail_encryption' => 'required|in:' . implode(',', EmailSetting::ENCRYPTIONS),
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name' => 'required|string|max:255',
        ], [
            'mail_driver.required' => 'Mail driver harus dipilih.',
            'mail_driver.in' => 'Mail driver tidak valid.',
            'mail_host.required' => 'SMTP host harus diisi.',
            'mail_port.required' => 'Port SMTP harus diisi.',
            'mail_port.integer' => 'Port harus berupa angka.',
            'mail_port.min' => 'Port minimal 1.',
            'mail_port.max' => 'Port maksimal 65535.',
            'mail_encryption.required' => 'Encryption harus dipilih.',
            'mail_encryption.in' => 'Encryption tidak valid.',
            'mail_from_address.required' => 'Alamat email pengirim harus diisi.',
            'mail_from_address.email' => 'Format email pengirim tidak valid.',
            'mail_from_name.required' => 'Nama pengirim harus diisi.',
        ]);

        // Save settings via service
        $result = $this->emailService->saveSettings($validated);

        if ($result['success']) {
            return redirect()
                ->route('adminui.settings.email')
                ->with('success', $result['message']);
        }

        return redirect()
            ->route('adminui.settings.email')
            ->with('error', $result['message'])
            ->withInput();
    }

    /**
     * Send test email.
     * 
     * POST /adminui/settings/email/test
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function sendTest(Request $request): RedirectResponse
    {
        // Role check handled by middleware(['role:super_admin'])\n        // Validate email address
        $validated = $request->validate([
            'test_email' => 'required|email|max:255',
        ], [
            'test_email.required' => 'Alamat email tujuan harus diisi.',
            'test_email.email' => 'Format email tidak valid.',
        ]);

        // Send test email via service
        $result = $this->emailService->sendTestEmail($validated['test_email']);

        if ($result['success']) {
            return redirect()
                ->route('adminui.settings.email')
                ->with('success', $result['message']);
        }

        return redirect()
            ->route('adminui.settings.email')
            ->with('error', $result['message']);
    }

    /**
     * Check if current user can access email settings.
     * Only SUPER ADMIN is allowed.
     * 
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @return void
     */
    protected function authorizeAccess(): void
    {
        $user = auth()->user();

        if (!$user) {
            abort(401, 'Anda harus login untuk mengakses halaman ini.');
        }

        // Use RBAC isSuperAdmin() method for proper role checking
        if (!$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak. Hanya Super Admin yang dapat mengelola pengaturan email.');
        }
    }
}

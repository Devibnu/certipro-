<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Models\LogoAdmin;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * BrandingController
 * 
 * Controller untuk mengelola Branding & Logo LSP.
 * Hanya SUPER ADMIN yang dapat mengakses modul ini.
 * Semua perubahan dicatat ke Audit Log untuk kepatuhan BNSP/ISO 17024.
 * 
 * Routes:
 * - GET  /adminui/settings/branding  - index()
 * - POST /adminui/settings/branding  - update()
 * 
 * @package App\Http\Controllers\AdminUI
 */
class BrandingController extends Controller
{
    /**
     * Allowed roles for accessing this controller.
     */
    protected array $allowedRoles = ['super admin', 'super_admin', 'superadmin'];

    /**
     * Display branding & logo settings.
     * 
     * GET /adminui/settings/branding
     * 
     * @return View
     */
    public function index(): View
    {
        $this->authorizeAccess();

        // Get current active logo
        $currentLogo = LogoAdmin::active()->first();

        // Logo info
        $logoInfo = [
            'exists' => $currentLogo ? true : false,
            'path' => $currentLogo && $currentLogo->gambar ? asset('storage/' . $currentLogo->gambar) : null,
            'nama_perusahaan' => $currentLogo && $currentLogo->nama_perusahaan && trim($currentLogo->nama_perusahaan) !== '' ? $currentLogo->nama_perusahaan : null,
            'tagline' => $currentLogo->tagline ?? null,
            'updated_at' => $currentLogo ? $currentLogo->updated_at->format('d M Y H:i') : null,
            'updated_by' => $currentLogo ? $currentLogo->updated_by ?? 'System' : null,
        ];

        return view('adminui.settings.branding', compact('logoInfo', 'currentLogo'));
    }

    /**
     * Update/upload logo.
     * 
     * POST /adminui/settings/branding
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $this->authorizeAccess();

        try {
            // Check if logo already exists
            $existingLogo = LogoAdmin::active()->first();
            
            // Validation - logo required only if no existing logo
            $logoRule = $existingLogo ? 'nullable' : 'required';
            
            $validated = $request->validate([
                'logo' => $logoRule . '|image|mimes:png,jpg,jpeg,svg|max:2048',
                'nama_perusahaan' => 'nullable|string|max:255',
                'tagline' => 'nullable|string|max:255',
            ], [
                'logo.required' => 'Logo wajib diupload',
                'logo.image' => 'File harus berupa gambar',
                'logo.mimes' => 'Format logo harus: PNG, JPG, JPEG, atau SVG',
                'logo.max' => 'Ukuran logo maksimal 2MB',
            ]);

            // Get old logo for audit log
            $oldLogo = $existingLogo;
            $oldValue = $oldLogo ? [
                'path' => $oldLogo->gambar,
                'nama_perusahaan' => $oldLogo->nama_perusahaan,
                'tagline' => $oldLogo->tagline,
            ] : null;

            // If new logo uploaded
            if ($request->hasFile('logo')) {
                // Deactivate all existing logos
                LogoAdmin::query()->update(['status' => false]);

                // Upload new logo
                $file = $request->file('logo');
                $filename = 'logo-' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('logo-admin', $filename, 'public');

                // Create new logo record
                $newLogo = LogoAdmin::create([
                    'gambar' => $path,
                    'nama_perusahaan' => $request->filled('nama_perusahaan') ? $validated['nama_perusahaan'] : null,
                    'tagline' => $request->filled('tagline') ? $validated['tagline'] : null,
                    'status' => true,
                ]);

                // Delete old logo file if exists
                if ($oldLogo && $oldLogo->gambar) {
                    Storage::disk('public')->delete($oldLogo->gambar);
                }
            } else {
                // Just update nama_perusahaan and tagline without new logo
                if ($oldLogo) {
                    $oldLogo->update([
                        'nama_perusahaan' => $request->filled('nama_perusahaan') ? $validated['nama_perusahaan'] : null,
                        'tagline' => $request->filled('tagline') ? $validated['tagline'] : null,
                    ]);
                    $newLogo = $oldLogo;
                    $path = $oldLogo->gambar;
                } else {
                    return redirect()
                        ->back()
                        ->with('error', 'Logo wajib diupload karena belum ada logo tersimpan.');
                }
            }

            // Audit Log
            $this->createAuditLog(
                'branding_logo_updated',
                'Logo sistem berhasil diupdate',
                $oldValue,
                [
                    'path' => $path ?? $newLogo->gambar,
                    'nama_perusahaan' => $newLogo->nama_perusahaan,
                    'tagline' => $newLogo->tagline,
                ]
            );

            Log::info('Logo branding updated', [
                'user_id' => auth()->id(),
                'old_logo' => $oldLogo?->id,
                'new_logo' => $newLogo->id,
            ]);

            // Clear cache untuk logo
            cache()->forget('system_logo');
            cache()->forget('system_name');
            cache()->forget('system_tagline');

            return redirect()
                ->route('adminui.settings.branding')
                ->with('success', 'Logo berhasil diupdate dan akan diterapkan ke seluruh sistem.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            Log::error('Failed to update branding logo', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Gagal mengupdate logo: ' . $e->getMessage());
        }
    }

    /**
     * Delete logo (set to inactive).
     * 
     * DELETE /adminui/settings/branding
     * 
     * @return RedirectResponse
     */
    public function destroy(): RedirectResponse
    {
        $this->authorizeAccess();

        try {
            $currentLogo = LogoAdmin::active()->first();

            if (!$currentLogo) {
                return redirect()
                    ->back()
                    ->with('info', 'Tidak ada logo aktif untuk dihapus.');
            }

            // Deactivate logo
            $currentLogo->update(['status' => false]);

            // Delete file
            if ($currentLogo->gambar) {
                Storage::disk('public')->delete($currentLogo->gambar);
            }

            // Audit Log
            $this->createAuditLog(
                'branding_logo_deleted',
                'Logo sistem dihapus',
                [
                    'path' => $currentLogo->gambar,
                    'nama_perusahaan' => $currentLogo->nama_perusahaan,
                ],
                null
            );

            return redirect()
                ->route('adminui.settings.branding')
                ->with('success', 'Logo berhasil dihapus.');

        } catch (\Exception $e) {
            Log::error('Failed to delete branding logo', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus logo: ' . $e->getMessage());
        }
    }

    /**
     * Check if user has access to this controller.
     * 
     * @return void
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function authorizeAccess(): void
    {
        $user = auth()->user();
        
        if (!$user) {
            abort(403, 'Unauthorized access');
        }

        $userRole = strtolower(str_replace(' ', '_', $user->role ?? ''));
        
        $hasAccess = collect($this->allowedRoles)->contains(function ($role) use ($userRole) {
            return strtolower(str_replace(' ', '_', $role)) === $userRole;
        });

        if (!$hasAccess) {
            abort(403, 'Akses ditolak. Hanya Super Admin yang dapat mengelola branding.');
        }
    }

    /**
     * Create audit log entry.
     * 
     * @param string $action
     * @param string $description
     * @param array|null $oldValue
     * @param array|null $newValue
     * @return void
     */
    protected function createAuditLog(
        string $action,
        string $description,
        ?array $oldValue = null,
        ?array $newValue = null
    ): void {
        try {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'description' => $description,
                'model_type' => LogoAdmin::class,
                'model_id' => LogoAdmin::active()->first()?->id,
                'old_values' => $oldValue ? json_encode($oldValue) : null,
                'new_values' => $newValue ? json_encode($newValue) : null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create audit log for branding', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}

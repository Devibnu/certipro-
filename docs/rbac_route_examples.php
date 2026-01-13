<?php

/**
 * ============================================================================
 * RBAC Route Protection Examples - ISO 17024 & BNSP Compliant
 * ============================================================================
 * 
 * This file demonstrates proper middleware application for all critical routes.
 * Copy and adapt these patterns to your routes/web.php or routes/admin.php
 * 
 * PRINCIPLES:
 * 1. Every critical route MUST have permission middleware
 * 2. UI visibility follows backend permission, not vice versa
 * 3. Use specific permissions (asesmen.submit) not generic (asesmen)
 * 
 * @see ISO 17024:2012 Clause 5.2 (Confidentiality)
 * @see BNSP Pedoman 201 (Keamanan Data)
 */

use Illuminate\Support\Facades\Route;

// =============================================================================
// PATTERN 1: Single Permission
// =============================================================================
// Use when only ONE specific permission is required

Route::get('/adminui/pendaftaran', [PendaftaranController::class, 'index'])
    ->middleware('permission:pendaftaran.view')
    ->name('adminui.pendaftaran.index');

Route::post('/adminui/pendaftaran/{id}/verify', [PendaftaranController::class, 'verify'])
    ->middleware('permission:pendaftaran.verify')
    ->name('adminui.pendaftaran.verify');


// =============================================================================
// PATTERN 2: Multiple Permissions (OR Logic)
// =============================================================================
// Use when ANY of the permissions should grant access

Route::get('/adminui/asesmen/{id}', [AsesmenController::class, 'show'])
    ->middleware('permission:asesmen.view|asesmen.submit')
    ->name('adminui.asesmen.show');


// =============================================================================
// PATTERN 3: Role-Based (When Permission is Too Granular)
// =============================================================================
// Use for entire module restriction to specific roles

Route::prefix('adminui/keputusan')
    ->name('adminui.keputusan.')
    ->middleware(['auth', 'role:komite_teknis'])
    ->group(function () {
        Route::get('/', [KeputusanController::class, 'index'])->name('index');
        Route::get('/{id}', [KeputusanController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [KeputusanController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [KeputusanController::class, 'reject'])->name('reject');
    });


// =============================================================================
// PATTERN 4: Grouped Routes with Same Permission
// =============================================================================
// Use when multiple routes share the same permission requirement

Route::prefix('adminui/users')
    ->name('adminui.users.')
    ->middleware(['auth', 'permission:users.manage'])
    ->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });


// =============================================================================
// PATTERN 5: Mixed Permissions in Group
// =============================================================================
// Use when routes in same module need different permissions

Route::prefix('adminui/asesmen')
    ->name('adminui.asesmen.')
    ->middleware('auth')
    ->group(function () {
        // View - accessible by asesor and admin (read-only)
        Route::get('/', [AsesmenController::class, 'index'])
            ->middleware('permission:asesmen.view')
            ->name('index');
        
        // Submit - only asesor can submit results
        Route::post('/{id}/simpan', [AsesmenController::class, 'simpan'])
            ->middleware('permission:asesmen.submit')
            ->name('simpan');
        
        // Lock - only asesor can finalize
        Route::post('/{id}/lock', [AsesmenController::class, 'lock'])
            ->middleware('permission:asesmen.lock')
            ->name('lock');
    });


// =============================================================================
// PATTERN 6: Resource Routes with Permission
// =============================================================================
// Use with Laravel's resource controller

Route::resource('adminui/skema-sertifikasi', SkemaSertifikasiController::class)
    ->middleware('permission:skema.manage')
    ->names([
        'index' => 'adminui.skema.index',
        'create' => 'adminui.skema.create',
        'store' => 'adminui.skema.store',
        'show' => 'adminui.skema.show',
        'edit' => 'adminui.skema.edit',
        'update' => 'adminui.skema.update',
        'destroy' => 'adminui.skema.destroy',
    ]);


// =============================================================================
// PATTERN 7: Sertifikat Module (Komite Teknis Only - ISO 17024 Clause 9.5)
// =============================================================================
// Critical: Only certification decision makers can issue certificates

Route::prefix('adminui/sertifikat')
    ->name('adminui.sertifikat.')
    ->middleware(['auth', 'role:komite_teknis'])
    ->group(function () {
        Route::get('/', [SertifikatController::class, 'index'])
            ->middleware('permission:sertifikat.view')
            ->name('index');
        
        Route::post('/{pendaftaran}/terbit', [SertifikatController::class, 'terbitkan'])
            ->middleware('permission:sertifikat.generate')
            ->name('terbit');
        
        Route::get('/{id}/download', [SertifikatController::class, 'download'])
            ->middleware('permission:sertifikat.download')
            ->name('download');
        
        // Revoke - Super Admin only (most critical action)
        Route::post('/{id}/revoke', [SertifikatController::class, 'revoke'])
            ->middleware('permission:sertifikat.revoke')
            ->name('revoke');
    });


// =============================================================================
// PATTERN 8: Audit Log (Admin Only - ISO 17024 Clause 10)
// =============================================================================
// Audit logs must be accessible but not modifiable

Route::prefix('adminui/audit-log')
    ->name('adminui.audit-log.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {
        Route::get('/', [AuditLogController::class, 'index'])
            ->middleware('permission:audit.view')
            ->name('index');
        
        Route::get('/{id}', [AuditLogController::class, 'show'])
            ->middleware('permission:audit.view')
            ->name('show');
        
        // Export - Super Admin only for BNSP audit
        Route::get('/export', [AuditLogController::class, 'export'])
            ->middleware('permission:audit.export')
            ->name('export');
        
        // Statistics - Super Admin only
        Route::get('/statistics', [AuditLogController::class, 'statistics'])
            ->middleware('permission:audit.statistics')
            ->name('statistics');
    });


// =============================================================================
// PATTERN 9: Settings (Super Admin Only)
// =============================================================================
// System settings require highest privilege

Route::prefix('adminui/settings')
    ->name('adminui.settings.')
    ->middleware(['auth', 'permission:settings.manage'])
    ->group(function () {
        Route::get('/email', [EmailSettingController::class, 'index'])->name('email');
        Route::post('/email', [EmailSettingController::class, 'store'])->name('email.store');
        Route::post('/email/test', [EmailSettingController::class, 'sendTest'])->name('email.test');
        
        Route::get('/branding', [BrandingController::class, 'index'])->name('branding');
        Route::post('/branding', [BrandingController::class, 'update'])->name('branding.update');
    });


// =============================================================================
// ANTI-PATTERN: DO NOT DO THIS
// =============================================================================

/*
 * ❌ WRONG: No middleware protection
 * 
 * Route::get('/adminui/sertifikat/generate/{id}', [SertifikatController::class, 'generate']);
 * 
 * This allows anyone with the URL to generate certificates!
 */

/*
 * ❌ WRONG: UI-only protection
 * 
 * In Blade:
 * @if($user->role == 'komite_teknis')
 *     <a href="/adminui/sertifikat/generate/{{ $id }}">Generate</a>
 * @endif
 * 
 * This only hides the button, the route is still accessible!
 */

/*
 * ❌ WRONG: Generic permission
 * 
 * Route::post('/adminui/sertifikat/generate', [...])
 *     ->middleware('permission:sertifikat');
 * 
 * Too generic! Use specific action: permission:sertifikat.generate
 */


// =============================================================================
// BLADE VIEW INTEGRATION
// =============================================================================

/*
 * ✅ CORRECT: Blade visibility follows backend permission
 * 
 * @if(auth()->user()->hasPermission('sertifikat.generate'))
 *     <a href="{{ route('adminui.sertifikat.terbit', $pendaftaran->id) }}" 
 *        class="btn btn-success">
 *         Terbitkan Sertifikat
 *     </a>
 * @endif
 * 
 * Even if someone removes the @if check, the middleware will still block!
 */


// =============================================================================
// CONTROLLER DOUBLE-CHECK (Belt and Suspenders)
// =============================================================================

/*
 * For extra security, you can also check in controller:
 * 
 * public function generate(Request $request, int $id)
 * {
 *     // Extra validation (middleware already handles this, but defense in depth)
 *     if (!auth()->user()->hasPermission('sertifikat.generate')) {
 *         abort(403, 'Unauthorized action.');
 *     }
 *     
 *     // ... proceed with certificate generation
 * }
 */

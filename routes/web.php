<?php

// User Management (AdminUI)
use App\Http\Controllers\AdminUI\UserController;
use App\Http\Controllers\HomeController;

Route::middleware(['auth'])->prefix('adminui')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('adminui.users.index')->middleware('check.permission:Users');
    Route::get('/users/create', [UserController::class, 'create'])->name('adminui.users.create')->middleware('check.permission:Users');
    Route::post('/users', [UserController::class, 'store'])->name('adminui.users.store')->middleware('check.permission:Users');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('adminui.users.edit')->middleware('check.permission:Users');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('adminui.users.update')->middleware('check.permission:Users');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('adminui.users.destroy')->middleware('check.permission:Users');
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('adminui.users.reset-password')->middleware('check.permission:Users');
});

use App\Http\Controllers\AdminUIController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\DynamicAboutController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

// Import controllers baru  
use App\Http\Controllers\Admin\AboutController as AdminAboutController;
use App\Http\Controllers\CKEditorController;
use App\Http\Controllers\AboutHeroController;
use App\Http\Controllers\AboutContentController;
use App\Models\AboutHeroSection;
use App\Http\Controllers\AboutTestimonialController;
use App\Http\Controllers\AboutPageController;
use App\Http\Controllers\AboutAdminController;
use App\Http\Controllers\ServicesPageController;
use App\Http\Controllers\AdminUI\ServicesAdminController;
use App\Http\Controllers\Admin\KontakController;

// Global login route - langsung ke halaman login admin
Route::get('/login', [AdminUIController::class, 'login'])->name('login')->middleware('guest');
Route::post('/authenticate', [AdminUIController::class, 'authenticate'])->name('authenticate');

// ============================================
// CMS LANDING PAGE PUBLIC ROUTES (NEW MODULE)
// URL tanpa prefix /page, langsung /{slug}
// ============================================

// Homepage - slug 'home'
Route::get('/', [App\Http\Controllers\LandingPageController::class, 'home'])->name('home');

// Halaman CMS dinamis lainnya
Route::get('/tentang', [App\Http\Controllers\LandingPageController::class, 'show'])->defaults('slug', 'tentang')->name('landing.tentang');
Route::get('/skema', [App\Http\Controllers\LandingPageController::class, 'show'])->defaults('slug', 'skema')->name('landing.skema');
Route::get('/alur', [App\Http\Controllers\LandingPageController::class, 'show'])->defaults('slug', 'alur')->name('landing.alur');
Route::get('/persyaratan', [App\Http\Controllers\LandingPageController::class, 'show'])->defaults('slug', 'persyaratan')->name('landing.persyaratan');
Route::get('/kontak', [App\Http\Controllers\LandingPageController::class, 'show'])->defaults('slug', 'kontak')->name('landing.kontak');

// ============================================
// PRA-PENDAFTARAN PUBLIC ROUTES
// Form pendaftaran untuk umum dan kampus
// ============================================
Route::get('/daftar', [App\Http\Controllers\PraPendaftaranController::class, 'create'])->name('daftar');
Route::post('/daftar', [App\Http\Controllers\PraPendaftaranController::class, 'store'])->name('daftar.store');
Route::get('/pendaftaran/sukses', [App\Http\Controllers\PraPendaftaranController::class, 'sukses'])->name('pendaftaran.sukses');

// ============================================
// SERTIFIKAT VERIFICATION (PUBLIC)
// Verifikasi keaslian sertifikat tanpa login
// ============================================
Route::get('/sertifikat/verifikasi', [App\Http\Controllers\SertifikatVerifikasiController::class, 'search'])->name('sertifikat.search.public');
Route::get('/sertifikat/verifikasi/{nomor_sertifikat}', [App\Http\Controllers\SertifikatVerifikasiController::class, 'verifikasi'])->name('sertifikat.verifikasi.public');
Route::get('/sertifikat/verify/{uuid}', [App\Http\Controllers\SertifikatVerifikasiController::class, 'verifyByUuid'])->name('sertifikat.verify.uuid');

// Alias routes for easier access
Route::get('/verifikasi', [App\Http\Controllers\SertifikatVerifikasiController::class, 'search'])->name('verifikasi.index');
Route::get('/verifikasi/{nomor_sertifikat}', [App\Http\Controllers\SertifikatVerifikasiController::class, 'verifikasi'])->name('verifikasi.show');

// ============================================
// STATUS PENDAFTARAN (PUBLIC)
// Transparansi status untuk peserta sesuai BNSP
// ============================================

// Status Pra-Pendaftaran (tahap awal)
Route::get('/status-pendaftaran', [App\Http\Controllers\StatusPraPendaftaranController::class, 'index'])->name('status-pendaftaran');
Route::get('/status-pra-pendaftaran', [App\Http\Controllers\StatusPraPendaftaranController::class, 'index'])->name('status-pra-pendaftaran.index');
Route::get('/status-pra-pendaftaran/cari', [App\Http\Controllers\StatusPraPendaftaranController::class, 'search'])->name('status-pra-pendaftaran.search');
Route::get('/api/status-pra-pendaftaran', [App\Http\Controllers\StatusPraPendaftaranController::class, 'apiCheck'])->name('status-pra-pendaftaran.api');

// Status Pendaftaran Sertifikasi (tahap lanjutan)
Route::get('/status-sertifikasi', [App\Http\Controllers\StatusPendaftaranController::class, 'index'])->name('status-pendaftaran.index');
Route::post('/status-sertifikasi', [App\Http\Controllers\StatusPendaftaranController::class, 'search'])->name('status-pendaftaran.search');
Route::get('/api/status-sertifikasi', [App\Http\Controllers\StatusPendaftaranController::class, 'apiCheck'])->name('status-pendaftaran.api');

// ============================================
// PENDAFTARAN SERTIFIKASI (ASESI)
// Routes untuk user login (asesi)
// ============================================
Route::middleware(['auth'])->group(function () {
    Route::get('/pendaftaran-sertifikasi', [App\Http\Controllers\PendaftaranSertifikasiController::class, 'index'])->name('pendaftaran-sertifikasi.index');
    Route::post('/pendaftaran-sertifikasi', [App\Http\Controllers\PendaftaranSertifikasiController::class, 'store'])->name('pendaftaran-sertifikasi.store');
});

// ============================================
// LEGACY ROUTES (backward compatibility)
// Routes lama tetap ada untuk kompatibilitas
// ============================================

// Dynamic About Page Routes (Legacy)
Route::get('/about', [AboutPageController::class, 'index'])->name('about');
Route::get('/about-test', [AboutPageController::class, 'index'])->name('about.test');
// Keep old routes for backward compatibility
Route::get('/admin/about', [DynamicAboutController::class, 'edit'])->name('admin.about.edit');
Route::post('/admin/about/autosave', [DynamicAboutController::class, 'update'])->name('admin.about.autosave');
Route::post('/admin/about/upload-image', [DynamicAboutController::class, 'uploadImage'])->name('admin.about.upload');

Route::get('/services', [ServicesPageController::class, 'index'])->name('services');

// Dynamic Contact Page Routes (Legacy)
Route::get('/contact', [KontakController::class, 'frontend'])->name('contact');
Route::post('/contact/submit', [KontakController::class, 'submitContactForm'])->name('contact.submit');

// Blog Routes
Route::get('/blog', [App\Http\Controllers\BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [App\Http\Controllers\BlogController::class, 'detail'])->name('blog.detail');
Route::post('/blog/{slug}/komentar', [App\Http\Controllers\BlogController::class, 'storeKomentar'])->name('blog.komentar.store');

// Projects Frontend Routes
Route::get('/projects', [App\Http\Controllers\ProjectFrontendController::class, 'index'])->name('projects.index');
Route::get('/projects/{slug}', [App\Http\Controllers\ProjectFrontendController::class, 'show'])->name('projects.show');

// Redirect /project to /projects (with data from controller)
Route::get('/project', [App\Http\Controllers\ProjectFrontendController::class, 'index'])->name('project');

// Request Quote Public Submit
Route::post('/request-quote/send', [App\Http\Controllers\Admin\RequestQuoteController::class, 'submitQuote'])->name('request-quote.send');

// Laravel Breeze Default Routes
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin UI Dashboard Routes with prefix
Route::prefix('adminui')->name('adminui.')->group(function () {
    
    // Route utama adminui - redirect ke login untuk single entry point
    Route::get('/', function() {
        if (Auth::check()) {
            return redirect()->route('adminui.dashboard');
        }
        return redirect()->route('login');
    })->name('index');
    
    // Routes untuk authenticated users
    Route::group(['middleware' => 'auth'], function () {
        Route::get('dashboard', [AdminUIController::class, 'dashboard'])->name('dashboard');
        
    // Profile Routes
    Route::get('profile', [App\Http\Controllers\AdminUI\ProfileController::class, 'index'])->name('profile')->middleware('check.permission:Profile');
    Route::put('profile', [App\Http\Controllers\AdminUI\ProfileController::class, 'update'])->name('profile.update')->middleware('check.permission:Profile');
    Route::put('profile/password', [App\Http\Controllers\AdminUI\ProfileController::class, 'updatePassword'])->name('profile.password')->middleware('check.permission:Profile');
        
    Route::get('virtual-reality', [AdminUIController::class, 'virtualReality'])->name('virtual-reality')->middleware('check.permission:Virtual Reality');
    Route::get('rtl', [AdminUIController::class, 'rtl'])->name('rtl')->middleware('check.permission:RTL');
        
        // About Management Dashboard - NEW 3-TAB SYSTEM
    Route::get('about', [AboutAdminController::class, 'index'])->name('about')->middleware('check.permission:About Page');
        
        // Debug route to catch wrong form submissions and handle them directly
        Route::post('about', function(Request $request) {
            Log::error('Form submitted to wrong endpoint /adminui/about - processing directly', [
                'data' => $request->all(),
                'debug_form' => $request->get('debug_form'),
                'has_tagline' => $request->has('tagline'),
                'has_projects' => $request->has('projects_completed'),
                'has_title' => $request->has('title'),
                'has_left_paragraph' => $request->has('left_paragraph'),
                'url' => $request->url(),
                'method' => $request->method()
            ]);
            
            $aboutController = new App\Http\Controllers\AboutAdminController();
            
            // Check debug_form field first, then fallback to content detection
            $formType = $request->get('debug_form');
            
            if ($formType === 'hero' || $request->has(['tagline', 'projects_completed'])) {
                Log::info('Processing hero form submission directly - HERO FORM');
                try {
                    $result = $aboutController->updateHero($request);
                    Log::info('Hero form processed successfully');
                    return $result;
                } catch (\Exception $e) {
                    Log::error('Hero form processing failed: ' . $e->getMessage());
                    return redirect()->route('adminui.about.index')->with('error', 'Hero form failed: ' . $e->getMessage());
                }
            } elseif ($formType === 'content' || $request->has(['title', 'left_paragraph'])) {
                Log::info('Processing content form submission directly - CONTENT FORM');
                try {
                    $result = $aboutController->storeContent($request);
                    Log::info('Content form processed successfully');
                    return $result;
                } catch (\Exception $e) {
                    Log::error('Content form processing failed: ' . $e->getMessage());
                    return redirect()->route('adminui.about.index')->with('error', 'Content form failed: ' . $e->getMessage());
                }
            } elseif ($formType === 'testimonial' || $request->has(['client_name', 'client_position'])) {
                Log::info('Processing testimonial form submission directly - TESTIMONIAL FORM');
                try {
                    $result = $aboutController->storeTestimonial($request);
                    Log::info('Testimonial form processed successfully');
                    return $result;
                } catch (\Exception $e) {
                    Log::error('Testimonial form processing failed: ' . $e->getMessage());
                    return redirect()->route('adminui.about.index')->with('error', 'Testimonial form failed: ' . $e->getMessage());
                }
            }
            
            Log::error('Unable to determine form type for debug route');
            return redirect()->route('adminui.about.index')->with('error', 'Form submission error - unknown form type');
        });
        
        Route::post('about/hero', [AboutAdminController::class, 'updateHero'])->name('about.hero');
        Route::post('about/heropage', [AboutAdminController::class, 'updateHeroPage'])->name('about.heropage');
        
        // About Content Routes
        Route::get('about/content', [AboutAdminController::class, 'indexContent'])->name('about.content.index');
        Route::get('about/content/create', [AboutAdminController::class, 'createContent'])->name('about.content.create');
        Route::post('about/content', [AboutAdminController::class, 'storeContent'])->name('about.content');
        Route::get('about/content/{content}/edit', [AboutAdminController::class, 'editContent'])->name('about.content.edit');
        Route::put('about/content/{content}', [AboutAdminController::class, 'updateContent'])->name('about.content.update');
        Route::delete('about/content/{content}', [AboutAdminController::class, 'destroyContent'])->name('about.content.delete');
        
        // About Testimonial Routes
        Route::get('about/testimonial', [AboutAdminController::class, 'indexTestimonial'])->name('about.testimonial.index');
        Route::get('about/testimonial/create', [AboutAdminController::class, 'createTestimonial'])->name('about.testimonial.create');
        Route::post('about/testimonial', [AboutAdminController::class, 'storeTestimonial'])->name('about.testimonial');
        Route::get('about/testimonial/{testimonial}/edit', [AboutAdminController::class, 'editTestimonial'])->name('about.testimonial.edit');
        Route::put('about/testimonial/{testimonial}', [AboutAdminController::class, 'updateTestimonial'])->name('about.testimonial.update');
        Route::delete('about/testimonial/{testimonial}', [AboutAdminController::class, 'destroyTestimonial'])->name('about.testimonial.delete');
        
        // About Hero Page Routes  
        Route::get('about/headers', [AboutAdminController::class, 'indexHeaders'])->name('about.headers.index');
        Route::get('about/headers/create', [AboutAdminController::class, 'createHeader'])->name('about.headers.create');
        Route::post('about/headers', [AboutAdminController::class, 'storeHeader'])->name('about.headers.store');
        Route::get('about/headers/{header}/edit', [AboutAdminController::class, 'editHeader'])->name('about.headers.edit');
        Route::put('about/headers/{header}', [AboutAdminController::class, 'updateHeader'])->name('about.headers.update');
        Route::delete('about/headers/{header}', [AboutAdminController::class, 'destroyHeader'])->name('about.headers.delete');
        
        // Services Admin Routes
    Route::get('services', [ServicesAdminController::class, 'index'])->name('services.index')->middleware('check.permission:Services');
    Route::get('services/header-layanan', [ServicesAdminController::class, 'indexHeaderLayanan'])->name('services.header-layanan.index')->middleware('check.permission:Services');
    Route::get('services/header-fitur-utama', [ServicesAdminController::class, 'indexHeaderFiturUtama'])->name('services.header-fitur-utama.index')->middleware('check.permission:Services');
    Route::get('services/header-fitur', [ServicesAdminController::class, 'indexHeaderFitur'])->name('services.header-fitur.index')->middleware('check.permission:Services');
    Route::get('services/fitur-utama', [ServicesAdminController::class, 'indexFiturUtama'])->name('services.fitur-utama.index')->middleware('check.permission:Services');
    Route::get('services/fitur-utama/create', [ServicesAdminController::class, 'createFiturUtama'])->name('services.fitur-utama.create')->middleware('check.permission:Services');
    Route::get('services/fitur-utama/{id}/edit', [ServicesAdminController::class, 'editFiturUtamaPage'])->name('services.fitur-utama.edit')->middleware('check.permission:Services');
    Route::get('services/daftar-layanan', [ServicesAdminController::class, 'indexDaftarLayanan'])->name('services.daftar-layanan.index')->middleware('check.permission:Services');
        
    Route::post('services/header-layanan', [ServicesAdminController::class, 'storeHeaderLayanan'])->name('services.header-layanan')->middleware('check.permission:Services');
    Route::post('services/header-fitur-utama', [ServicesAdminController::class, 'storeHeaderFiturUtama'])->name('services.header-fitur-utama')->middleware('check.permission:Services');
    Route::post('services/header-daftar-layanan', [ServicesAdminController::class, 'storeHeaderDaftarLayanan'])->name('services.header-daftar-layanan')->middleware('check.permission:Services');
    Route::post('services/header-fitur', [ServicesAdminController::class, 'storeHeaderFitur'])->name('services.header-fitur')->middleware('check.permission:Services');
    Route::post('services/fitur-utama', [ServicesAdminController::class, 'storeFiturUtama'])->name('services.fitur-utama')->middleware('check.permission:Services');
    Route::post('services/daftar-layanan', [ServicesAdminController::class, 'storeDaftarLayanan'])->name('services.daftar-layanan')->middleware('check.permission:Services');
    Route::get('services/daftar-layanan/{id}/edit', [ServicesAdminController::class, 'editDaftarLayanan'])->name('services.daftar-layanan.edit')->middleware('check.permission:Services');
    Route::put('services/fitur-utama/{id}', [ServicesAdminController::class, 'updateFiturUtama'])->name('services.fitur-utama.update')->middleware('check.permission:Services');
    Route::put('services/daftar-layanan/{id}', [ServicesAdminController::class, 'updateDaftarLayanan'])->name('services.daftar-layanan.update')->middleware('check.permission:Services');
    Route::delete('services/fitur-utama/{id}', [ServicesAdminController::class, 'destroyFiturUtama'])->name('services.fitur-utama.delete')->middleware('check.permission:Services');
    Route::delete('services/daftar-layanan/{id}', [ServicesAdminController::class, 'destroyDaftarLayanan'])->name('services.daftar-layanan.delete')->middleware('check.permission:Services');
        
    // Contact Admin Routes
    Route::get('contact', [KontakController::class, 'index'])->name('contact')->middleware('check.permission:Contact Page');
    Route::post('contact/update', [KontakController::class, 'update'])->name('contact.update')->middleware('check.permission:Contact Page');
    
    // Contact Messages Routes
    Route::get('contact-messages', [App\Http\Controllers\Admin\ContactMessageController::class, 'index'])->name('contact-messages.index')->middleware('check.permission:Dashboard');
    Route::get('contact-messages/{id}', [App\Http\Controllers\Admin\ContactMessageController::class, 'show'])->name('contact-messages.show')->middleware('check.permission:Dashboard');
    Route::post('contact-messages/{id}/update-status', [App\Http\Controllers\Admin\ContactMessageController::class, 'updateStatus'])->name('contact-messages.update-status')->middleware('check.permission:Dashboard');
    Route::delete('contact-messages/{id}', [App\Http\Controllers\Admin\ContactMessageController::class, 'destroy'])->name('contact-messages.destroy')->middleware('check.permission:Dashboard');
    Route::post('contact-messages/{id}/reply-whatsapp', [App\Http\Controllers\Admin\ContactMessageController::class, 'replyWhatsapp'])->name('contact-messages.reply-whatsapp')->middleware('check.permission:Dashboard');
    
    // Footer Settings Routes
    Route::get('footer', [App\Http\Controllers\Admin\FooterSettingController::class, 'index'])->name('footer.index')->middleware('check.permission:Dashboard');
    Route::post('footer/update', [App\Http\Controllers\Admin\FooterSettingController::class, 'update'])->name('footer.update')->middleware('check.permission:Dashboard');
    
    // Home Hero Routes
    Route::resource('home-hero', App\Http\Controllers\AdminUI\HomeHeroController::class)->middleware('check.permission:Dashboard');
    
    // Header Info Routes
    Route::resource('header-info', App\Http\Controllers\HeaderInfoController::class)->middleware('check.permission:Dashboard');
    
    // Logo Website Routes
    Route::resource('logo-website', App\Http\Controllers\LogoWebsiteController::class)->middleware('check.permission:Dashboard');
    
    // Logo Admin Routes
    Route::resource('logo-admin', App\Http\Controllers\LogoAdminController::class)->middleware('check.permission:Dashboard');
    
    // Favicon Routes
    Route::resource('favicon', App\Http\Controllers\FaviconController::class)->middleware('check.permission:Dashboard');
    
    // CMS Landing Page Routes - NEW MODULE
    Route::resource('halaman', App\Http\Controllers\AdminUI\HalamanController::class)->middleware('check.permission:Dashboard');
    Route::resource('bagian-halaman', App\Http\Controllers\AdminUI\BagianHalamanController::class)->middleware('check.permission:Dashboard');
    
    // Pra-Pendaftaran Admin Routes
    Route::get('pra-pendaftaran', [App\Http\Controllers\AdminUI\PraPendaftaranAdminController::class, 'index'])->name('pra-pendaftaran.index')->middleware('check.permission:Dashboard');
    Route::get('pra-pendaftaran/{id}', [App\Http\Controllers\AdminUI\PraPendaftaranAdminController::class, 'show'])->name('pra-pendaftaran.show')->middleware('check.permission:Dashboard');
    Route::patch('pra-pendaftaran/{id}/status', [App\Http\Controllers\AdminUI\PraPendaftaranAdminController::class, 'updateStatus'])->name('pra-pendaftaran.update-status')->middleware('check.permission:Dashboard');
    Route::post('pra-pendaftaran/{id}/buat-pendaftaran', [App\Http\Controllers\AdminUI\PraPendaftaranAdminController::class, 'buatPendaftaranSertifikasi'])->name('pra-pendaftaran.buat-pendaftaran')->middleware('check.permission:Dashboard');
    
    // Skema Sertifikasi Admin Routes (Master Data)
    Route::resource('skema-sertifikasi', App\Http\Controllers\AdminUI\SkemaSertifikasiController::class)->middleware('check.permission:Dashboard');
    
    // Unit Kompetensi Admin Routes (Master Data)
    Route::resource('unit-kompetensi', App\Http\Controllers\AdminUI\UnitKompetensiController::class)->middleware('check.permission:Dashboard');
    
    // KUK (Kriteria Unjuk Kerja) Admin Routes (Master Data)
    Route::resource('kuk', App\Http\Controllers\AdminUI\KukController::class)->middleware('check.permission:Dashboard');
    
    // API untuk dropdown berantai Unit Kompetensi by Skema
    Route::get('api/unit-kompetensi-by-skema/{skemaId}', [App\Http\Controllers\AdminUI\KukController::class, 'getUnitBySkema'])->name('api.unit-by-skema')->middleware('check.permission:Dashboard');
    
    // Pendaftaran Sertifikasi Admin Routes
    Route::get('pendaftaran-sertifikasi', [App\Http\Controllers\AdminUI\PendaftaranSertifikasiAdminController::class, 'index'])->name('pendaftaran-sertifikasi.index')->middleware('check.permission:pendaftaran_sertifikasi.view');
    Route::get('pendaftaran-sertifikasi/{id}', [App\Http\Controllers\AdminUI\PendaftaranSertifikasiAdminController::class, 'show'])->name('pendaftaran-sertifikasi.show')->middleware('check.permission:pendaftaran_sertifikasi.view');
    Route::get('pendaftaran-sertifikasi/{id}/audit-pdf', [App\Http\Controllers\AdminUI\PendaftaranSertifikasiAdminController::class, 'exportAuditEvidence'])->name('pendaftaran-sertifikasi.audit-pdf')->middleware('check.permission:pendaftaran_sertifikasi.view');
    Route::post('pendaftaran-sertifikasi/{id}/assign-skema', [App\Http\Controllers\AdminUI\PendaftaranSertifikasiAdminController::class, 'assignSkema'])->name('pendaftaran-sertifikasi.assign-skema')->middleware('check.permission:pendaftaran_sertifikasi.assign_skema');
    Route::patch('pendaftaran-sertifikasi/{id}/verifikasi', [App\Http\Controllers\AdminUI\PendaftaranSertifikasiAdminController::class, 'verifikasi'])->name('pendaftaran-sertifikasi.verifikasi')->middleware('check.permission:pendaftaran_sertifikasi.verify');
    Route::patch('pendaftaran-sertifikasi/{id}/tolak', [App\Http\Controllers\AdminUI\PendaftaranSertifikasiAdminController::class, 'tolak'])->name('pendaftaran-sertifikasi.tolak')->middleware('check.permission:pendaftaran_sertifikasi.verify');
    
    // Asesmen Routes (Asesor - staff is legacy alias for asesor)
    Route::prefix('asesmen')->name('asesmen.')->middleware('check.role:asesor|staff')->group(function () {
        Route::get('/', [App\Http\Controllers\AdminUI\AsesmenController::class, 'index'])->name('index');
        Route::get('/{pendaftaran}/mulai', [App\Http\Controllers\AdminUI\AsesmenController::class, 'mulaiAsesmen'])->name('mulai');
        Route::post('/{pendaftaran}/simpan', [App\Http\Controllers\AdminUI\AsesmenController::class, 'simpanAsesmen'])->name('simpan');
        Route::get('/detail/{id}', [App\Http\Controllers\AdminUI\AsesmenController::class, 'show'])->name('show');
        Route::get('/detail/{id}/audit-pdf', [App\Http\Controllers\AdminUI\AsesmenController::class, 'exportAuditEvidence'])->name('audit-pdf');
    });
    
    // Keputusan Sertifikasi Routes (Komite Teknis ONLY - Audit BNSP Compliant)
    Route::prefix('keputusan')->name('keputusan.')->middleware('check.role:komite_teknis')->group(function () {
        Route::get('/', [App\Http\Controllers\AdminUI\KeputusanSertifikasiController::class, 'index'])->name('index');
        Route::get('/{pendaftaran}', [App\Http\Controllers\AdminUI\KeputusanSertifikasiController::class, 'show'])->name('show');
        Route::get('/{pendaftaran}/audit-pdf', [App\Http\Controllers\AdminUI\KeputusanSertifikasiController::class, 'exportAuditEvidence'])->name('audit-pdf');
        Route::post('/{pendaftaran}/simpan', [App\Http\Controllers\AdminUI\KeputusanSertifikasiController::class, 'simpan'])->name('simpan');
    });
    
    // Sertifikat Routes (Komite Teknis ONLY)
    Route::prefix('sertifikat')->name('sertifikat.')->middleware('check.role:komite_teknis')->group(function () {
        Route::get('/', [App\Http\Controllers\AdminUI\SertifikatController::class, 'index'])->name('index');
        Route::post('/{pendaftaran}/terbit', [App\Http\Controllers\AdminUI\SertifikatController::class, 'terbitkan'])->name('terbit');
        Route::get('/{id}', [App\Http\Controllers\AdminUI\SertifikatController::class, 'show'])->name('show');
        Route::get('/{id}/download', [App\Http\Controllers\AdminUI\SertifikatController::class, 'download'])->name('download');
        Route::get('/{id}/preview', [App\Http\Controllers\AdminUI\SertifikatController::class, 'preview'])->name('preview');
        Route::post('/{id}/regenerate', [App\Http\Controllers\AdminUI\SertifikatController::class, 'regenerate'])->name('regenerate');
    });
    
    // Audit Log Routes (Super Admin & Admin ONLY - BNSP Compliance)
    Route::prefix('audit-log')->name('audit-log.')->middleware('check.role:admin')->group(function () {
        Route::get('/', [App\Http\Controllers\AdminUI\AuditLogController::class, 'index'])->name('index');
        Route::get('/export', [App\Http\Controllers\AdminUI\AuditLogController::class, 'export'])->name('export');
        Route::get('/statistics', [App\Http\Controllers\AdminUI\AuditLogController::class, 'statistics'])->name('statistics');
        Route::get('/{id}', [App\Http\Controllers\AdminUI\AuditLogController::class, 'show'])->name('show');
    });
    
    // Audit Evidence PDF (Super Admin ONLY - BNSP Audit Document)
    Route::prefix('audit-evidence')->name('audit-evidence.')->middleware('check.role:super admin')->group(function () {
        Route::get('/pdf', [App\Http\Controllers\AdminUI\AuditEvidenceController::class, 'generatePdf'])->name('pdf');
        Route::get('/preview', [App\Http\Controllers\AdminUI\AuditEvidenceController::class, 'preview'])->name('preview');
    });
    
    // =====================================================
    // SYSTEM SETTINGS ROUTES (Super Admin ONLY)
    // =====================================================
    Route::prefix('settings')->name('settings.')->group(function () {
        // Email Settings
        Route::get('/email', [App\Http\Controllers\AdminUI\EmailSettingController::class, 'index'])->name('email');
        Route::post('/email', [App\Http\Controllers\AdminUI\EmailSettingController::class, 'store'])->name('email.store');
        Route::post('/email/test', [App\Http\Controllers\AdminUI\EmailSettingController::class, 'sendTest'])->name('email.test');
        
        // Branding & Logo Settings
        Route::get('/branding', [App\Http\Controllers\AdminUI\BrandingController::class, 'index'])->name('branding');
        Route::post('/branding', [App\Http\Controllers\AdminUI\BrandingController::class, 'update'])->name('branding.update');
        Route::delete('/branding', [App\Http\Controllers\AdminUI\BrandingController::class, 'destroy'])->name('branding.destroy');
    });
    
    // =====================================================
    // EMAIL PREVIEW & RESEND ROUTES (Admin/Super Admin)
    // =====================================================
    Route::prefix('email')->name('email.')->group(function () {
        Route::get('/preview/{type}/{id}', [App\Http\Controllers\AdminUI\EmailPreviewController::class, 'preview'])->name('preview');
        Route::post('/resend/{type}/{id}', [App\Http\Controllers\AdminUI\EmailPreviewController::class, 'resend'])->name('resend');
        Route::get('/history/{type}/{id}', [App\Http\Controllers\AdminUI\EmailPreviewController::class, 'history'])->name('history');
        Route::get('/status/{type}/{id}', [App\Http\Controllers\AdminUI\EmailPreviewController::class, 'status'])->name('status');
    });
    
    // =====================================================
    // AUDIT EVIDENCE PDF ROUTES (Admin/Super Admin)
    // Generate PDF bukti audit untuk keperluan BNSP/ISO 17024
    // =====================================================
    Route::prefix('audit')->name('audit.')->group(function () {
        // Email Audit Evidence PDF
        Route::get('/email/{type}/{id}/pdf', [App\Http\Controllers\AdminUI\EmailAuditEvidenceController::class, 'download'])->name('email.pdf');
        Route::get('/email/{type}/{id}/preview', [App\Http\Controllers\AdminUI\EmailAuditEvidenceController::class, 'preview'])->name('email.preview');
        Route::get('/email/types', [App\Http\Controllers\AdminUI\EmailAuditEvidenceController::class, 'types'])->name('email.types');
        
        // Final Audit Package PDF
        Route::get('/final-package/pdf', [App\Http\Controllers\Admin\AuditPackageController::class, 'generateFinalPackage'])->name('final-package.pdf');
        Route::get('/final-package/preview', [App\Http\Controllers\Admin\AuditPackageController::class, 'preview'])->name('final-package.preview');
    });
    
    // Request Quote Routes
    Route::prefix('request-quote')->name('request-quote.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\RequestQuoteController::class, 'index'])->name('index')->middleware('check.permission:Dashboard');
        Route::post('/update', [App\Http\Controllers\Admin\RequestQuoteController::class, 'update'])->name('update')->middleware('check.permission:Dashboard');
        Route::get('/messages', [App\Http\Controllers\Admin\RequestQuoteController::class, 'messages'])->name('messages')->middleware('check.permission:Dashboard');
        Route::delete('/messages/{id}', [App\Http\Controllers\Admin\RequestQuoteController::class, 'destroyMessage'])->name('messages.destroy')->middleware('check.permission:Dashboard');
        
        // Request Quote Services Routes
        Route::prefix('services')->name('services.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\RequestQuoteServiceController::class, 'index'])->name('index')->middleware('check.permission:Dashboard');
            Route::get('/create', [App\Http\Controllers\Admin\RequestQuoteServiceController::class, 'create'])->name('create')->middleware('check.permission:Dashboard');
            Route::post('/', [App\Http\Controllers\Admin\RequestQuoteServiceController::class, 'store'])->name('store')->middleware('check.permission:Dashboard');
            Route::get('/{service}/edit', [App\Http\Controllers\Admin\RequestQuoteServiceController::class, 'edit'])->name('edit')->middleware('check.permission:Dashboard');
            Route::put('/{service}', [App\Http\Controllers\Admin\RequestQuoteServiceController::class, 'update'])->name('update')->middleware('check.permission:Dashboard');
            Route::delete('/{service}', [App\Http\Controllers\Admin\RequestQuoteServiceController::class, 'destroy'])->name('destroy')->middleware('check.permission:Dashboard');
            Route::post('/{service}/toggle-status', [App\Http\Controllers\Admin\RequestQuoteServiceController::class, 'toggleStatus'])->name('toggle-status')->middleware('check.permission:Dashboard');
        });
        
        // Request Quote Inbox Routes
        Route::prefix('inbox')->name('inbox.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\RequestQuoteInboxController::class, 'index'])->name('index')->middleware('check.permission:Dashboard');
            Route::get('/{id}', [App\Http\Controllers\Admin\RequestQuoteInboxController::class, 'show'])->name('show')->middleware('check.permission:Dashboard');
            Route::post('/{id}/update-status', [App\Http\Controllers\Admin\RequestQuoteInboxController::class, 'updateStatus'])->name('update-status')->middleware('check.permission:Dashboard');
            Route::post('/{id}/reply-email', [App\Http\Controllers\Admin\RequestQuoteInboxController::class, 'replyEmail'])->name('reply-email'); // Permission check removed temporarily
            Route::post('/{id}/reply-whatsapp', [App\Http\Controllers\Admin\RequestQuoteInboxController::class, 'replyWhatsapp'])->name('reply-whatsapp'); // Permission check removed temporarily
            Route::delete('/{id}', [App\Http\Controllers\Admin\RequestQuoteInboxController::class, 'destroy'])->name('destroy')->middleware('check.permission:Dashboard');
        });
    });
        
        // Header Blog Routes (HARUS SEBELUM resource blog!)
        Route::prefix('blog/header')->name('blog.header.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\HeaderBlogController::class, 'index'])->name('index')->middleware('check.permission:Blog');
            Route::put('/', [App\Http\Controllers\Admin\HeaderBlogController::class, 'update'])->name('update')->middleware('check.permission:Blog');
        });
        
        // Blog Admin Routes
        Route::resource('blog', App\Http\Controllers\Admin\ArtikelController::class)->parameters([
            'blog' => 'artikel'
        ])->middleware('check.permission:Blog');
        Route::post('blog/{artikel}/autosave', [App\Http\Controllers\Admin\ArtikelController::class, 'autoSave'])->name('blog.autosave')->middleware('check.permission:Blog');
        
        // Header Projects Routes (HARUS SEBELUM resource projects!)
        Route::prefix('projects/header')->name('projects.header.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\HeaderProjectsController::class, 'index'])->name('index')->middleware('check.permission:Projects');
            Route::get('/create', [App\Http\Controllers\Admin\HeaderProjectsController::class, 'create'])->name('create')->middleware('check.permission:Projects');
            Route::post('/', [App\Http\Controllers\Admin\HeaderProjectsController::class, 'store'])->name('store')->middleware('check.permission:Projects');
            Route::get('/{id}/edit', [App\Http\Controllers\Admin\HeaderProjectsController::class, 'edit'])->name('edit')->middleware('check.permission:Projects');
            Route::put('/{id}', [App\Http\Controllers\Admin\HeaderProjectsController::class, 'update'])->name('update')->middleware('check.permission:Projects');
            Route::delete('/{id}', [App\Http\Controllers\Admin\HeaderProjectsController::class, 'destroy'])->name('destroy')->middleware('check.permission:Projects');
        });
        
        // Projects Admin Routes
        Route::resource('projects', App\Http\Controllers\Admin\ProjectController::class)->parameters([
            'projects' => 'project'
        ])->middleware('check.permission:Projects');
        
        // Kategori Project Admin Routes
        Route::resource('kategori-project', App\Http\Controllers\KategoriProjectController::class)->except(['show', 'create', 'edit'])->parameters([
            'kategori-project' => 'kategoriProject'
        ]);
        
        // Kategori Admin Routes
        Route::resource('kategori', App\Http\Controllers\Admin\KategoriController::class)->except(['show', 'create', 'edit'])->parameters([
            'kategori' => 'kategori'
        ]);
        
        // Komentar Admin Routes
    Route::get('komentar', [App\Http\Controllers\Admin\KomentarController::class, 'index'])->name('komentar.index')->middleware('check.permission:Blog');
    Route::post('komentar/{komentar}/approve', [App\Http\Controllers\Admin\KomentarController::class, 'approve'])->name('komentar.approve')->middleware('check.permission:Blog');
    Route::post('komentar/{komentar}/reject', [App\Http\Controllers\Admin\KomentarController::class, 'reject'])->name('komentar.reject')->middleware('check.permission:Blog');
    Route::post('komentar/{komentar}/reply', [App\Http\Controllers\Admin\KomentarController::class, 'reply'])->name('komentar.reply')->middleware('check.permission:Blog');
    Route::delete('komentar/{komentar}', [App\Http\Controllers\Admin\KomentarController::class, 'destroy'])->name('komentar.destroy')->middleware('check.permission:Blog');
    Route::get('artikel/{artikel}/komentar', [App\Http\Controllers\Admin\KomentarController::class, 'byArtikel'])->name('artikel.komentar')->middleware('check.permission:Blog');
        
        // OLD SYSTEM ROUTES - DISABLED TO AVOID CONFLICTS
        // Route::get('about/headers', [AdminAboutController::class, 'headers'])->name('about.headers');
        // Route::get('about/contents', [AdminAboutController::class, 'contents'])->name('about.contents');
        // Route::prefix('about')->name('about.')->group(function () {
        //     Route::resource('hero', AboutHeroController::class);
        //     Route::resource('content', AboutContentController::class);  
        //     Route::resource('testimonials', AboutTestimonialController::class);
        // });
        
        // BACKUP ROUTE (for testing old system if needed)
        Route::get('about-old', [AdminAboutController::class, 'index'])->name('about-old');
        
        // CKEditor Upload Route
        Route::post('ckeditor/upload', [CKEditorController::class, 'upload'])->name('ckeditor.upload');
        
        Route::post('logout', [AdminUIController::class, 'logout'])->name('logout');
    });
});

// Route fallback untuk file HTML dari folder website
Route::get('/{page}.html', function ($page) {
    $path = base_path("website/{$page}.html");
    if (File::exists($path)) {
        return response(File::get($path))
            ->header('Content-Type', 'text/html');
    }
    abort(404);
})->where('page', '.*')->name('website.html');

// Simple test route outside middleware for debugging (CSRF exempt)  
// Simple test page for About management
Route::get('/test-about-admin', function() {
    $heroSection = AboutHeroSection::first();
    return view('adminui.about.simple-test', compact('heroSection'));
})->name('test.about.admin');

// Debug page for About data
Route::get('/debug-about-data', function() {
    $heroSection = AboutHeroSection::first();
    $contents = AboutContentSection::latest()->get();
    $testimonials = AboutTestimonialSection::latest()->get();
    
    return view('debug.about-data', compact('heroSection', 'contents', 'testimonials'));
})->name('debug.about.data');

Route::post('/test-hero-submit', function(Request $request) {
    Log::info('Test hero form submission received', [
        'data' => $request->all(),
        'timestamp' => now()
    ]);
    
    try {
        AboutHeroSection::truncate();
        $hero = AboutHeroSection::create([
            'tagline' => $request->get('tagline', 'Default Tagline'),
            'projects_completed' => $request->get('projects_completed', 0),
            'satisfied_customers' => $request->get('satisfied_customers', 0),
            'awards_received' => $request->get('awards_received', 0),
            'years_experience' => $request->get('years_experience', 0),
        ]);
        
        Log::info('Hero section created successfully', ['hero' => $hero]);
        return redirect('/test-about-admin')->with('success', 'Hero section updated successfully! Data has been saved to database.');
    } catch (\Exception $e) {
        Log::error('Failed to create hero section', ['error' => $e->getMessage()]);
        return redirect('/test-about-admin')->with('error', 'Failed to update: ' . $e->getMessage());
    }
})->name('test.hero.submit');

require __DIR__.'/auth.php';

// Debug route - remove after testing
Route::get('/test-request-quote', function() {
    $data = \App\Models\RequestQuoteSetting::where('status', true)->first();
    return response()->json($data);
});

// Clear OPcache route
Route::get('/clear-opcache', function() {
    if (function_exists('opcache_reset')) {
        opcache_reset();
    }
    \Artisan::call('view:clear');
    \Artisan::call('cache:clear');
    \Artisan::call('config:clear');
    
    $data = \DB::table('request_quote_settings')->where('status', 1)->first();
    
    return response()->json([
        'message' => 'All caches cleared!',
        'timestamp' => now()->toDateTimeString(),
        'request_quote_data' => $data
    ]);
});

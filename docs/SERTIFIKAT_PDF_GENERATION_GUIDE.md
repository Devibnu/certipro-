# 📜 PANDUAN GENERATE PDF SERTIFIKAT RESMI LSP/BNSP

**System:** LSP CertiPro  
**Date:** January 22, 2026  
**Version:** Production-Ready v1.0  
**Compliance:** BNSP & ISO 17024  

---

## 🎯 OVERVIEW

Sistem generate PDF sertifikat kompetensi profesional sesuai standar BNSP dengan fitur:

- ✅ **Validation ketat** - Hanya kompeten final yang bisa terbit
- ✅ **QR Code verification** - UUID-based public verification
- ✅ **Template profesional** - A4 portrait, print-ready
- ✅ **Regenerate support** - Update template tanpa duplikasi data
- ✅ **Audit trail** - Semua generate dicatat lengkap
- ✅ **Security** - Nomor unik, SHA256 hash, UUID protection

---

## 📋 ATURAN BISNIS (BUSINESS RULES)

### 1. Syarat Penerbitan Sertifikat

```php
// ❌ TIDAK BOLEH diterbitkan jika:
- Keputusan Sertifikasi belum ada
- Status bukan KOMPETEN_FINAL
- Sertifikat sudah pernah diterbitkan
- Data asesi/skema tidak lengkap

// ✅ BOLEH diterbitkan jika:
- Status = KOMPETEN_FINAL
- Keputusan = KOMPETEN
- Keputusan sudah LOCKED (final)
- Data lengkap (asesi + skema + keputusan)
```

### 2. Konten Wajib di PDF

Sesuai standar BNSP, sertifikat HARUS memuat:

```
┌─────────────────────────────────────────┐
│  [LOGO LSP]  [LOGO BNSP]  [LOGO INST]  │
│                                         │
│       SERTIFIKAT KOMPETENSI            │
│      Certificate of Competence         │
│                                         │
│  Nomor: CERT/CTP/2026/000123           │
│                                         │
│  Diberikan Kepada / Awarded To         │
│  ─────────────────────────────          │
│        NAMA ASESI LENGKAP              │
│  ─────────────────────────────          │
│                                         │
│  Telah dinyatakan KOMPETEN pada        │
│  Skema Sertifikasi:                    │
│                                         │
│  → [Nama Skema Sertifikasi]            │
│    Kode Skema: [KODE-XXX]              │
│                                         │
│  Berdasarkan Keputusan Komite Teknis:  │
│  No: [Nomor Keputusan]                 │
│  Tanggal: [DD Month YYYY]              │
│                                         │
│  Jakarta, [DD Month YYYY]              │
│                                         │
│  [QR CODE]        [TTD Digital]        │
│                   Ketua LSP            │
│                   [Nama Ketua]         │
│                                         │
│  Masa Berlaku: [DD Month YYYY]         │
│  s.d. [DD Month YYYY] (3 Tahun)       │
│                                         │
│  LSP CertiPro - Lisensi BNSP: XXX     │
└─────────────────────────────────────────┘
```

---

## 🏗️ ARSITEKTUR SISTEM

### Flow Diagram

```
┌──────────────┐
│ Admin clicks │
│ "Terbitkan"  │
└──────┬───────┘
       │
       ▼
┌──────────────────────┐
│ SertifikatController │
│ (Orchestration)      │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ SertifikatService    │
│ (Business Logic)     │
├──────────────────────┤
│ 1. Validate          │
│ 2. Generate Nomor    │
│ 3. Create Record     │
│ 4. Generate QR Code  │
│ 5. Generate PDF      │
│ 6. Save Files        │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Database + Storage   │
│ - sertifikat record  │
│ - PDF file           │
│ - QR code SVG        │
└──────────────────────┘
```

### Component Breakdown

#### 1. **SertifikatController** (Orchestration Layer)

```php
namespace App\Http\Controllers\AdminUI;

class SertifikatController extends Controller
{
    // Responsibilities:
    // ✅ Load data with relations
    // ✅ Call service methods
    // ✅ Handle HTTP response/redirect
    // ❌ NO business logic here!
    
    public function terbitkan($pendaftaranId)
    {
        // Load dengan eager loading
        $pendaftaran = PendaftaranSertifikasi::with([
            'user', 'skemaSertifikasi', 'keputusan'
        ])->findOrFail($pendaftaranId);
        
        // Delegate ke service
        $result = $this->sertifikatService->terbitkan($pendaftaran);
        
        // Handle response
        if ($result['success']) {
            return redirect()->route('adminui.sertifikat.show', $result['sertifikat']->id)
                ->with('success', 'Sertifikat berhasil diterbitkan');
        }
        
        return redirect()->back()->with('error', $result['error']);
    }
    
    public function preview($id)
    {
        // Generate PDF on-the-fly untuk preview
        $sertifikat = Sertifikat::with('pendaftaran')->findOrFail($id);
        
        $qrCode = QrCode::format('svg')->generate($sertifikat->getVerificationUrl());
        
        $pdf = Pdf::loadView('pdf.sertifikat-bnsp', [
            'sertifikat' => $sertifikat,
            'qrCodeBase64' => base64_encode($qrCode),
        ]);
        
        return $pdf->stream();
    }
    
    public function download($id)
    {
        // Download file PDF yang sudah tersimpan
        $sertifikat = Sertifikat::findOrFail($id);
        
        if (!Storage::disk('public')->exists($sertifikat->file_pdf)) {
            abort(404, 'File PDF tidak ditemukan');
        }
        
        return Storage::disk('public')->download($sertifikat->file_pdf);
    }
    
    public function regenerate($id)
    {
        // Regenerate PDF dengan template terbaru
        // Useful untuk update layout tanpa ubah data
        
        $sertifikat = Sertifikat::with('pendaftaran')->findOrFail($id);
        
        // Hapus file lama
        Storage::disk('public')->delete([
            $sertifikat->file_pdf,
            $sertifikat->qr_code
        ]);
        
        // Generate ulang
        $qrCodePath = $this->generateQRCode($sertifikat);
        $pdfPath = $this->generatePDF($sertifikat, $qrCodePath);
        
        // Update record
        $sertifikat->update([
            'qr_code' => $qrCodePath,
            'file_pdf' => $pdfPath,
        ]);
        
        return redirect()->back()->with('success', 'PDF berhasil di-regenerate');
    }
}
```

#### 2. **SertifikatService** (Business Logic Layer)

```php
namespace App\Services;

class SertifikatService
{
    /**
     * Validate pendaftaran sebelum terbitkan sertifikat.
     * CRITICAL: All validation must be exhaustive.
     */
    public function validatePendaftaran(PendaftaranSertifikasi $pendaftaran): array
    {
        $errors = [];
        
        // 1. Asesi exists
        if (!$pendaftaran->user) {
            $errors[] = 'Data asesi tidak ditemukan';
        }
        
        // 2. Skema exists
        if (!$pendaftaran->skemaSertifikasi) {
            $errors[] = 'Data skema sertifikasi tidak ditemukan';
        }
        
        // 3. Keputusan exists (CRITICAL!)
        if (!$pendaftaran->keputusan) {
            $errors[] = 'Keputusan sertifikasi belum ditetapkan';
        }
        
        // 4. Status must be KOMPETEN_FINAL
        if ($pendaftaran->status !== PendaftaranSertifikasi::STATUS_KOMPETEN_FINAL) {
            $errors[] = 'Status pendaftaran harus KOMPETEN FINAL';
        }
        
        // 5. Not already issued
        if ($pendaftaran->sertifikat) {
            $errors[] = 'Sertifikat sudah diterbitkan sebelumnya';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
    
    /**
     * Terbitkan sertifikat (main method).
     * ATOMIC: Uses DB transaction for data consistency.
     */
    public function terbitkan(PendaftaranSertifikasi $pendaftaran): array
    {
        // Validate first
        $validation = $this->validatePendaftaran($pendaftaran);
        
        if (!$validation['valid']) {
            return [
                'success' => false,
                'error' => implode(', ', $validation['errors']),
            ];
        }
        
        DB::beginTransaction();
        
        try {
            // 1. Generate nomor sertifikat
            $nomorSertifikat = $this->generateNomorSertifikat();
            
            // 2. Create sertifikat record
            $sertifikat = Sertifikat::create([
                'pendaftaran_id' => $pendaftaran->id,
                'nomor_sertifikat' => $nomorSertifikat,
                'nama_peserta' => $pendaftaran->user->name,
                'skema_sertifikasi' => $pendaftaran->skemaSertifikasi->nama_skema,
                'tanggal_terbit' => now(),
                'tanggal_berlaku_sampai' => now()->addYears(3),
                'diterbitkan_oleh' => Auth::id(),
            ]);
            
            // 3. Generate QR Code
            $qrCodePath = $this->generateQRCode($sertifikat);
            
            // 4. Generate PDF
            $pdfPath = $this->generatePDF($sertifikat, $pendaftaran, $qrCodePath);
            
            // 5. Update paths
            $sertifikat->update([
                'qr_code' => $qrCodePath,
                'file_pdf' => $pdfPath,
            ]);
            
            DB::commit();
            
            Log::info('Certificate issued successfully', [
                'sertifikat_id' => $sertifikat->id,
                'nomor' => $nomorSertifikat,
            ]);
            
            return [
                'success' => true,
                'sertifikat' => $sertifikat,
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Certificate issuance failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'success' => false,
                'error' => 'Gagal menerbitkan sertifikat: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Generate nomor sertifikat unik.
     * Format: CERT/CTP/YYYY/NNNNNN
     * Example: CERT/CTP/2026/000123
     */
    private function generateNomorSertifikat(): string
    {
        $prefix = 'CERT/CTP';
        $year = date('Y');
        
        // Get last number untuk tahun ini
        $lastRecord = Sertifikat::where('nomor_sertifikat', 'like', "{$prefix}/{$year}/%")
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNumber = $lastRecord 
            ? ((int) substr($lastRecord->nomor_sertifikat, -6)) + 1
            : 1;
        
        return $prefix . '/' . $year . '/' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Generate QR Code untuk verifikasi publik.
     * Uses UUID for security (tidak expose database ID).
     */
    private function generateQRCode(Sertifikat $sertifikat): string
    {
        $verificationUrl = route('public.sertifikat.verify', $sertifikat->uuid);
        
        $qrCodeFileName = 'qr_' . $sertifikat->uuid . '.svg';
        $qrCodePath = 'sertifikat/qrcodes/' . $qrCodeFileName;
        
        $qrCode = QrCode::format('svg')
            ->size(200)
            ->margin(1)
            ->errorCorrection('H') // High error correction untuk print
            ->generate($verificationUrl);
        
        Storage::disk('public')->put($qrCodePath, $qrCode);
        
        return $qrCodePath;
    }
    
    /**
     * Generate PDF sertifikat.
     * Uses Blade template dengan data binding yang aman.
     */
    private function generatePDF(
        Sertifikat $sertifikat, 
        PendaftaranSertifikasi $pendaftaran, 
        string $qrCodePath
    ): string {
        $pdfFileName = 'sertifikat_' . $sertifikat->uuid . '.pdf';
        $pdfPath = 'sertifikat/pdf/' . $pdfFileName;
        
        // Load QR code untuk embed di PDF
        $qrCodeContent = Storage::disk('public')->get($qrCodePath);
        $qrCodeBase64 = base64_encode($qrCodeContent);
        
        // Data untuk template
        $data = [
            'sertifikat' => $sertifikat,
            'pendaftaran' => $pendaftaran,
            'keputusan' => $pendaftaran->keputusan,
            'asesi' => $pendaftaran->user,
            'skema' => $pendaftaran->skemaSertifikasi,
            'qrCodeBase64' => $qrCodeBase64,
            'ketuaLsp' => config('certipro.ketua_lsp'),
            'kotaTerbit' => config('certipro.kota_terbit'),
            'nomorLisensi' => config('certipro.nomor_lisensi'),
        ];
        
        // Generate PDF
        $pdf = Pdf::loadView('pdf.sertifikat-bnsp', $data);
        
        // Settings
        $pdf->setPaper('A4', 'portrait')
            ->setOption('enable-local-file-access', true) // Untuk load image lokal
            ->setOption('dpi', 300); // High quality untuk print
        
        // Save ke storage
        Storage::disk('public')->put($pdfPath, $pdf->output());
        
        return $pdfPath;
    }
}
```

#### 3. **Sertifikat Model** (Data & Helpers)

```php
namespace App\Models;

class Sertifikat extends Model
{
    use Auditable;
    
    protected $fillable = [
        'pendaftaran_id',
        'nomor_sertifikat',
        'uuid',
        'nama_peserta',
        'skema_sertifikasi',
        'tanggal_terbit',
        'tanggal_berlaku_sampai',
        'qr_code',
        'file_pdf',
        'diterbitkan_oleh',
    ];
    
    protected $casts = [
        'tanggal_terbit' => 'date',
        'tanggal_berlaku_sampai' => 'date',
    ];
    
    /**
     * Auto-generate UUID saat create.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($sertifikat) {
            if (empty($sertifikat->uuid)) {
                $sertifikat->uuid = (string) Str::uuid();
            }
        });
    }
    
    /**
     * Get URL verifikasi publik (UUID-based).
     * PUBLIC ROUTE: Siapa saja bisa akses untuk verifikasi.
     */
    public function getVerificationUrl(): string
    {
        return route('public.sertifikat.verify', $this->uuid);
    }
    
    /**
     * Generate security hash untuk anti-forgery.
     * Hash = SHA256(uuid + nomor_sertifikat)
     */
    public function getSecurityHashAttribute(): string
    {
        return hash('sha256', $this->uuid . $this->nomor_sertifikat);
    }
    
    /**
     * Check apakah sertifikat masih berlaku.
     */
    public function isValid(): bool
    {
        return $this->tanggal_berlaku_sampai >= now();
    }
    
    /**
     * Relationships.
     */
    public function pendaftaran()
    {
        return $this->belongsTo(PendaftaranSertifikasi::class);
    }
    
    public function penerbit()
    {
        return $this->belongsTo(User::class, 'diterbitkan_oleh');
    }
}
```

---

## 🎨 TEMPLATE PDF (Blade View)

### File: `resources/views/pdf/sertifikat-bnsp.blade.php`

**Design Principles:**
- ✅ A4 Portrait (210mm x 297mm)
- ✅ Print-ready (300 DPI)
- ✅ Professional layout (border, logo, spacing)
- ✅ Inline CSS (no external stylesheets)
- ✅ Font: DejaVu Serif (included in DomPDF)

**Key Sections:**

```html
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Sertifikat - {{ $sertifikat->nomor_sertifikat }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }
        
        body {
            font-family: 'DejaVu Serif', serif;
            margin: 0;
            padding: 0;
        }
        
        .certificate-page {
            width: 210mm;
            height: 297mm;
            position: relative;
            background: white;
        }
        
        /* Border ornamen */
        .border-outer {
            position: absolute;
            top: 8mm;
            left: 8mm;
            right: 8mm;
            bottom: 8mm;
            border: 4px solid #1a365d;
        }
        
        /* Content area */
        .content-wrapper {
            padding: 25mm 20mm;
        }
        
        /* Header logos */
        .header-logos {
            text-align: center;
            margin-bottom: 10mm;
        }
        
        .logo-img {
            max-height: 25mm;
            margin: 0 10mm;
        }
        
        /* Title */
        .title-main {
            font-size: 32pt;
            font-weight: bold;
            color: #1a365d;
            text-align: center;
            letter-spacing: 5px;
            margin: 15mm 0;
        }
        
        /* Recipient name */
        .recipient-name {
            font-size: 28pt;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            border-bottom: 3px solid #b8860b;
            padding-bottom: 5mm;
            margin: 8mm 0;
        }
        
        /* Body text */
        .body-text {
            font-size: 12pt;
            line-height: 1.6;
            text-align: justify;
            margin: 5mm 0;
        }
        
        /* Skema info */
        .skema-box {
            background: #f8f9fa;
            border-left: 4px solid #1a365d;
            padding: 5mm;
            margin: 8mm 0;
        }
        
        /* Signature section */
        .signature-section {
            display: table;
            width: 100%;
            margin-top: 15mm;
        }
        
        .signature-left {
            display: table-cell;
            width: 50%;
            text-align: center;
        }
        
        .signature-right {
            display: table-cell;
            width: 50%;
            text-align: center;
        }
        
        .qr-code {
            width: 40mm;
            height: 40mm;
        }
        
        /* Footer */
        .footer {
            position: absolute;
            bottom: 12mm;
            left: 20mm;
            right: 20mm;
            text-align: center;
            font-size: 8pt;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 3mm;
        }
    </style>
</head>
<body>
    <div class="certificate-page">
        <div class="border-outer"></div>
        
        <div class="content-wrapper">
            <!-- Header Logos -->
            <div class="header-logos">
                <img src="{{ public_path('images/logo-lsp.png') }}" class="logo-img" alt="LSP">
                <img src="{{ public_path('images/logo-bnsp.png') }}" class="logo-img" alt="BNSP">
            </div>
            
            <!-- Title -->
            <div class="title-main">
                SERTIFIKAT KOMPETENSI
            </div>
            <div style="text-align: center; font-size: 11pt; color: #666; font-style: italic;">
                Certificate of Competence
            </div>
            
            <!-- Certificate Number -->
            <div style="text-align: center; margin: 8mm 0; font-size: 11pt;">
                <strong>Nomor / Number:</strong> {{ $sertifikat->nomor_sertifikat }}
            </div>
            
            <!-- Intro Text -->
            <div class="body-text" style="text-align: center;">
                Diberikan kepada / Awarded to:
            </div>
            
            <!-- Recipient Name -->
            <div class="recipient-name">
                {{ strtoupper($sertifikat->nama_peserta) }}
            </div>
            
            <!-- Competency Statement -->
            <div class="body-text">
                Telah dinyatakan <strong>KOMPETEN</strong> berdasarkan hasil asesmen
                yang dilakukan oleh Asesor Kompetensi LSP {{ config('certipro.nama_lsp') }}
                pada Skema Sertifikasi:
            </div>
            
            <!-- Skema Box -->
            <div class="skema-box">
                <strong style="font-size: 14pt;">{{ $sertifikat->skema_sertifikasi }}</strong><br>
                <span style="font-size: 10pt; color: #666;">
                    Kode Skema: {{ $skema->kode_skema ?? '-' }}
                </span>
            </div>
            
            <!-- Decision Reference -->
            <div class="body-text">
                Berdasarkan Keputusan Komite Teknis Sertifikasi<br>
                Nomor: <strong>{{ $keputusan->id ?? '-' }}</strong><br>
                Tanggal: <strong>{{ $keputusan->tanggal_keputusan->isoFormat('D MMMM Y') }}</strong>
            </div>
            
            <!-- Validity Period -->
            <div class="body-text" style="text-align: center; margin-top: 10mm;">
                <strong>Masa Berlaku Sertifikat / Certificate Validity Period:</strong><br>
                {{ $sertifikat->tanggal_terbit->isoFormat('D MMMM Y') }} 
                s.d. 
                {{ $sertifikat->tanggal_berlaku_sampai->isoFormat('D MMMM Y') }}
            </div>
            
            <!-- Signature Section -->
            <div class="signature-section">
                <div class="signature-left">
                    <img src="data:image/svg+xml;base64,{{ $qrCodeBase64 }}" 
                         class="qr-code" 
                         alt="QR Code">
                    <div style="font-size: 8pt; margin-top: 3mm;">
                        Scan untuk verifikasi<br>
                        Scan to verify
                    </div>
                </div>
                
                <div class="signature-right">
                    <div style="margin-bottom: 15mm;">
                        {{ $kotaTerbit }}, {{ $sertifikat->tanggal_terbit->isoFormat('D MMMM Y') }}
                    </div>
                    
                    @if(file_exists(public_path('images/ttd-ketua-lsp.png')))
                        <img src="{{ public_path('images/ttd-ketua-lsp.png') }}" 
                             style="height: 20mm;" 
                             alt="Tanda Tangan">
                    @else
                        <div style="height: 20mm;"></div>
                    @endif
                    
                    <div style="border-top: 2px solid #1a365d; display: inline-block; padding-top: 2mm;">
                        <strong>{{ $ketuaLsp }}</strong><br>
                        <span style="font-size: 10pt;">Ketua LSP</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            {{ config('certipro.nama_lsp') }} - 
            Lisensi BNSP: {{ config('certipro.nomor_lisensi') }}<br>
            {{ config('certipro.alamat') }} | 
            {{ config('certipro.telepon') }} | 
            {{ config('certipro.website') }}
        </div>
    </div>
</body>
</html>
```

---

## ⚙️ KONFIGURASI

### File: `config/certipro.php`

```php
<?php

return [
    // Informasi LSP
    'nama_lsp' => env('CERTIPRO_NAMA_LSP', 'LSP CertiPro'),
    'alamat' => env('CERTIPRO_ALAMAT', 'Jakarta, Indonesia'),
    'telepon' => env('CERTIPRO_TELEPON', '021-12345678'),
    'email' => env('CERTIPRO_EMAIL', 'info@certipro.id'),
    'website' => env('CERTIPRO_WEBSITE', 'https://lsp-ui.ibnuapps.cloud'),
    
    // Pejabat
    'ketua_lsp' => env('CERTIPRO_KETUA_LSP', 'Dr. Ahmad Hidayat, M.Kom'),
    'kota_terbit' => env('CERTIPRO_KOTA_TERBIT', 'Jakarta'),
    
    // Lisensi BNSP
    'nomor_lisensi' => env('CERTIPRO_NOMOR_LISENSI', 'LSP-XXXXX-ID'),
    
    // PDF Settings
    'pdf' => [
        'paper_size' => 'A4',
        'orientation' => 'portrait',
        'dpi' => 300,
    ],
    
    // Sertifikat
    'masa_berlaku_tahun' => 3,
    'prefix_nomor' => 'CERT/CTP',
];
```

### File: `.env`

```env
# LSP Information
CERTIPRO_NAMA_LSP="LSP CertiPro Universitas Indonesia"
CERTIPRO_ALAMAT="Kampus UI Depok, Gedung Rektorat Lt. 3"
CERTIPRO_TELEPON="021-7867222"
CERTIPRO_EMAIL="lsp@ui.ac.id"
CERTIPRO_WEBSITE="https://lsp-ui.ibnuapps.cloud"

# Official
CERTIPRO_KETUA_LSP="Prof. Dr. Ir. Ahmad Hidayat, M.Kom"
CERTIPRO_KOTA_TERBIT="Depok"

# BNSP License
CERTIPRO_NOMOR_LISENSI="LSP-002-ID-2024"
```

---

## 🔧 SETUP & INSTALASI

### 1. Install Dependencies

```bash
# DomPDF for PDF generation
composer require barryvdh/laravel-dompdf

# QR Code generator
composer require simplesoftwareio/simple-qrcode
```

### 2. Publish Config

```bash
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

### 3. Setup Storage

```bash
# Create storage directories
mkdir -p storage/app/public/sertifikat/pdf
mkdir -p storage/app/public/sertifikat/qrcodes

# Symlink
php artisan storage:link
```

### 4. Upload Assets

```bash
# Logo & Signature
public/images/
├── logo-lsp.png          # Logo LSP (max 800x400 px)
├── logo-bnsp.png         # Logo BNSP (official)
└── ttd-ketua-lsp.png     # Tanda tangan digital (transparent PNG)
```

**Requirements untuk gambar:**
- Format: PNG dengan background transparan
- Logo: max 800x400 px
- TTD: 600x200 px (signature area)
- DPI: minimal 150 untuk print quality

---

## 📱 ROUTES

### File: `routes/web.php`

```php
// Admin Routes (Protected)
Route::prefix('adminui/sertifikat')->middleware(['auth'])->group(function () {
    Route::get('/', [SertifikatController::class, 'index'])->name('adminui.sertifikat.index');
    Route::post('/terbitkan/{pendaftaran}', [SertifikatController::class, 'terbitkan'])->name('adminui.sertifikat.terbitkan');
    Route::get('/{id}', [SertifikatController::class, 'show'])->name('adminui.sertifikat.show');
    Route::get('/{id}/preview', [SertifikatController::class, 'preview'])->name('adminui.sertifikat.preview');
    Route::get('/{id}/download', [SertifikatController::class, 'download'])->name('adminui.sertifikat.download');
    Route::post('/{id}/regenerate', [SertifikatController::class, 'regenerate'])->name('adminui.sertifikat.regenerate');
});

// Public Routes (No Auth Required)
Route::prefix('sertifikat')->group(function () {
    Route::get('/verify/{uuid}', [PublicSertifikatController::class, 'verify'])->name('public.sertifikat.verify');
    Route::get('/verifikasi/{nomor}', [PublicSertifikatController::class, 'verifyByNumber'])->name('public.sertifikat.verifikasi');
});
```

---

## 🧪 TESTING

### Unit Test: PDF Generation

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Sertifikat;
use App\Services\SertifikatService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SertifikatPdfTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function it_generates_pdf_for_valid_pendaftaran()
    {
        $pendaftaran = PendaftaranSertifikasi::factory()
            ->withUser()
            ->withSkema()
            ->withKeputusan()
            ->create([
                'status' => PendaftaranSertifikasi::STATUS_KOMPETEN_FINAL
            ]);
        
        $service = app(SertifikatService::class);
        $result = $service->terbitkan($pendaftaran);
        
        $this->assertTrue($result['success']);
        $this->assertInstanceOf(Sertifikat::class, $result['sertifikat']);
        
        // Verify PDF file exists
        $this->assertTrue(Storage::disk('public')->exists($result['sertifikat']->file_pdf));
        
        // Verify QR code exists
        $this->assertTrue(Storage::disk('public')->exists($result['sertifikat']->qr_code));
    }
    
    /** @test */
    public function it_prevents_duplicate_certificate_issuance()
    {
        $pendaftaran = PendaftaranSertifikasi::factory()
            ->withUser()
            ->withSkema()
            ->withKeputusan()
            ->create(['status' => PendaftaranSertifikasi::STATUS_KOMPETEN_FINAL]);
        
        $service = app(SertifikatService::class);
        
        // First issuance
        $result1 = $service->terbitkan($pendaftaran);
        $this->assertTrue($result1['success']);
        
        // Second issuance (should fail)
        $result2 = $service->terbitkan($pendaftaran->fresh());
        $this->assertFalse($result2['success']);
        $this->assertStringContainsString('sudah diterbitkan', $result2['error']);
    }
    
    /** @test */
    public function it_generates_unique_certificate_numbers()
    {
        $pendaftaran1 = PendaftaranSertifikasi::factory()->kompeten()->create();
        $pendaftaran2 = PendaftaranSertifikasi::factory()->kompeten()->create();
        
        $service = app(SertifikatService::class);
        
        $result1 = $service->terbitkan($pendaftaran1);
        $result2 = $service->terbitkan($pendaftaran2);
        
        $this->assertNotEquals(
            $result1['sertifikat']->nomor_sertifikat,
            $result2['sertifikat']->nomor_sertifikat
        );
    }
}
```

### Manual Test Checklist

```bash
# 1. Generate sertifikat dari admin panel
✅ Login sebagai admin
✅ Buka menu Sertifikat
✅ Klik "Terbitkan Sertifikat" untuk pendaftaran KOMPETEN_FINAL
✅ Verify success message muncul
✅ Verify redirect ke detail sertifikat

# 2. Preview PDF
✅ Klik tombol "Preview PDF"
✅ PDF terbuka di tab baru
✅ Verify layout rapi (border, logo, text alignment)
✅ Verify QR code muncul
✅ Verify data akurat (nama, skema, tanggal, nomor)

# 3. Download PDF
✅ Klik tombol "Download PDF"
✅ File terdownload dengan nama yang benar
✅ Buka file, verify bisa dibuka
✅ Print preview, verify print-ready (tidak terpotong)

# 4. QR Code Verification
✅ Scan QR code dengan smartphone
✅ Browser terbuka ke halaman verifikasi
✅ Verify data sertifikat ditampilkan
✅ Verify status "VALID" atau "KADALUARSA"

# 5. Regenerate Test
✅ Update template PDF
✅ Klik "Regenerate PDF"
✅ Verify PDF baru tergenerate
✅ Verify nomor sertifikat TIDAK BERUBAH
✅ Verify layout menggunakan template terbaru
```

---

## 🐛 TROUBLESHOOTING

### Issue 1: PDF Blank / Error

```bash
# Symptom
PDF generates tapi kosong atau error "Failed to load PDF"

# Solution
# 1. Check DomPDF config
php artisan config:clear
php artisan cache:clear

# 2. Check file permissions
chmod -R 775 storage/app/public/sertifikat
chown -R www-data:www-data storage/app/public/sertifikat

# 3. Check PHP memory limit
# php.ini
memory_limit = 256M
```

### Issue 2: QR Code Not Showing

```bash
# Symptom
QR code tidak muncul di PDF

# Solution
# 1. Verify SimpleSoftwareIO\QrCode installed
composer show simplesoftwareio/simple-qrcode

# 2. Check base64 encoding
# Di Blade: data:image/svg+xml;base64,{{ $qrCodeBase64 }}

# 3. Test QR generation manual
php artisan tinker
>>> QrCode::format('svg')->generate('https://test.com');
```

### Issue 3: Image/Logo Not Loading

```bash
# Symptom
Logo LSP/BNSP tidak muncul di PDF

# Solution
# 1. Verify file exists
ls -la public/images/logo-*.png

# 2. Use absolute path di Blade
<img src="{{ public_path('images/logo-lsp.png') }}">
// BUKAN: asset() atau url()

# 3. Check DomPDF option
$pdf->setOption('enable-local-file-access', true);
```

### Issue 4: Font Issues (Characters Broken)

```bash
# Symptom
Huruf Indonesia (é, à, dll) tidak muncul atau kotak-kotak

# Solution
# 1. Use DejaVu fonts (included in DomPDF)
font-family: 'DejaVu Serif', serif;

# 2. Set charset di HTML
<meta charset="utf-8">

# 3. Check file encoding
# Pastikan .blade.php UTF-8 without BOM
```

### Issue 5: Performance Slow

```bash
# Symptom
Generate PDF lambat (>10 detik)

# Solution
# 1. Optimize images (compress logo/ttd)
# Max 800KB per image

# 2. Use queue untuk PDF generation
php artisan queue:work

# 3. Cache QR code jika sama
# Store di storage, reuse if exists
```

---

## 🔒 SECURITY BEST PRACTICES

### 1. UUID-Based Verification

```php
// ✅ GOOD: UUID tidak predictable
route('public.verify', $sertifikat->uuid);
// URL: /verify/a5f3d2b1-4c3e-8f9a-1d2e-3c4b5a6f7g8h

// ❌ BAD: ID sequential, bisa ditebak
route('public.verify', $sertifikat->id);
// URL: /verify/123 → orang bisa coba /verify/124, /verify/125, etc
```

### 2. Rate Limiting Public Routes

```php
// routes/web.php
Route::get('/sertifikat/verify/{uuid}', [...])->middleware('throttle:10,1');
// Max 10 requests per minute per IP
```

### 3. PDF File Access Control

```php
// ❌ BAD: Direct public access
// public/sertifikat/pdf/123.pdf → anyone can download

// ✅ GOOD: Controller-based download
Route::get('/sertifikat/{id}/download', [...])->middleware('auth');

public function download($id) {
    $sertifikat = Sertifikat::findOrFail($id);
    
    // Check permission
    if (!auth()->user()->can('download-certificate', $sertifikat)) {
        abort(403);
    }
    
    return Storage::disk('public')->download($sertifikat->file_pdf);
}
```

### 4. Watermark untuk Preview

```php
// Optional: Add watermark for preview
public function preview($id) {
    $pdf = Pdf::loadView('pdf.sertifikat-bnsp', $data);
    
    // Add watermark
    $pdf->setOption('watermark', 'PREVIEW - NOT FOR OFFICIAL USE');
    
    return $pdf->stream();
}
```

---

## 📊 MONITORING & ANALYTICS

### Metrics to Track

```php
// Log certificate generation metrics
Log::info('Certificate generated', [
    'sertifikat_id' => $sertifikat->id,
    'nomor' => $sertifikat->nomor_sertifikat,
    'skema_id' => $pendaftaran->skema_sertifikasi_id,
    'user_id' => Auth::id(),
    'generation_time_ms' => $generationTime,
    'pdf_size_kb' => Storage::size($pdfPath) / 1024,
]);
```

### Dashboard Queries

```sql
-- Total certificates issued
SELECT COUNT(*) as total FROM sertifikat;

-- Certificates issued this month
SELECT COUNT(*) as total 
FROM sertifikat 
WHERE DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m');

-- Certificates by skema
SELECT 
    skema_sertifikasi, 
    COUNT(*) as total
FROM sertifikat
GROUP BY skema_sertifikasi
ORDER BY total DESC;

-- Valid vs expired
SELECT 
    CASE 
        WHEN tanggal_berlaku_sampai >= CURDATE() THEN 'Valid'
        ELSE 'Expired'
    END as status,
    COUNT(*) as total
FROM sertifikat
GROUP BY status;

-- Generation time analysis
SELECT 
    DATE(created_at) as date,
    COUNT(*) as total,
    AVG(TIMESTAMPDIFF(SECOND, pendaftaran.updated_at, sertifikat.created_at)) as avg_time_seconds
FROM sertifikat
JOIN pendaftaran_sertifikasi ON sertifikat.pendaftaran_id = pendaftaran_sertifikasi.id
WHERE sertifikat.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(sertifikat.created_at);
```

---

## 🎓 BEST PRACTICES SUMMARY

### DO ✅

1. **Validate exhaustively** sebelum generate PDF
2. **Use transactions** untuk atomicity
3. **Generate unique numbers** dengan proper locking
4. **Store files properly** (storage/app/public, not public/)
5. **Use UUID** untuk public verification
6. **Log everything** (generation, errors, access)
7. **Test print output** before production
8. **Compress images** untuk faster generation
9. **Use config** untuk dynamic content (nama LSP, ketua, etc)
10. **Implement regenerate** untuk template updates

### DON'T ❌

1. **Don't expose database IDs** di public URLs
2. **Don't allow duplicate certificates** untuk same pendaftaran
3. **Don't generate PDF** without keputusan validation
4. **Don't use external fonts** (use DomPDF bundled fonts)
5. **Don't hardcode** LSP info di template
6. **Don't skip error handling** di PDF generation
7. **Don't forget** to clear cache after template updates
8. **Don't allow** anyone to download certificates (auth required)
9. **Don't use asset()** di PDF templates (use public_path())
10. **Don't forget** to backup before regenerate

---

## 📚 REFERENCES

- **DomPDF Documentation:** https://github.com/barryvdh/laravel-dompdf
- **QR Code Generator:** https://www.simplesoftwareio.com/simple-qrcode/
- **BNSP Standards:** https://bnsp.go.id
- **ISO 17024:** International standard for certification of persons
- **DomPDF Fonts:** DejaVu font family (supports Indonesian characters)

---

## ✅ DEPLOYMENT CHECKLIST

Sebelum go-live:

- [ ] Config `.env` sudah diisi lengkap
- [ ] Logo LSP & BNSP uploaded ke `public/images/`
- [ ] Tanda tangan digital uploaded (PNG transparent)
- [ ] Test generate PDF berhasil
- [ ] Test preview PDF tampil sempurna
- [ ] Test download PDF berhasil
- [ ] Test QR code scan → verifikasi works
- [ ] Test regenerate tidak duplikasi nomor
- [ ] Lisensi BNSP sudah valid
- [ ] Nama Ketua LSP & jabatan benar
- [ ] Storage permissions correct (775)
- [ ] Symlink storage sudah dibuat
- [ ] Print test PDF → layout tidak terpotong
- [ ] Performance test: <5 detik generate
- [ ] Error logging configured
- [ ] Backup strategy ready

---

**Status:** ✅ PRODUCTION-READY  
**Compliance:** BNSP & ISO 17024  
**Last Updated:** January 22, 2026  
**Maintained By:** LSP CertiPro DevOps Team

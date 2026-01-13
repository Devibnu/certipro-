<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Audit Evidence - {{ $metadata['document_id'] }}</title>
    <style>
        /* ================================================================
           AUDIT EVIDENCE DOCUMENT
           Template PDF A4 Portrait
           Untuk Keperluan Audit BNSP
           ================================================================ */
        
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 20mm 15mm;
        }
        
        @page :first {
            margin: 0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            color: #1a1a1a;
            line-height: 1.5;
            background: #ffffff;
        }
        
        /* ================================================================
           COVER PAGE
           ================================================================ */
        
        .cover-page {
            width: 210mm;
            height: 297mm;
            position: relative;
            background: linear-gradient(180deg, #1a365d 0%, #2c5282 100%);
            page-break-after: always;
        }
        
        .cover-border {
            position: absolute;
            top: 10mm;
            left: 10mm;
            right: 10mm;
            bottom: 10mm;
            border: 2px solid rgba(255,255,255,0.3);
        }
        
        .cover-content {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            padding: 30mm 25mm;
            text-align: center;
            color: #ffffff;
        }
        
        .cover-logo-section {
            margin-bottom: 15mm;
        }
        
        .cover-logo {
            width: 80px;
            height: auto;
            margin-bottom: 5mm;
        }
        
        .cover-badge {
            display: inline-block;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            padding: 3mm 8mm;
            border-radius: 3mm;
            font-size: 8pt;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 5mm;
        }
        
        .cover-title-section {
            margin: 30mm 0;
        }
        
        .cover-title {
            font-size: 24pt;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 5mm;
            text-transform: uppercase;
        }
        
        .cover-subtitle {
            font-size: 14pt;
            font-weight: normal;
            opacity: 0.9;
            margin-bottom: 3mm;
        }
        
        .cover-divider {
            width: 60mm;
            height: 1px;
            background: rgba(255,255,255,0.5);
            margin: 10mm auto;
        }
        
        .cover-lsp-name {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 2mm;
        }
        
        .cover-license {
            font-size: 10pt;
            opacity: 0.8;
        }
        
        .cover-info-section {
            position: absolute;
            bottom: 40mm;
            left: 25mm;
            right: 25mm;
        }
        
        .cover-info-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .cover-info-table td {
            padding: 2mm 5mm;
            text-align: left;
            font-size: 9pt;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        
        .cover-info-table td:first-child {
            width: 40%;
            opacity: 0.7;
        }
        
        .cover-qr-section {
            position: absolute;
            bottom: 15mm;
            right: 20mm;
            text-align: center;
        }
        
        .cover-qr-label {
            font-size: 7pt;
            opacity: 0.6;
            margin-top: 2mm;
        }
        
        .cover-footer {
            position: absolute;
            bottom: 15mm;
            left: 25mm;
            font-size: 7pt;
            opacity: 0.5;
        }
        
        /* ================================================================
           CONTENT PAGES
           ================================================================ */
        
        .content-page {
            page-break-before: always;
        }
        
        .content-page:first-of-type {
            page-break-before: auto;
        }
        
        /* Header for content pages */
        .page-header {
            border-bottom: 2px solid #1a365d;
            padding-bottom: 3mm;
            margin-bottom: 8mm;
        }
        
        .page-header-table {
            width: 100%;
        }
        
        .page-header-left {
            font-size: 12pt;
            font-weight: bold;
            color: #1a365d;
        }
        
        .page-header-right {
            text-align: right;
            font-size: 8pt;
            color: #666666;
        }
        
        /* Section Styling */
        .section {
            margin-bottom: 8mm;
        }
        
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 4mm;
            padding-bottom: 2mm;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .section-number {
            display: inline-block;
            background: #1a365d;
            color: #ffffff;
            width: 20px;
            height: 20px;
            text-align: center;
            line-height: 20px;
            border-radius: 3px;
            margin-right: 3mm;
            font-size: 9pt;
        }
        
        .subsection-title {
            font-size: 10pt;
            font-weight: bold;
            color: #2c5282;
            margin: 5mm 0 3mm 0;
        }
        
        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 3mm 0;
            font-size: 9pt;
        }
        
        .data-table th {
            background: #f7fafc;
            color: #1a365d;
            font-weight: bold;
            text-align: left;
            padding: 2.5mm 3mm;
            border: 0.5px solid #e2e8f0;
            font-size: 8pt;
            text-transform: uppercase;
        }
        
        .data-table td {
            padding: 2mm 3mm;
            border: 0.5px solid #e2e8f0;
            vertical-align: top;
        }
        
        .data-table tr:nth-child(even) {
            background: #f7fafc;
        }
        
        /* Info Box */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 3mm 0;
        }
        
        .info-table td {
            padding: 2mm 0;
            font-size: 9pt;
            vertical-align: top;
        }
        
        .info-table .label {
            width: 40%;
            color: #666666;
        }
        
        .info-table .value {
            font-weight: 500;
        }
        
        /* Stats Grid */
        .stats-grid {
            width: 100%;
            border-collapse: collapse;
            margin: 4mm 0;
        }
        
        .stat-box {
            border: 1px solid #e2e8f0;
            padding: 4mm;
            text-align: center;
            background: #f7fafc;
            width: 25%;
        }
        
        .stat-value {
            font-size: 18pt;
            font-weight: bold;
            color: #1a365d;
        }
        
        .stat-label {
            font-size: 7pt;
            color: #666666;
            text-transform: uppercase;
            margin-top: 1mm;
        }
        
        /* Badges */
        .badge {
            display: inline-block;
            padding: 1mm 3mm;
            border-radius: 2mm;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .badge-create { background: #c6f6d5; color: #276749; }
        .badge-update { background: #bee3f8; color: #2b6cb0; }
        .badge-delete { background: #fed7d7; color: #c53030; }
        .badge-login { background: #e9d8fd; color: #6b46c1; }
        .badge-logout { background: #e2e8f0; color: #4a5568; }
        .badge-approve { background: #c6f6d5; color: #276749; }
        .badge-reject { background: #fed7d7; color: #c53030; }
        .badge-verify { background: #bee3f8; color: #2b6cb0; }
        .badge-issue { background: #b2f5ea; color: #234e52; }
        .badge-revoke { background: #feebc8; color: #c05621; }
        .badge-decide { background: #feebc8; color: #c05621; }
        
        /* Flow Diagram */
        .flow-container {
            margin: 5mm 0;
        }
        
        .flow-step {
            display: inline-block;
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            padding: 3mm 5mm;
            margin: 1mm;
            border-radius: 2mm;
            font-size: 8pt;
        }
        
        .flow-step-number {
            display: inline-block;
            background: #1a365d;
            color: #ffffff;
            width: 16px;
            height: 16px;
            text-align: center;
            line-height: 16px;
            border-radius: 50%;
            margin-right: 2mm;
            font-size: 7pt;
        }
        
        .flow-arrow {
            display: inline-block;
            color: #a0aec0;
            font-size: 12pt;
            margin: 0 2mm;
        }
        
        /* Security Features */
        .security-box {
            background: #f0fff4;
            border: 1px solid #9ae6b4;
            border-radius: 3mm;
            padding: 4mm;
            margin: 3mm 0;
        }
        
        .security-icon {
            display: inline-block;
            width: 20px;
            text-align: center;
            color: #38a169;
        }
        
        /* Signature Section */
        .signature-section {
            margin-top: 15mm;
            page-break-inside: avoid;
        }
        
        .signature-box {
            width: 60%;
            margin-left: auto;
            text-align: center;
            padding: 5mm;
            border: 1px solid #e2e8f0;
        }
        
        .signature-title {
            font-size: 9pt;
            color: #666666;
            margin-bottom: 20mm;
        }
        
        .signature-line {
            border-bottom: 1px solid #1a1a1a;
            width: 80%;
            margin: 0 auto 2mm auto;
        }
        
        .signature-name {
            font-weight: bold;
            font-size: 10pt;
        }
        
        .signature-position {
            font-size: 8pt;
            color: #666666;
        }
        
        /* Footer */
        .page-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 15mm;
            border-top: 1px solid #e2e8f0;
            padding-top: 2mm;
            font-size: 7pt;
            color: #666666;
        }
        
        .footer-left {
            float: left;
        }
        
        .footer-center {
            text-align: center;
        }
        
        .footer-right {
            float: right;
        }
        
        /* Watermark */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60pt;
            color: rgba(0,0,0,0.03);
            font-weight: bold;
            white-space: nowrap;
            z-index: -1;
        }
        
        /* Checksum */
        .checksum-box {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            padding: 3mm;
            margin-top: 5mm;
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 7pt;
            word-break: break-all;
        }
        
        /* Alert Box */
        .alert-box {
            padding: 3mm 4mm;
            border-radius: 2mm;
            margin: 3mm 0;
            font-size: 8pt;
        }
        
        .alert-info {
            background: #ebf8ff;
            border-left: 3px solid #3182ce;
            color: #2b6cb0;
        }
        
        .alert-warning {
            background: #fffaf0;
            border-left: 3px solid #dd6b20;
            color: #c05621;
        }
        
        .alert-success {
            background: #f0fff4;
            border-left: 3px solid #38a169;
            color: #276749;
        }
        
        /* Page Break */
        .page-break {
            page-break-before: always;
        }
        
        /* Text utilities */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-small { font-size: 8pt; }
        .text-muted { color: #666666; }
        .text-bold { font-weight: bold; }
        .mt-2 { margin-top: 2mm; }
        .mt-4 { margin-top: 4mm; }
        .mb-2 { margin-bottom: 2mm; }
        .mb-4 { margin-bottom: 4mm; }
    </style>
</head>
<body>
    <!-- Watermark (akan muncul di semua halaman konten) -->
    <div class="watermark">AUDIT EVIDENCE</div>
    
    <!-- ================================================================
         COVER PAGE
         ================================================================ -->
    @php
        $logoBase64 = systemLogoBase64();
        $logoInfo = systemLogoInfo();
    @endphp
    <div class="cover-page">
        <div class="cover-border"></div>
        <div class="cover-content">
            <!-- Logo Section -->
            <div class="cover-logo-section">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" class="cover-logo" alt="Logo LSP">
                @else
                    <div style="font-size: 36pt; font-weight: bold;">LSP</div>
                @endif
                <div class="cover-badge">Dokumen Resmi</div>
            </div>
            
            <!-- Title Section -->
            <div class="cover-title-section">
                <div class="cover-title">AUDIT EVIDENCE</div>
                <div class="cover-subtitle">Sistem Informasi Sertifikasi</div>
                
                <div class="cover-divider"></div>
                
                <div class="cover-lsp-name">{{ $logoInfo['nama_perusahaan'] ?? $systemInfo['nama_lsp'] }}</div>
                <div class="cover-license">{{ $systemInfo['nomor_lisensi'] }}</div>
            </div>
            
            <!-- Info Section -->
            <div class="cover-info-section">
                <table class="cover-info-table">
                    <tr>
                        <td>Nama Sistem</td>
                        <td><strong>{{ $systemInfo['nama_sistem'] }}</strong></td>
                    </tr>
                    <tr>
                        <td>Versi Aplikasi</td>
                        <td>{{ $systemInfo['versi'] }}</td>
                    </tr>
                    <tr>
                        <td>URL Sistem</td>
                        <td>{{ $systemInfo['url'] }}</td>
                    </tr>
                    <tr>
                        <td>Tanggal Cetak</td>
                        <td>{{ $metadata['generated_at']->format('d F Y, H:i') }} WIB</td>
                    </tr>
                    <tr>
                        <td>Document ID</td>
                        <td style="font-family: monospace;">{{ $metadata['document_id'] }}</td>
                    </tr>
                </table>
            </div>
            
            <!-- Footer -->
            <div class="cover-footer">
                Dokumen ini digenerate secara otomatis oleh sistem.<br>
                Bersifat READ-ONLY dan tidak dapat diubah untuk keperluan audit BNSP.
            </div>
        </div>
    </div>
    
    <!-- ================================================================
         SECTION 1: IDENTITAS SISTEM
         ================================================================ -->
    <div class="content-page">
        <div class="page-header">
            <table class="page-header-table">
                <tr>
                    <td class="page-header-left">AUDIT EVIDENCE - {{ $systemInfo['nama_lsp'] }}</td>
                    <td class="page-header-right">{{ $metadata['document_id'] }}</td>
                </tr>
            </table>
        </div>
        
        <div class="section">
            <div class="section-title">
                <span class="section-number">1</span>
                IDENTITAS SISTEM
            </div>
            
            <table class="info-table">
                <tr>
                    <td class="label">Nama Sistem</td>
                    <td class="value">{{ $systemInfo['nama_sistem'] }}</td>
                </tr>
                <tr>
                    <td class="label">Versi Aplikasi</td>
                    <td class="value">{{ $systemInfo['versi'] }}</td>
                </tr>
                <tr>
                    <td class="label">URL Sistem</td>
                    <td class="value">{{ $systemInfo['url'] }}</td>
                </tr>
                <tr>
                    <td class="label">Penyelenggara</td>
                    <td class="value">{{ $systemInfo['nama_lsp'] }}</td>
                </tr>
                <tr>
                    <td class="label">Nomor Lisensi BNSP</td>
                    <td class="value">{{ $systemInfo['nomor_lisensi'] }}</td>
                </tr>
                <tr>
                    <td class="label">Alamat</td>
                    <td class="value">{{ $systemInfo['alamat'] }}</td>
                </tr>
                <tr>
                    <td class="label">Telepon</td>
                    <td class="value">{{ $systemInfo['telepon'] }}</td>
                </tr>
                <tr>
                    <td class="label">Email</td>
                    <td class="value">{{ $systemInfo['email'] }}</td>
                </tr>
                <tr>
                    <td class="label">Website</td>
                    <td class="value">{{ $systemInfo['website'] }}</td>
                </tr>
                <tr>
                    <td class="label">Penanggung Jawab Sistem</td>
                    <td class="value">{{ $systemInfo['ketua_lsp'] }}</td>
                </tr>
            </table>
        </div>
        
        <div class="section">
            <div class="section-title">
                <span class="section-number">2</span>
                ARSITEKTUR & KEAMANAN SISTEM
            </div>
            
            <div class="subsection-title">2.1 Role & Segregation of Duties</div>
            <p class="text-small mb-4">Sistem menerapkan pemisahan tugas (segregation of duties) sesuai standar BNSP:</p>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 20%;">Role</th>
                        <th style="width: 15%;">Jumlah</th>
                        <th>Hak Akses</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Super Admin</strong></td>
                        <td>{{ $roleDistribution['super admin'] ?? 0 }}</td>
                        <td>Akses penuh ke seluruh modul sistem, manajemen pengguna, konfigurasi</td>
                    </tr>
                    <tr>
                        <td><strong>Admin</strong></td>
                        <td>{{ $roleDistribution['admin'] ?? 0 }}</td>
                        <td>Manajemen pendaftaran, verifikasi dokumen, monitoring proses</td>
                    </tr>
                    <tr>
                        <td><strong>Asesor</strong></td>
                        <td>{{ $roleDistribution['asesor'] ?? 0 }}</td>
                        <td>Melaksanakan asesmen kompetensi, input nilai per KUK, rekomendasi</td>
                    </tr>
                    <tr>
                        <td><strong>Komite Teknis</strong></td>
                        <td>{{ $roleDistribution['komite_teknis'] ?? 0 }}</td>
                        <td>Keputusan final sertifikasi (KOMPETEN/BELUM KOMPETEN)</td>
                    </tr>
                    <tr>
                        <td><strong>Asesi</strong></td>
                        <td>{{ $roleDistribution['asesi'] ?? 0 }}</td>
                        <td>Pendaftaran, upload dokumen, melihat status & hasil</td>
                    </tr>
                </tbody>
            </table>
            
            <div class="subsection-title">2.2 Fitur Keamanan Sistem</div>
            
            <div class="security-box">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 5%;"><span class="security-icon">✓</span></td>
                        <td><strong>Audit Log Aktif & Immutable</strong> - Semua aktivitas tercatat otomatis dan tidak dapat dihapus</td>
                    </tr>
                    <tr>
                        <td><span class="security-icon">✓</span></td>
                        <td><strong>Timestamp WIB (UTC+7)</strong> - Pencatatan waktu menggunakan zona waktu Indonesia Barat</td>
                    </tr>
                    <tr>
                        <td><span class="security-icon">✓</span></td>
                        <td><strong>UUID & Hash SHA-256</strong> - Setiap sertifikat memiliki identifier unik dan hash untuk validasi</td>
                    </tr>
                    <tr>
                        <td><span class="security-icon">✓</span></td>
                        <td><strong>QR Code Verifikasi</strong> - Sertifikat dapat diverifikasi publik melalui scan QR code</td>
                    </tr>
                    <tr>
                        <td><span class="security-icon">✓</span></td>
                        <td><strong>Lock Decision</strong> - Keputusan sertifikasi tidak dapat diubah setelah dikunci</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <!-- ================================================================
         SECTION 3: ALUR PROSES SERTIFIKASI
         ================================================================ -->
    <div class="page-break"></div>
    <div class="content-page">
        <div class="page-header">
            <table class="page-header-table">
                <tr>
                    <td class="page-header-left">AUDIT EVIDENCE - {{ $systemInfo['nama_lsp'] }}</td>
                    <td class="page-header-right">{{ $metadata['document_id'] }}</td>
                </tr>
            </table>
        </div>
        
        <div class="section">
            <div class="section-title">
                <span class="section-number">3</span>
                ALUR PROSES SERTIFIKASI
            </div>
            
            <p class="text-small mb-4">Sistem mengimplementasikan alur sertifikasi end-to-end sesuai standar BNSP:</p>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 8%;">No</th>
                        <th style="width: 25%;">Tahapan</th>
                        <th style="width: 20%;">Pelaku</th>
                        <th>Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center"><span class="flow-step-number">1</span></td>
                        <td><strong>Pra-Pendaftaran</strong></td>
                        <td>Asesi</td>
                        <td>Asesi mengisi data diri awal dan memilih skema sertifikasi yang diminati</td>
                    </tr>
                    <tr>
                        <td class="text-center"><span class="flow-step-number">2</span></td>
                        <td><strong>Verifikasi Admin</strong></td>
                        <td>Admin LSP</td>
                        <td>Admin memverifikasi kelengkapan dan keabsahan dokumen pra-pendaftaran</td>
                    </tr>
                    <tr>
                        <td class="text-center"><span class="flow-step-number">3</span></td>
                        <td><strong>Pendaftaran Sertifikasi</strong></td>
                        <td>Admin LSP</td>
                        <td>Pembuatan nomor pendaftaran resmi dan penugasan asesor</td>
                    </tr>
                    <tr>
                        <td class="text-center"><span class="flow-step-number">4</span></td>
                        <td><strong>Asesmen Kompetensi</strong></td>
                        <td>Asesor</td>
                        <td>Pelaksanaan asesmen per Unit Kompetensi dan Kriteria Unjuk Kerja (KUK)</td>
                    </tr>
                    <tr>
                        <td class="text-center"><span class="flow-step-number">5</span></td>
                        <td><strong>Keputusan Sertifikasi</strong></td>
                        <td>Komite Teknis</td>
                        <td>Penetapan status KOMPETEN atau BELUM KOMPETEN berdasarkan hasil asesmen</td>
                    </tr>
                    <tr>
                        <td class="text-center"><span class="flow-step-number">6</span></td>
                        <td><strong>Penerbitan Sertifikat</strong></td>
                        <td>Sistem</td>
                        <td>Generate sertifikat digital dengan nomor unik, QR code, dan hash keamanan</td>
                    </tr>
                </tbody>
            </table>
            
            <div class="subsection-title">3.1 Statistik Proses Sertifikasi</div>
            
            <table class="stats-grid">
                <tr>
                    <td class="stat-box">
                        <div class="stat-value">{{ number_format($certificationStats['pendaftaran_diajukan']) }}</div>
                        <div class="stat-label">Diajukan</div>
                    </td>
                    <td class="stat-box">
                        <div class="stat-value">{{ number_format($certificationStats['pendaftaran_diverifikasi']) }}</div>
                        <div class="stat-label">Diverifikasi</div>
                    </td>
                    <td class="stat-box">
                        <div class="stat-value">{{ number_format($certificationStats['pendaftaran_asesmen']) }}</div>
                        <div class="stat-label">Asesmen</div>
                    </td>
                    <td class="stat-box">
                        <div class="stat-value">{{ number_format($certificationStats['pendaftaran_kompeten']) }}</div>
                        <div class="stat-label">Kompeten</div>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="section">
            <div class="section-title">
                <span class="section-number">4</span>
                STATISTIK SISTEM
            </div>
            
            <table class="stats-grid">
                <tr>
                    <td class="stat-box">
                        <div class="stat-value">{{ number_format($statistics['total_users']) }}</div>
                        <div class="stat-label">Total Pengguna</div>
                    </td>
                    <td class="stat-box">
                        <div class="stat-value">{{ number_format($statistics['total_skema']) }}</div>
                        <div class="stat-label">Skema Sertifikasi</div>
                    </td>
                    <td class="stat-box">
                        <div class="stat-value">{{ number_format($statistics['total_pendaftaran']) }}</div>
                        <div class="stat-label">Total Pendaftaran</div>
                    </td>
                    <td class="stat-box">
                        <div class="stat-value">{{ number_format($statistics['total_sertifikat']) }}</div>
                        <div class="stat-label">Sertifikat Terbit</div>
                    </td>
                </tr>
            </table>
            
            <table class="stats-grid" style="margin-top: 3mm;">
                <tr>
                    <td class="stat-box">
                        <div class="stat-value">{{ number_format($statistics['sertifikat_aktif']) }}</div>
                        <div class="stat-label">Sertifikat Aktif</div>
                    </td>
                    <td class="stat-box">
                        <div class="stat-value">{{ number_format($statistics['total_audit_log']) }}</div>
                        <div class="stat-label">Total Audit Log</div>
                    </td>
                    <td class="stat-box">
                        <div class="stat-value">{{ number_format($activeUsers) }}</div>
                        <div class="stat-label">User Aktif (7 Hari)</div>
                    </td>
                    <td class="stat-box">
                        <div class="stat-value">{{ number_format($criticalEvents) }}</div>
                        <div class="stat-label">Event Kritis</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    
    <!-- ================================================================
         SECTION 5: BUKTI AUDIT LOG
         ================================================================ -->
    <div class="page-break"></div>
    <div class="content-page">
        <div class="page-header">
            <table class="page-header-table">
                <tr>
                    <td class="page-header-left">AUDIT EVIDENCE - {{ $systemInfo['nama_lsp'] }}</td>
                    <td class="page-header-right">{{ $metadata['document_id'] }}</td>
                </tr>
            </table>
        </div>
        
        <div class="section">
            <div class="section-title">
                <span class="section-number">5</span>
                BUKTI AUDIT LOG SISTEM
            </div>
            
            <div class="alert-box alert-info mb-4">
                <strong>Informasi:</strong> Audit log bersifat READ-ONLY dan tidak dapat dihapus atau dimodifikasi. 
                Data berikut adalah sampel 20 log terbaru dari total {{ number_format($statistics['total_audit_log']) }} log tercatat.
            </div>
            
            <div class="subsection-title">5.1 Distribusi Event</div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Jenis Event</th>
                        <th style="width: 20%;">Jumlah</th>
                        <th style="width: 25%;">Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalEvents = array_sum($eventDistribution); @endphp
                    @foreach($eventDistribution as $event => $count)
                    <tr>
                        <td>
                            <span class="badge badge-{{ $event }}">{{ strtoupper($event) }}</span>
                        </td>
                        <td>{{ number_format($count) }}</td>
                        <td>{{ $totalEvents > 0 ? number_format(($count / $totalEvents) * 100, 1) : 0 }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="subsection-title">5.2 Distribusi Modul</div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Modul</th>
                        <th style="width: 20%;">Jumlah Log</th>
                        <th style="width: 25%;">Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalModules = array_sum($moduleDistribution); @endphp
                    @foreach($moduleDistribution as $module => $count)
                    <tr>
                        <td>{{ ucfirst(str_replace('_', ' ', $module)) }}</td>
                        <td>{{ number_format($count) }}</td>
                        <td>{{ $totalModules > 0 ? number_format(($count / $totalModules) * 100, 1) : 0 }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- ================================================================
         SECTION 5 CONTINUED: SAMPLE AUDIT LOGS
         ================================================================ -->
    <div class="page-break"></div>
    <div class="content-page">
        <div class="page-header">
            <table class="page-header-table">
                <tr>
                    <td class="page-header-left">AUDIT EVIDENCE - {{ $systemInfo['nama_lsp'] }}</td>
                    <td class="page-header-right">{{ $metadata['document_id'] }}</td>
                </tr>
            </table>
        </div>
        
        <div class="section">
            <div class="subsection-title">5.3 Sampel Audit Log Terbaru</div>
            
            <table class="data-table" style="font-size: 7pt;">
                <thead>
                    <tr>
                        <th style="width: 15%;">Waktu</th>
                        <th style="width: 15%;">User</th>
                        <th style="width: 10%;">Role</th>
                        <th style="width: 12%;">Modul</th>
                        <th style="width: 10%;">Event</th>
                        <th>Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLogs as $log)
                    <tr>
                        <td style="font-size: 6pt;">
                            {{ $log->created_at->format('d/m/Y') }}<br>
                            <span class="text-muted">{{ $log->created_at->format('H:i:s') }}</span>
                        </td>
                        <td>{{ Str::limit($log->user_name ?? 'System', 15) }}</td>
                        <td>{{ ucfirst($log->user_role ?? 'system') }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $log->module)) }}</td>
                        <td>
                            <span class="badge badge-{{ $log->action }}">{{ strtoupper($log->action) }}</span>
                        </td>
                        <td style="font-size: 6pt;">{{ Str::limit($log->description, 40) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Belum ada log tercatat</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            
            <div class="alert-box alert-warning mt-4">
                <strong>Catatan:</strong> Untuk log lengkap, gunakan fitur Export CSV pada halaman Audit Log sistem.
            </div>
        </div>
    </div>
    
    <!-- ================================================================
         SECTION 6: BUKTI KEAMANAN SERTIFIKAT
         ================================================================ -->
    <div class="page-break"></div>
    <div class="content-page">
        <div class="page-header">
            <table class="page-header-table">
                <tr>
                    <td class="page-header-left">AUDIT EVIDENCE - {{ $systemInfo['nama_lsp'] }}</td>
                    <td class="page-header-right">{{ $metadata['document_id'] }}</td>
                </tr>
            </table>
        </div>
        
        <div class="section">
            <div class="section-title">
                <span class="section-number">6</span>
                BUKTI KEAMANAN SERTIFIKAT
            </div>
            
            <p class="text-small mb-4">Setiap sertifikat yang diterbitkan oleh sistem memiliki fitur keamanan berikut:</p>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Fitur Keamanan</th>
                        <th>Deskripsi</th>
                        <th style="width: 15%;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>UUID Unik</strong></td>
                        <td>Setiap sertifikat memiliki UUID (Universally Unique Identifier) yang tidak dapat diduplikasi</td>
                        <td class="text-center"><span class="badge badge-approve">AKTIF</span></td>
                    </tr>
                    <tr>
                        <td><strong>QR Code Verifikasi</strong></td>
                        <td>QR code yang dapat dipindai untuk verifikasi keaslian sertifikat secara publik</td>
                        <td class="text-center"><span class="badge badge-approve">AKTIF</span></td>
                    </tr>
                    <tr>
                        <td><strong>Hash SHA-256</strong></td>
                        <td>Setiap sertifikat memiliki hash kriptografis untuk deteksi pemalsuan</td>
                        <td class="text-center"><span class="badge badge-approve">AKTIF</span></td>
                    </tr>
                    <tr>
                        <td><strong>Watermark LSP</strong></td>
                        <td>Watermark tersembunyi pada dokumen PDF untuk identifikasi</td>
                        <td class="text-center"><span class="badge badge-approve">AKTIF</span></td>
                    </tr>
                    <tr>
                        <td><strong>Read-Only Status</strong></td>
                        <td>Sertifikat tidak dapat dimodifikasi setelah diterbitkan</td>
                        <td class="text-center"><span class="badge badge-approve">AKTIF</span></td>
                    </tr>
                    <tr>
                        <td><strong>Audit Trail</strong></td>
                        <td>Setiap akses dan verifikasi sertifikat tercatat dalam audit log</td>
                        <td class="text-center"><span class="badge badge-approve">AKTIF</span></td>
                    </tr>
                </tbody>
            </table>
            
            @if($sampleCertificates->count() > 0)
            <div class="subsection-title">6.1 Sampel Sertifikat Terbaru</div>
            
            <table class="data-table" style="font-size: 8pt;">
                <thead>
                    <tr>
                        <th>Nomor Sertifikat</th>
                        <th>Pemegang</th>
                        <th>Skema</th>
                        <th style="width: 12%;">Status</th>
                        <th style="width: 15%;">Berlaku s/d</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sampleCertificates as $cert)
                    <tr>
                        <td style="font-family: monospace;">{{ $cert->nomor_sertifikat }}</td>
                        <td>{{ $cert->pendaftaranSertifikasi->user->name ?? '-' }}</td>
                        <td>{{ $cert->pendaftaranSertifikasi->skemaSertifikasi->nama_skema ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge badge-{{ $cert->status == 'aktif' ? 'approve' : 'reject' }}">
                                {{ strtoupper($cert->status) }}
                            </span>
                        </td>
                        <td>{{ $cert->tanggal_expired ? $cert->tanggal_expired->format('d M Y') : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
    
    <!-- ================================================================
         SECTION 7: PENGESAHAN
         ================================================================ -->
    <div class="page-break"></div>
    <div class="content-page">
        <div class="page-header">
            <table class="page-header-table">
                <tr>
                    <td class="page-header-left">AUDIT EVIDENCE - {{ $systemInfo['nama_lsp'] }}</td>
                    <td class="page-header-right">{{ $metadata['document_id'] }}</td>
                </tr>
            </table>
        </div>
        
        <div class="section">
            <div class="section-title">
                <span class="section-number">7</span>
                PENGESAHAN
            </div>
            
            <p class="mb-4">
                Dokumen Audit Evidence ini digenerate secara otomatis oleh sistem dan merupakan bukti resmi 
                yang dapat digunakan untuk keperluan audit BNSP. Seluruh data yang tercantum dalam dokumen ini 
                bersumber langsung dari database sistem dan bersifat akurat pada saat pencetakan.
            </p>
            
            <div class="alert-box alert-success mb-4">
                <strong>Pernyataan Keaslian:</strong><br>
                Dengan ini menyatakan bahwa sistem informasi sertifikasi ini telah mengimplementasikan 
                fitur audit trail, keamanan data, dan alur sertifikasi sesuai dengan standar BNSP.
            </div>
            
            <table class="info-table">
                <tr>
                    <td class="label">Tanggal Pengesahan</td>
                    <td class="value">{{ $metadata['generated_at']->format('d F Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Waktu Generate</td>
                    <td class="value">{{ $metadata['generated_at']->format('H:i:s') }} WIB</td>
                </tr>
                <tr>
                    <td class="label">Digenerate Oleh</td>
                    <td class="value">{{ $metadata['generated_by'] }}</td>
                </tr>
            </table>
            
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-title">{{ $systemInfo['kota_terbit'] }}, {{ $metadata['generated_at']->format('d F Y') }}</div>
                    <div class="signature-line"></div>
                    <div class="signature-name">{{ $systemInfo['ketua_lsp'] }}</div>
                    <div class="signature-position">Ketua LSP</div>
                </div>
            </div>
            
            <div class="checksum-box mt-4">
                <strong>Document Hash (SHA-256):</strong><br>
                {{ $metadata['document_hash'] }}
            </div>
            
            <div class="alert-box alert-info mt-4">
                <strong>Informasi Verifikasi:</strong><br>
                Keaslian dokumen ini dapat diverifikasi dengan membandingkan hash di atas dengan hash yang 
                tersimpan dalam sistem. Untuk informasi lebih lanjut, hubungi {{ $systemInfo['email'] }}.
            </div>
        </div>
        
        <div class="section" style="margin-top: 10mm;">
            <p class="text-center text-small text-muted">
                — Akhir Dokumen —
            </p>
        </div>
    </div>
    
    <!-- Footer (akan muncul di semua halaman) -->
    <div class="page-footer">
        <table style="width: 100%;">
            <tr>
                <td class="footer-left" style="width: 33%;">
                    {{ $systemInfo['nama_lsp'] }}<br>
                    {{ $systemInfo['nomor_lisensi'] }}
                </td>
                <td class="footer-center" style="width: 34%;">
                    Dokumen ini digenerate otomatis oleh sistem<br>
                    Bersifat READ-ONLY untuk keperluan audit BNSP
                </td>
                <td class="footer-right" style="width: 33%; text-align: right;">
                    {{ $metadata['document_id'] }}<br>
                    {{ $metadata['generated_at']->format('d/m/Y H:i') }} WIB
                </td>
            </tr>
        </table>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Sertifikat Kompetensi - {{ $sertifikat->nomor_sertifikat }}</title>
    <style>
        /* ================================================================
           SERTIFIKAT KOMPETENSI LSP - STANDAR BNSP
           CENTER-ALIGNED LAYOUT - A4 Print Optimized
           Version 4 - Professional Certificate
           ================================================================ */
        
        /* SAFE PAGE MARGINS for A4 Print */
        @page {
            size: A4 portrait;
            margin: 30mm 25mm 30mm 25mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Serif', 'Times New Roman', Georgia, serif;
            background: #ffffff;
            color: #1a1a1a;
            line-height: 1.25;
            text-align: center;
        }
        
        /* ================================================================
           MAIN CONTAINER - Centered & Symmetric
           ================================================================ */
        
        .certificate-wrapper {
            width: 100%;
            margin: 0 auto;
            text-align: center;
        }
        
        .certificate-container {
            width: 100%;
            padding: 10mm;
            border: 3px solid #1a365d;
            background: #ffffff;
            text-align: center;
        }
        
        .certificate-inner {
            border: 1px solid #b8860b;
            padding: 8mm;
            position: relative;
            text-align: center;
        }
        
        /* Watermark - BACKGROUND ONLY (Centered) */
        .watermark-bg {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 80mm;
            height: 80mm;
            margin-left: -40mm;
            margin-top: -40mm;
            opacity: 0.05;
            z-index: 0;
        }
        
        .watermark-logo {
            width: 100%;
            height: 100%;
        }
        
        /* Content above watermark - Centered */
        .certificate-content {
            position: relative;
            z-index: 1;
            text-align: center;
            width: 100%;
        }
        
        /* ================================================================
           HEADER - LOGO SECTION (Centered Table)
           ================================================================ */
        
        .header-logos {
            width: 100%;
            margin: 0 auto 4mm auto;
            text-align: center;
        }
        
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto;
        }
        
        .header-table td {
            vertical-align: middle;
            padding: 2mm;
        }
        
        .logo-left {
            width: 35%;
            text-align: left;
        }
        
        .logo-center {
            width: 30%;
            text-align: center;
        }
        
        .logo-right {
            width: 35%;
            text-align: right;
        }
        
        .logo-img {
            max-height: 16mm;
            max-width: 45mm;
            display: inline-block;
        }
        
        .lsp-info {
            margin-top: 1mm;
            text-align: left;
        }
        
        .lsp-name {
            font-size: 8pt;
            font-weight: bold;
            color: #1a365d;
        }
        
        .lsp-lisensi {
            font-size: 6pt;
            color: #666;
        }
        
        /* ================================================================
           TITLE SECTION (Centered)
           ================================================================ */
        
        .title-section {
            text-align: center;
            margin: 4mm auto;
            padding-bottom: 3mm;
            border-bottom: 1px solid #e0e0e0;
            width: 100%;
        }
        
        .title-main {
            font-size: 22pt;
            font-weight: bold;
            color: #1a365d;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 1mm;
        }
        
        .title-sub {
            font-size: 9pt;
            color: #666;
            font-style: italic;
            letter-spacing: 1px;
        }
        
        /* ================================================================
           BODY CONTENT (All Centered)
           ================================================================ */
        
        .body-content {
            text-align: center;
            width: 100%;
            margin: 0 auto;
        }
        
        .intro-text {
            font-size: 9pt;
            color: #444;
            margin: 3mm 0 2mm 0;
        }
        
        .recipient-name {
            font-size: 19pt;
            font-weight: bold;
            color: #1a365d;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 2mm 10mm;
            margin: 0 auto 4mm auto;
            border-bottom: 2px solid #b8860b;
            display: inline-block;
        }
        
        /* Info Section - Centered */
        .info-row {
            margin: 3mm auto;
            text-align: center;
        }
        
        .info-label {
            font-size: 8pt;
            color: #666;
            margin-bottom: 0.5mm;
        }
        
        .info-value {
            font-size: 10pt;
            font-weight: bold;
            color: #1a365d;
        }
        
        .info-value-large {
            font-size: 12pt;
            font-weight: bold;
            color: #1a365d;
            line-height: 1.3;
        }
        
        .info-code {
            font-size: 7pt;
            color: #888;
            font-family: 'DejaVu Sans Mono', 'Courier New', monospace;
        }
        
        /* Declaration Box - Centered */
        .declaration-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 3mm 5mm;
            margin: 4mm auto;
            display: inline-block;
            text-align: center;
        }
        
        .declaration-intro {
            font-size: 8pt;
            color: #555;
            margin-bottom: 1mm;
        }
        
        .declaration-status {
            font-size: 15pt;
            font-weight: bold;
            color: #1a365d;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        
        /* Validity Table - Centered */
        .validity-section {
            width: 70%;
            margin: 4mm auto;
            text-align: center;
        }
        
        .validity-table {
            width: 100%;
            border: 1px solid #e0e0e0;
            border-collapse: collapse;
            margin: 0 auto;
        }
        
        .validity-table td {
            width: 50%;
            padding: 3mm;
            text-align: center;
            background: #fafafa;
            border: 1px solid #e0e0e0;
        }
        
        .validity-label {
            font-size: 7pt;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 1mm;
        }
        
        .validity-value {
            font-size: 9pt;
            font-weight: bold;
            color: #1a365d;
            display: block;
        }
        
        /* ================================================================
           FOOTER SECTION - Centered Table
           ================================================================ */
        
        .footer-section {
            margin-top: 5mm;
            padding-top: 3mm;
            border-top: 1px solid #e0e0e0;
            width: 100%;
            text-align: center;
        }
        
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto;
        }
        
        .footer-table td {
            vertical-align: top;
            padding: 2mm;
        }
        
        .footer-qr {
            width: 28%;
            text-align: center;
        }
        
        .footer-signature {
            width: 44%;
            text-align: center;
        }
        
        .footer-info {
            width: 28%;
            text-align: center;
        }
        
        /* QR Code - Centered */
        .qr-wrapper {
            text-align: center;
            margin: 0 auto 2mm auto;
        }
        
        .qr-code-img {
            width: 22mm;
            height: 22mm;
            border: 1px solid #e0e0e0;
            display: inline-block;
        }
        
        .qr-text {
            font-size: 6pt;
            color: #888;
            line-height: 1.1;
            margin-top: 1mm;
        }
        
        /* Signature Block - Centered */
        .signature-wrapper {
            text-align: center;
            margin: 0 auto;
        }
        
        .signature-location {
            font-size: 8pt;
            color: #444;
            margin-bottom: 10mm;
        }
        
        .signature-line {
            width: 55mm;
            border-bottom: 1px solid #333;
            margin: 0 auto 1mm auto;
        }
        
        .signature-name {
            font-size: 10pt;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 0.5mm;
        }
        
        .signature-title {
            font-size: 7pt;
            color: #666;
        }
        
        /* Info Block - Centered */
        .info-wrapper {
            text-align: center;
            margin: 0 auto;
        }
        
        .secure-label {
            font-size: 6pt;
            color: #aaa;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1mm;
        }
        
        .secure-id {
            font-size: 5pt;
            color: #999;
            font-family: 'DejaVu Sans Mono', 'Courier New', monospace;
            word-break: break-all;
            line-height: 1.2;
        }
        
        .doc-id {
            font-size: 5pt;
            color: #bbb;
            margin-top: 1mm;
        }
        
        /* Bottom Secure Footer - Centered */
        .secure-footer {
            text-align: center;
            font-size: 5pt;
            color: #ccc;
            letter-spacing: 0.3px;
            font-family: 'DejaVu Sans Mono', 'Courier New', monospace;
            margin-top: 3mm;
            padding-top: 2mm;
            border-top: 1px solid #f0f0f0;
        }
        
        /* ================================================================
           UTILITY CLASSES
           ================================================================ */
        
        .text-center { text-align: center; }
    </style>
</head>
<body>
    @php
        $logoInfo = systemLogoInfo();
        $logoBase64 = systemLogoBase64();
        $nomorLisensi = config('certipro.nomor_lisensi');
        $isBnspLicensed = config('certipro.is_bnsp_licensed', false);
        $logoBnspPath = public_path('images/logo-bnsp.png');
        $ketuaLsp = config('certipro.ketua_lsp', 'Ketua LSP');
    @endphp
    
    <!-- Wrapper - Centered Layout -->
    <div class="certificate-wrapper">
        <div class="certificate-container">
            <div class="certificate-inner">
                
                <!-- Watermark - Background (Centered) -->
                @if($logoBase64)
                <div class="watermark-bg">
                    <img src="{{ $logoBase64 }}" class="watermark-logo" alt="">
                </div>
                @endif
                
                <!-- All Content - Centered Above Watermark -->
                <div class="certificate-content">
                
                    <!-- ============ HEADER - LOGOS ============ -->
                    <div class="header-logos">
                        <table class="header-table">
                            <tr>
                                <!-- Logo LSP (Left) -->
                                <td class="logo-left">
                                    @if($logoBase64)
                                        <img src="{{ $logoBase64 }}" class="logo-img" alt="Logo LSP">
                                    @endif
                                    <div class="lsp-info">
                                        @if($logoInfo['nama_perusahaan'])
                                            <div class="lsp-name">{{ $logoInfo['nama_perusahaan'] }}</div>
                                        @endif
                                        @if($nomorLisensi)
                                            <div class="lsp-lisensi">Lisensi: {{ $nomorLisensi }}</div>
                                        @endif
                                    </div>
                                </td>
                                
                                <!-- Center Space -->
                                <td class="logo-center">
                                    {{-- Reserved --}}
                                </td>
                                
                                <!-- Logo BNSP (Right) -->
                                <td class="logo-right">
                                    @if($isBnspLicensed && file_exists($logoBnspPath))
                                        <img src="{{ $logoBnspPath }}" class="logo-img" alt="Logo BNSP">
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- ============ TITLE ============ -->
                    <div class="title-section">
                        <h1 class="title-main">Sertifikat Kompetensi</h1>
                        <p class="title-sub">Certificate of Competency</p>
                    </div>
                    
                    <!-- ============ BODY CONTENT ============ -->
                    <div class="body-content">
                        
                        <!-- Recipient -->
                        <p class="intro-text">Diberikan kepada:</p>
                        <h2 class="recipient-name">{{ strtoupper($sertifikat->nama_peserta) }}</h2>
                        
                        <!-- Certificate Number -->
                        <div class="info-row">
                            <div class="info-label">Nomor Sertifikat:</div>
                            <div class="info-value">{{ $sertifikat->nomor_sertifikat }}</div>
                        </div>
                        
                        <!-- Scheme -->
                        <div class="info-row">
                            <div class="info-label">Skema Sertifikasi:</div>
                            <div class="info-value-large">{{ $sertifikat->skema_sertifikasi }}</div>
                            @if(isset($pendaftaran) && $pendaftaran->skemaSertifikasi)
                                <div class="info-code">({{ $pendaftaran->skemaSertifikasi->kode_skema ?? '' }})</div>
                            @endif
                        </div>
                        
                        <!-- Declaration - ISO 17024 Compliant -->
                        <div class="declaration-box">
                            <div class="declaration-intro">Menyatakan bahwa yang bersangkutan</div>
                            <div class="declaration-status">Telah Dinyatakan Kompeten</div>
                        </div>
                        
                        <!-- Validity Period -->
                        <div class="validity-section">
                            <table class="validity-table">
                                <tr>
                                    <td>
                                        <span class="validity-label">Tanggal Terbit</span>
                                        <span class="validity-value">{{ $sertifikat->tanggal_terbit->translatedFormat('d F Y') }}</span>
                                    </td>
                                    <td>
                                        <span class="validity-label">Berlaku Sampai</span>
                                        <span class="validity-value">{{ $sertifikat->tanggal_berlaku_sampai->translatedFormat('d F Y') }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                    </div>
                    
                    <!-- ============ FOOTER ============ -->
                    <div class="footer-section">
                        <table class="footer-table">
                            <tr>
                                <!-- QR Code -->
                                <td class="footer-qr">
                                    <div class="qr-wrapper">
                                        <img src="data:image/svg+xml;base64,{{ $qrCodeBase64 }}" class="qr-code-img" alt="QR">
                                        <div class="qr-text">Scan untuk<br>verifikasi</div>
                                    </div>
                                </td>
                                
                                <!-- Signature -->
                                <td class="footer-signature">
                                    <div class="signature-wrapper">
                                        <div class="signature-location">
                                            {{ config('certipro.kota_terbit', 'Jakarta') }}, {{ $sertifikat->tanggal_terbit->translatedFormat('d F Y') }}
                                        </div>
                                        <div class="signature-line"></div>
                                        <div class="signature-name">{{ $ketuaLsp }}</div>
                                        @if($logoInfo['nama_perusahaan'])
                                            <div class="signature-title">Ketua {{ $logoInfo['nama_perusahaan'] }}</div>
                                        @else
                                            <div class="signature-title">Ketua LSP</div>
                                        @endif
                                    </div>
                                </td>
                                
                                <!-- Secure Info -->
                                <td class="footer-info">
                                    <div class="info-wrapper">
                                        <div class="secure-label">Secure ID</div>
                                        <div class="secure-id">
                                            {{ substr($sertifikat->uuid, 0, 18) }}<br>
                                            {{ substr($sertifikat->uuid, 18) }}
                                        </div>
                                        <div class="doc-id">
                                            DOC-{{ $sertifikat->id }}-{{ $sertifikat->created_at->format('Ymd') }}
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                        
                        <!-- Bottom Secure Footer -->
                        <div class="secure-footer">
                            SECURE-ID: {{ $sertifikat->uuid }} | HASH: {{ substr($sertifikat->security_hash ?? '', 0, 12) }}
                        </div>
                    </div>
                
                </div><!-- .certificate-content -->
                
            </div><!-- .certificate-inner -->
        </div><!-- .certificate-container -->
    </div><!-- .certificate-wrapper -->
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Sertifikat Kompetensi - {{ $sertifikat->nomor_sertifikat }}</title>
    <style>
        /* ================================================================
           SERTIFIKAT KOMPETENSI LSP - STANDAR BNSP
           Template PDF A4 Portrait
           Production-Ready Template
           ================================================================ */
        
        @page {
            size: A4 portrait;
            margin: 0;
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
            line-height: 1.4;
        }
        
        /* ================================================================
           CONTAINER & BORDERS
           ================================================================ */
        
        .certificate-page {
            width: 210mm;
            height: 297mm;
            position: relative;
            background: #ffffff;
            overflow: hidden;
        }
        
        /* Border Utama */
        .border-outer {
            position: absolute;
            top: 8mm;
            left: 8mm;
            right: 8mm;
            bottom: 8mm;
            border: 4px solid #1a365d;
        }
        
        .border-inner {
            position: absolute;
            top: 12mm;
            left: 12mm;
            right: 12mm;
            bottom: 12mm;
            border: 1.5px solid #b8860b;
        }
        
        /* Dekorasi sudut */
        .corner-decoration {
            position: absolute;
            width: 20px;
            height: 20px;
        }
        
        .corner-tl { top: 10mm; left: 10mm; border-top: 3px solid #b8860b; border-left: 3px solid #b8860b; }
        .corner-tr { top: 10mm; right: 10mm; border-top: 3px solid #b8860b; border-right: 3px solid #b8860b; }
        .corner-bl { bottom: 10mm; left: 10mm; border-bottom: 3px solid #b8860b; border-left: 3px solid #b8860b; }
        .corner-br { bottom: 10mm; right: 10mm; border-bottom: 3px solid #b8860b; border-right: 3px solid #b8860b; }
        
        /* Content Area */
        .content-wrapper {
            position: absolute;
            top: 18mm;
            left: 18mm;
            right: 18mm;
            bottom: 18mm;
        }
        
        /* ================================================================
           HEADER - LOGO SECTION
           ================================================================ */
        
        .header-logos {
            display: table;
            width: 100%;
            margin-bottom: 5mm;
        }
        
        .logo-cell {
            display: table-cell;
            vertical-align: middle;
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
            max-height: 22mm;
            max-width: 55mm;
        }
        
        .logo-placeholder {
            width: 50mm;
            height: 18mm;
            border: 1px dashed #ccc;
            display: inline-block;
            line-height: 18mm;
            font-size: 8pt;
            color: #aaa;
            text-align: center;
            font-family: 'DejaVu Sans', Arial, sans-serif;
        }
        
        .lsp-info {
            margin-top: 2mm;
        }
        
        .lsp-name {
            font-size: 10pt;
            font-weight: bold;
            color: #1a365d;
        }
        
        .lsp-lisensi {
            font-size: 7pt;
            color: #666;
        }
        
        /* ================================================================
           TITLE SECTION
           ================================================================ */
        
        .title-section {
            text-align: center;
            margin: 8mm 0 10mm 0;
            padding-bottom: 5mm;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .title-main {
            font-size: 28pt;
            font-weight: bold;
            color: #1a365d;
            text-transform: uppercase;
            letter-spacing: 5px;
            margin-bottom: 2mm;
        }
        
        .title-sub {
            font-size: 11pt;
            color: #666;
            font-style: italic;
            letter-spacing: 2px;
        }
        
        /* ================================================================
           BODY CONTENT
           ================================================================ */
        
        .body-content {
            text-align: center;
            padding: 0 15mm;
        }
        
        .intro-text {
            font-size: 12pt;
            color: #444;
            margin-bottom: 4mm;
        }
        
        .recipient-name {
            font-size: 26pt;
            font-weight: bold;
            color: #1a365d;
            text-transform: uppercase;
            letter-spacing: 2px;
            padding: 4mm 0;
            margin: 0 auto 6mm auto;
            border-bottom: 2.5px solid #b8860b;
            display: inline-block;
            min-width: 65%;
        }
        
        /* Info Grid */
        .info-section {
            margin: 8mm 0;
        }
        
        .info-row {
            margin: 5mm 0;
        }
        
        .info-label {
            font-size: 10pt;
            color: #666;
            margin-bottom: 1mm;
        }
        
        .info-value {
            font-size: 13pt;
            font-weight: bold;
            color: #1a365d;
        }
        
        .info-value-large {
            font-size: 15pt;
            font-weight: bold;
            color: #1a365d;
            line-height: 1.4;
        }
        
        .info-code {
            font-size: 9pt;
            color: #888;
            font-family: 'DejaVu Sans Mono', monospace;
        }
        
        /* Declaration Box */
        .declaration-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 5mm 8mm;
            margin: 8mm auto;
            display: inline-block;
            min-width: 70%;
        }
        
        .declaration-intro {
            font-size: 10pt;
            color: #555;
            margin-bottom: 2mm;
        }
        
        .declaration-status {
            font-size: 20pt;
            font-weight: bold;
            color: #1a365d;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        
        /* Validity Table */
        .validity-table {
            display: table;
            width: 75%;
            margin: 8mm auto;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .validity-row {
            display: table-row;
        }
        
        .validity-cell {
            display: table-cell;
            width: 50%;
            padding: 4mm;
            text-align: center;
            background: #fafafa;
        }
        
        .validity-cell:first-child {
            border-right: 1px solid #e0e0e0;
        }
        
        .validity-label {
            font-size: 9pt;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1mm;
        }
        
        .validity-value {
            font-size: 11pt;
            font-weight: bold;
            color: #1a365d;
        }
        
        /* ================================================================
           FOOTER SECTION
           ================================================================ */
        
        .footer-section {
            position: absolute;
            bottom: 22mm;
            left: 18mm;
            right: 18mm;
        }
        
        .footer-grid {
            display: table;
            width: 100%;
        }
        
        .footer-cell {
            display: table-cell;
            vertical-align: bottom;
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
        
        /* QR Code */
        .qr-wrapper {
            text-align: center;
        }
        
        .qr-code-img {
            width: 28mm;
            height: 28mm;
            margin-bottom: 2mm;
        }
        
        .qr-text {
            font-size: 7pt;
            color: #888;
            line-height: 1.3;
        }
        
        /* Signature Block */
        .signature-wrapper {
            text-align: center;
        }
        
        .signature-location {
            font-size: 10pt;
            color: #444;
            margin-bottom: 18mm;
        }
        
        .signature-line {
            width: 60mm;
            border-bottom: 1.5px solid #333;
            margin: 0 auto 2mm auto;
        }
        
        .signature-name {
            font-size: 12pt;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 1mm;
        }
        
        .signature-title {
            font-size: 9pt;
            color: #666;
        }
        
        /* Info Block */
        .info-wrapper {
            text-align: right;
            padding-right: 5mm;
        }
        
        .secure-label {
            font-size: 7pt;
            color: #aaa;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .secure-id {
            font-size: 6pt;
            color: #999;
            font-family: 'DejaVu Sans Mono', monospace;
            word-break: break-all;
            line-height: 1.4;
        }
        
        .doc-id {
            font-size: 5pt;
            color: #bbb;
            margin-top: 2mm;
        }
        
        /* ================================================================
           SECURITY FEATURES
           ================================================================ */
        
        /* Watermark */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-40deg);
            font-size: 60pt;
            color: rgba(26, 54, 93, 0.025);
            font-weight: bold;
            letter-spacing: 15px;
            white-space: nowrap;
            pointer-events: none;
            z-index: 1;
            font-family: 'DejaVu Sans', Arial, sans-serif;
        }
        
        /* Secure Footer */
        .secure-footer {
            position: absolute;
            bottom: 10mm;
            left: 50%;
            transform: translateX(-50%);
            font-size: 6pt;
            color: #ccc;
            letter-spacing: 0.5px;
            font-family: 'DejaVu Sans Mono', monospace;
            text-align: center;
        }
        
        /* ================================================================
           UTILITY CLASSES
           ================================================================ */
        
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .text-uppercase { text-transform: uppercase; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>
    @php
        $logoInfo = systemLogoInfo();
        $logoBase64 = systemLogoBase64();
    @endphp
    <!-- Watermark Background -->
    @if($logoInfo['nama_perusahaan'])
        <div class="watermark">{{ strtoupper($logoInfo['nama_perusahaan']) }}</div>
    @endif
    
    <div class="certificate-page">
        <!-- Decorative Borders -->
        <div class="border-outer"></div>
        <div class="border-inner"></div>
        <div class="corner-decoration corner-tl"></div>
        <div class="corner-decoration corner-tr"></div>
        <div class="corner-decoration corner-bl"></div>
        <div class="corner-decoration corner-br"></div>
        
        <!-- Secure Footer -->
        <div class="secure-footer">
            SECURE-ID: {{ $sertifikat->uuid }} | HASH: {{ substr($sertifikat->security_hash, 0, 12) }}
        </div>
        
        <!-- Main Content -->
        <div class="content-wrapper">
            
            <!-- ============ HEADER - LOGOS ============ -->
            <div class="header-logos">
                <div class="logo-cell logo-left">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" class="logo-img" alt="Logo LSP">
                    @else
                        <div class="logo-placeholder">LOGO LSP</div>
                    @endif
                    <div class="lsp-info">
                        @if($logoInfo['nama_perusahaan'])
                            <div class="lsp-name">{{ $logoInfo['nama_perusahaan'] }}</div>
                        @endif
                        @if(config('certipro.nomor_lisensi'))
                            <div class="lsp-lisensi">Lisensi: {{ config('certipro.nomor_lisensi') }}</div>
                        @endif
                    </div>
                </div>
                <div class="logo-cell logo-center">
                    {{-- Reserved for additional emblems --}}
                </div>
                <div class="logo-cell logo-right">
                    @if(file_exists(public_path('images/logo-bnsp.png')))
                        <img src="{{ public_path('images/logo-bnsp.png') }}" class="logo-img" alt="Logo BNSP">
                    @else
                        <div class="logo-placeholder">LOGO BNSP</div>
                    @endif
                </div>
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
                
                <!-- Declaration -->
                <div class="declaration-box">
                    <div class="declaration-intro">Menyatakan bahwa yang bersangkutan</div>
                    <div class="declaration-status">Telah Dinyatakan Kompeten</div>
                </div>
                
                <!-- Validity Period -->
                <div class="validity-table">
                    <div class="validity-row">
                        <div class="validity-cell">
                            <div class="validity-label">Tanggal Terbit</div>
                            <div class="validity-value">{{ $sertifikat->tanggal_terbit->translatedFormat('d F Y') }}</div>
                        </div>
                        <div class="validity-cell">
                            <div class="validity-label">Berlaku Sampai</div>
                            <div class="validity-value">{{ $sertifikat->tanggal_berlaku_sampai->translatedFormat('d F Y') }}</div>
                        </div>
                    </div>
                </div>
                
            </div>
            
            <!-- ============ FOOTER ============ -->
            <div class="footer-section">
                <div class="footer-grid">
                    
                    <!-- QR Code -->
                    <div class="footer-cell footer-qr">
                        <div class="qr-wrapper">
                            <img src="data:image/svg+xml;base64,{{ $qrCodeBase64 }}" class="qr-code-img" alt="QR Verification">
                            <div class="qr-text">
                                Scan untuk verifikasi<br>
                                keaslian sertifikat
                            </div>
                        </div>
                    </div>
                    
                    <!-- Signature -->
                    <div class="footer-cell footer-signature">
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
                    </div>
                    
                    <!-- Secure Info -->
                    <div class="footer-cell footer-info">
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
                    </div>
                    
                </div>
            </div>
            
        </div>
    </div>
</body>
</html>

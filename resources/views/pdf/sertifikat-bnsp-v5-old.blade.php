<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Sertifikat Kompetensi - {{ $sertifikat->nomor_sertifikat }}</title>
    <style>
        /* ================================================================
           SERTIFIKAT KOMPETENSI LSP - DomPDF 1-PAGE LOCK
           ================================================================ */
        
        @page {
            size: A4 portrait;
            margin: 25mm 20mm 25mm 20mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'DejaVu Serif', 'Times New Roman', Georgia, serif;
        }
        
        /* TABLE WRAPPER - LOCK HEIGHT */
        .page-wrapper {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
        }
        
        .page-cell {
            vertical-align: middle;
            text-align: center;
        }
        
        /* CERTIFICATE BOX */
        .cert-box {
            border: 3px solid #1a365d;
            padding: 10mm;
            position: relative;
        }
        
        .cert-inner {
            border: 1px solid #b8860b;
            padding: 8mm;
            position: relative;
        }
        
        /* WATERMARK */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 70mm;
            height: 70mm;
            margin-left: -35mm;
            margin-top: -35mm;
            opacity: 0.05;
            z-index: -1;
        }
        
        .watermark img {
            width: 100%;
            height: 100%;
        }
        
        /* CONTENT */
        .content {
            position: relative;
            z-index: 1;
        }
        
        /* HEADER */
        .header {
            margin-bottom: 4mm;
        }
        
        .header table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .header td {
            vertical-align: middle;
            padding: 1mm;
        }
        
        .logo-left {
            width: 35%;
            text-align: left;
        }
        
        .logo-center {
            width: 30%;
        }
        
        .logo-right {
            width: 35%;
            text-align: right;
        }
        
        .logo-img {
            max-height: 14mm;
            max-width: 40mm;
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
        
        /* TITLE */
        .title {
            margin: 3mm 0;
            padding-bottom: 2mm;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .title h1 {
            font-size: 20pt;
            font-weight: bold;
            color: #1a365d;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 1mm;
        }
        
        .title p {
            font-size: 9pt;
            color: #666;
            font-style: italic;
        }
        
        /* BODY */
        .body {
            margin: 3mm 0;
        }
        
        .intro {
            font-size: 9pt;
            color: #444;
            margin-bottom: 1mm;
        }
        
        .name {
            font-size: 18pt;
            font-weight: bold;
            color: #1a365d;
            text-transform: uppercase;
            padding: 2mm 8mm;
            margin: 0 auto 3mm;
            border-bottom: 2px solid #b8860b;
            display: inline-block;
        }
        
        .info {
            margin: 2mm 0;
        }
        
        .info-label {
            font-size: 8pt;
            color: #666;
        }
        
        .info-value {
            font-size: 10pt;
            font-weight: bold;
            color: #1a365d;
        }
        
        .info-large {
            font-size: 11pt;
            font-weight: bold;
            color: #1a365d;
            line-height: 1.2;
        }
        
        .info-code {
            font-size: 7pt;
            color: #888;
            font-family: 'DejaVu Sans Mono', monospace;
        }
        
        /* DECLARATION */
        .declaration {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 3mm;
            margin: 3mm auto;
            display: inline-block;
        }
        
        .declaration-intro {
            font-size: 8pt;
            color: #555;
            margin-bottom: 1mm;
        }
        
        .declaration-status {
            font-size: 14pt;
            font-weight: bold;
            color: #1a365d;
            text-transform: uppercase;
        }
        
        /* VALIDITY */
        .validity {
            width: 65%;
            margin: 3mm auto;
        }
        
        .validity table {
            width: 100%;
            border: 1px solid #e0e0e0;
            border-collapse: collapse;
        }
        
        .validity td {
            width: 50%;
            padding: 2mm;
            text-align: center;
            background: #fafafa;
            border: 1px solid #e0e0e0;
        }
        
        .validity-label {
            font-size: 7pt;
            color: #888;
            text-transform: uppercase;
            display: block;
        }
        
        .validity-value {
            font-size: 9pt;
            font-weight: bold;
            color: #1a365d;
            display: block;
        }
        
        /* FOOTER */
        .footer {
            margin-top: 4mm;
            padding-top: 2mm;
            border-top: 1px solid #e0e0e0;
        }
        
        .footer table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .footer td {
            vertical-align: top;
            padding: 1mm;
        }
        
        .footer-qr {
            width: 28%;
            text-align: center;
        }
        
        .footer-sig {
            width: 44%;
            text-align: center;
        }
        
        .footer-info {
            width: 28%;
            text-align: center;
        }
        
        .qr-img {
            width: 20mm;
            height: 20mm;
            border: 1px solid #e0e0e0;
        }
        
        .qr-text {
            font-size: 6pt;
            color: #888;
            margin-top: 0.5mm;
        }
        
        .sig-location {
            font-size: 8pt;
            color: #444;
            margin-bottom: 8mm;
        }
        
        .sig-line {
            width: 50mm;
            border-bottom: 1px solid #333;
            margin: 0 auto 1mm;
        }
        
        .sig-name {
            font-size: 10pt;
            font-weight: bold;
            color: #1a365d;
        }
        
        .sig-title {
            font-size: 7pt;
            color: #666;
        }
        
        .secure-label {
            font-size: 6pt;
            color: #aaa;
            text-transform: uppercase;
        }
        
        .secure-id {
            font-size: 5pt;
            color: #999;
            font-family: 'DejaVu Sans Mono', monospace;
            word-break: break-all;
            line-height: 1.2;
        }
        
        .doc-id {
            font-size: 5pt;
            color: #bbb;
            margin-top: 1mm;
        }
        
        .secure-footer {
            font-size: 5pt;
            color: #ccc;
            font-family: 'DejaVu Sans Mono', monospace;
            margin-top: 2mm;
            padding-top: 1mm;
            border-top: 1px solid #f0f0f0;
        }
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
    
    <table class="page-wrapper" cellspacing="0" cellpadding="0">
        <tr>
            <td class="page-cell">
                
                <div class="cert-box">
                    <div class="cert-inner">
                        
                        @if($logoBase64)
                        <div class="watermark">
                            <img src="{{ $logoBase64 }}" alt="">
                        </div>
                        @endif
                        
                        <div class="content">
                            
                            <!-- HEADER -->
                            <div class="header">
                                <table cellspacing="0" cellpadding="0">
                                    <tr>
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
                                        <td class="logo-center"></td>
                                        <td class="logo-right">
                                            @if($isBnspLicensed && file_exists($logoBnspPath))
                                                <img src="{{ $logoBnspPath }}" class="logo-img" alt="Logo BNSP">
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                            <!-- TITLE -->
                            <div class="title">
                                <h1>Sertifikat Kompetensi</h1>
                                <p>Certificate of Competency</p>
                            </div>
                            
                            <!-- BODY -->
                            <div class="body">
                                <p class="intro">Diberikan kepada:</p>
                                <h2 class="name">{{ strtoupper($sertifikat->nama_peserta) }}</h2>
                                
                                <div class="info">
                                    <div class="info-label">Nomor Sertifikat:</div>
                                    <div class="info-value">{{ $sertifikat->nomor_sertifikat }}</div>
                                </div>
                                
                                <div class="info">
                                    <div class="info-label">Skema Sertifikasi:</div>
                                    <div class="info-large">{{ $sertifikat->skema_sertifikasi }}</div>
                                    @if(isset($pendaftaran) && $pendaftaran->skemaSertifikasi)
                                        <div class="info-code">({{ $pendaftaran->skemaSertifikasi->kode_skema ?? '' }})</div>
                                    @endif
                                </div>
                                
                                <div class="declaration">
                                    <div class="declaration-intro">Menyatakan bahwa yang bersangkutan</div>
                                    <div class="declaration-status">Telah Dinyatakan Kompeten</div>
                                </div>
                                
                                <div class="validity">
                                    <table cellspacing="0" cellpadding="0">
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
                            
                            <!-- FOOTER -->
                            <div class="footer">
                                <table cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td class="footer-qr">
                                            <img src="data:image/svg+xml;base64,{{ $qrCodeBase64 }}" class="qr-img" alt="QR">
                                            <div class="qr-text">Scan untuk<br>verifikasi</div>
                                        </td>
                                        <td class="footer-sig">
                                            <div class="sig-location">
                                                {{ config('certipro.kota_terbit', 'Jakarta') }}, {{ $sertifikat->tanggal_terbit->translatedFormat('d F Y') }}
                                            </div>
                                            <div class="sig-line"></div>
                                            <div class="sig-name">{{ $ketuaLsp }}</div>
                                            @if($logoInfo['nama_perusahaan'])
                                                <div class="sig-title">Ketua {{ $logoInfo['nama_perusahaan'] }}</div>
                                            @else
                                                <div class="sig-title">Ketua LSP</div>
                                            @endif
                                        </td>
                                        <td class="footer-info">
                                            <div class="secure-label">Secure ID</div>
                                            <div class="secure-id">
                                                {{ substr($sertifikat->uuid, 0, 18) }}<br>
                                                {{ substr($sertifikat->uuid, 18) }}
                                            </div>
                                            <div class="doc-id">
                                                DOC-{{ $sertifikat->id }}-{{ $sertifikat->created_at->format('Ymd') }}
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                                
                                <div class="secure-footer">
                                    SECURE-ID: {{ $sertifikat->uuid }} | HASH: {{ substr($sertifikat->security_hash ?? '', 0, 12) }}
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
                
            </td>
        </tr>
    </table>
</body>
</html>

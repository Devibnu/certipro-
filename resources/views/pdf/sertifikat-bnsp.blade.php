<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Sertifikat Kompetensi - {{ $sertifikat->nomor_sertifikat }}</title>
    <style>
        /* ================================================================
           SERTIFIKAT KOMPETENSI LSP - DomPDF SIMPLE & STABLE
           ================================================================ */
        
        @page {
            size: A4 portrait;
            margin: 6mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            margin: 0;
            padding: 0;
            font-family: 'DejaVu Serif', 'Times New Roman', Georgia, serif;
        }
        
        .page {
            padding: 7mm;
        }
        
        .certificate {
            border: 3px solid #1a365d;
            padding: 15mm;
            text-align: center;
            position: relative;
        }
        
        /* WATERMARK */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 120px;
            height: 120px;
            margin-left: -60px;
            margin-top: -60px;
            opacity: 0.05;
            z-index: 0;
        }
        
        .content {
            position: relative;
            z-index: 1;
        }
        
        /* HEADER LOGO */
        .header-logos {
            margin-bottom: 5mm;
        }
        
        .header-logos table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .header-logos td {
            vertical-align: middle;
            padding: 1mm;
        }
        
        .logo-img {
            max-height: 42px;
            max-width: 105px;
        }
        
        .lsp-name {
            font-size: 7pt;
            color: #333;
            font-weight: bold;
            margin-top: 1.5mm;
        }
        
        .lsp-lisensi {
            font-size: 6pt;
            color: #777;
        }
        
        /* TITLE */
        .title {
            margin-bottom: 5mm;
            padding-bottom: 2.5mm;
            border-bottom: 1px solid #ddd;
        }
        
        .title h1 {
            font-size: 20pt;
            font-weight: bold;
            color: #1a365d;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 1.5mm;
        }
        
        .title p {
            font-size: 10pt;
            color: #666;
            font-style: italic;
        }
        
        /* BODY */
        .intro {
            font-size: 9pt;
            color: #555;
            margin-bottom: 3mm;
        }
        
        .name {
            font-size: 16pt;
            font-weight: bold;
            color: #1a365d;
            text-transform: uppercase;
            margin: 4mm 0;
            padding: 3.5mm 11mm;
            border-bottom: 2px solid #c9a24d;
            display: inline-block;
        }
        
        .info-section {
            margin: 4mm 0;
        }
        
        .info-label {
            font-size: 8pt;
            color: #777;
            margin-top: 1mm;
        }
        
        .info-value {
            font-size: 11pt;
            font-weight: bold;
            color: #1a365d;
            margin: 2mm 0;
            line-height: 1.3;
        }
        
        .info-code {
            font-size: 7pt;
            color: #999;
            font-family: 'DejaVu Sans Mono', monospace;
        }
        
        /* DECLARATION */
        .declaration {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 4.5mm;
            margin: 3.5mm auto;
            display: inline-block;
        }
        
        .declaration-intro {
            font-size: 8pt;
            color: #666;
            margin-bottom: 1mm;
        }
        
        .declaration-status {
            font-size: 13pt;
            font-weight: bold;
            color: #1a365d;
            text-transform: uppercase;
        }
        
        /* VALIDITY */
        .validity {
            width: 65%;
            margin: 3.5mm auto;
        }
        
        .validity table {
            width: 100%;
            border: 1px solid #ddd;
            border-collapse: collapse;
        }
        
        .validity td {
            width: 50%;
            padding: 4mm;
            text-align: center;
            border: 1px solid #ddd;
            background: #fafafa;
        }
        
        .validity-label {
            font-size: 7pt;
            color: #999;
            text-transform: uppercase;
            display: block;
        }
        
        .validity-value {
            font-size: 10pt;
            font-weight: bold;
            color: #1a365d;
            display: block;
            margin-top: 1mm;
        }
        
        /* FOOTER */
        .footer {
            margin-top: 7mm;
            padding-top: 3mm;
            border-top: 1px solid #ddd;
        }
        
        .footer table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .footer td {
            vertical-align: top;
            padding: 2.5mm;
        }
        
        .qr-section {
            text-align: center;
        }
        
        .qr-code {
            width: 80px;
            height: 80px;
            border: 1px solid #ddd;
        }
        
        .qr-text {
            font-size: 6pt;
            color: #999;
            margin-top: 1mm;
        }
        
        .sig-section {
            text-align: center;
        }
        
        .sig-location {
            font-size: 9pt;
            color: #555;
            margin-bottom: 16mm;
        }
        
        .sig-line {
            width: 130px;
            border-bottom: 1px solid #333;
            margin: 0 auto 1.5mm auto;
        }
        
        .sig-name {
            font-size: 11pt;
            font-weight: bold;
            color: #1a365d;
        }
        
        .sig-title {
            font-size: 8pt;
            color: #666;
        }
        
        .secure-section {
            text-align: center;
        }
        
        .secure-label {
            font-size: 6pt;
            color: #bbb;
            text-transform: uppercase;
        }
        
        .secure-id {
            font-size: 5pt;
            color: #ccc;
            font-family: 'DejaVu Sans Mono', monospace;
            word-break: break-all;
            line-height: 1.2;
            margin-top: 1mm;
        }
        
        .doc-id {
            font-size: 5pt;
            color: #ddd;
            margin-top: 2mm;
        }
        
        .secure-footer {
            font-size: 5pt;
            color: #e0e0e0;
            font-family: 'DejaVu Sans Mono', monospace;
            margin-top: 3mm;
            padding-top: 2mm;
            border-top: 1px solid #f5f5f5;
            text-align: center;
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
        $kotaTerbit = config('certipro.kota_terbit', 'Jakarta');
    @endphp
    
    <div class="page">
        <div class="certificate">
            
            <!-- WATERMARK -->
            @if($logoBase64)
            <div class="watermark">
                <img src="{{ $logoBase64 }}" width="120" height="120" alt="">
            </div>
            @endif
            
            <div class="content">
                
                <!-- HEADER LOGO -->
                <div class="header-logos">
                    <table cellpadding="0" cellspacing="0">
                        <tr>
                            <td width="35%" align="left">
                                @if($logoBase64)
                                    <img src="{{ $logoBase64 }}" class="logo-img" alt="Logo LSP">
                                @endif
                                @if($logoInfo['nama_perusahaan'])
                                    <div class="lsp-name">{{ $logoInfo['nama_perusahaan'] }}</div>
                                @endif
                                @if($nomorLisensi)
                                    <div class="lsp-lisensi">Lisensi: {{ $nomorLisensi }}</div>
                                @endif
                            </td>
                            <td width="30%" align="center"></td>
                            <td width="35%" align="right">
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
                <div class="intro">Diberikan kepada:</div>
                
                <div class="name">{{ strtoupper($sertifikat->nama_peserta) }}</div>
                
                <div class="info-section">
                    <div class="info-label">Nomor Sertifikat:</div>
                    <div class="info-value">{{ $sertifikat->nomor_sertifikat }}</div>
                </div>
                
                <div class="info-section">
                    <div class="info-label">Skema Sertifikasi:</div>
                    <div class="info-value">{{ $sertifikat->skema_sertifikasi }}</div>
                    @if(isset($pendaftaran) && $pendaftaran->skemaSertifikasi)
                        <div class="info-code">({{ $pendaftaran->skemaSertifikasi->kode_skema ?? '' }})</div>
                    @endif
                </div>
                
                <!-- DECLARATION -->
                <div class="declaration">
                    <div class="declaration-intro">Menyatakan bahwa yang bersangkutan</div>
                    <div class="declaration-status">Telah Dinyatakan Kompeten</div>
                </div>
                
                <!-- VALIDITY -->
                <div class="validity">
                    <table cellpadding="0" cellspacing="0">
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
                
                <!-- FOOTER -->
                <div class="footer">
                    <table cellpadding="0" cellspacing="0">
                        <tr>
                            <!-- QR CODE -->
                            <td width="28%" class="qr-section">
                                <img src="data:image/svg+xml;base64,{{ $qrCodeBase64 }}" 
                                     class="qr-code"
                                     width="80" 
                                     height="80" 
                                     alt="QR">
                                <div class="qr-text">Scan untuk verifikasi</div>
                            </td>
                            
                            <!-- SIGNATURE -->
                            <td width="44%" class="sig-section">
                                <div class="sig-location">
                                    {{ $kotaTerbit }}, {{ $sertifikat->tanggal_terbit->translatedFormat('d F Y') }}
                                </div>
                                <div class="sig-line"></div>
                                <div class="sig-name">{{ $ketuaLsp }}</div>
                                @if($logoInfo['nama_perusahaan'])
                                    <div class="sig-title">Ketua {{ $logoInfo['nama_perusahaan'] }}</div>
                                @else
                                    <div class="sig-title">Ketua LSP</div>
                                @endif
                            </td>
                            
                            <!-- SECURE ID -->
                            <td width="28%" class="secure-section">
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
                    
                    <!-- SECURE FOOTER -->
                    <div class="secure-footer">
                        SECURE: {{ $sertifikat->uuid }} | HASH: {{ substr($sertifikat->security_hash ?? '', 0, 12) }}
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</body>
</html>

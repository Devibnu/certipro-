<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Sertifikat Kompetensi - {{ $sertifikat->nomor_sertifikat }}</title>
    <style>
        /* ================================================================
           SERTIFIKAT KOMPETENSI LSP - DomPDF TABLE-BASED (ANTI 2 HALAMAN)
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
        
        /* WATERMARK (OPSIONAL - OPACITY 5%) */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 150px;
            height: 150px;
            margin-left: -75px;
            margin-top: -75px;
            opacity: 0.05;
            z-index: 0;
        }
        
        /* CERTIFICATE BORDER */
        .cert-border {
            border: 3px solid #1a365d;
            padding: 15px;
            position: relative;
            background: #fff;
        }
        
        .cert-border-inner {
            border: 1px solid #c9a24d;
            padding: 20px 15px;
        }
        
        /* LOGO */
        .logo-img {
            max-height: 50px;
            max-width: 120px;
        }
        
        /* TITLE */
        .title-main {
            font-size: 22pt;
            font-weight: bold;
            color: #1a365d;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 8px 0 3px 0;
        }
        
        .title-sub {
            font-size: 10pt;
            color: #666;
            font-style: italic;
            margin-bottom: 12px;
        }
        
        .divider {
            border-top: 1px solid #e0e0e0;
            margin: 5px auto;
            width: 80%;
        }
        
        /* BODY TEXT */
        .intro-text {
            font-size: 9pt;
            color: #555;
            margin: 10px 0 5px 0;
        }
        
        .name-peserta {
            font-size: 18pt;
            font-weight: bold;
            color: #1a365d;
            text-transform: uppercase;
            margin: 10px 0;
            padding: 8px 20px;
            border-bottom: 2px solid #c9a24d;
            display: inline-block;
        }
        
        .info-label {
            font-size: 8pt;
            color: #777;
            margin-top: 8px;
        }
        
        .info-value {
            font-size: 11pt;
            font-weight: bold;
            color: #1a365d;
            margin: 3px 0;
            line-height: 1.3;
        }
        
        .info-code {
            font-size: 7pt;
            color: #999;
            font-family: 'DejaVu Sans Mono', monospace;
        }
        
        /* DECLARATION BOX */
        .declaration-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 10px 15px;
            margin: 12px auto;
            display: inline-block;
        }
        
        .declaration-intro {
            font-size: 8pt;
            color: #666;
        }
        
        .declaration-status {
            font-size: 14pt;
            font-weight: bold;
            color: #1a365d;
            text-transform: uppercase;
            margin-top: 2px;
        }
        
        /* VALIDITY TABLE */
        .validity-table {
            width: 60%;
            margin: 12px auto;
            border: 1px solid #e0e0e0;
            border-collapse: collapse;
        }
        
        .validity-table td {
            padding: 8px;
            text-align: center;
            border: 1px solid #e0e0e0;
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
            margin-top: 2px;
        }
        
        /* FOOTER */
        .footer-divider {
            border-top: 1px solid #e0e0e0;
            margin: 15px auto 10px auto;
            width: 90%;
        }
        
        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .footer-table td {
            vertical-align: top;
            padding: 5px;
        }
        
        .qr-code {
            width: 80px;
            height: 80px;
            border: 1px solid #ddd;
        }
        
        .qr-text {
            font-size: 6pt;
            color: #999;
            margin-top: 3px;
        }
        
        .sig-location {
            font-size: 9pt;
            color: #555;
            margin-bottom: 25px;
        }
        
        .sig-line {
            width: 150px;
            border-bottom: 1px solid #333;
            margin: 0 auto 5px auto;
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
        
        .secure-id-label {
            font-size: 6pt;
            color: #bbb;
            text-transform: uppercase;
        }
        
        .secure-id-value {
            font-size: 5pt;
            color: #ccc;
            font-family: 'DejaVu Sans Mono', monospace;
            word-break: break-all;
            line-height: 1.2;
            margin-top: 2px;
        }
        
        .doc-id {
            font-size: 5pt;
            color: #ddd;
            margin-top: 3px;
        }
        
        .secure-footer {
            font-size: 5pt;
            color: #e0e0e0;
            font-family: 'DejaVu Sans Mono', monospace;
            margin-top: 8px;
            padding-top: 5px;
            border-top: 1px solid #f5f5f5;
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
    
    <!-- OUTER TABLE: LOCK HEIGHT 100% & CENTER VERTIKAL -->
    <table width="100%" height="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" valign="middle">
                
                <!-- INNER TABLE: CERTIFICATE CONTENT -->
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td>
                            <div class="cert-border">
                                <div class="cert-border-inner">
                                    
                                    <!-- WATERMARK (OPSIONAL) -->
                                    @if($logoBase64)
                                    <div class="watermark">
                                        <img src="{{ $logoBase64 }}" width="150" height="150" alt="">
                                    </div>
                                    @endif
                                    
                                    <!-- HEADER: LOGO LSP & BNSP -->
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td width="33%" align="left" valign="middle">
                                                @if($logoBase64)
                                                    <img src="{{ $logoBase64 }}" class="logo-img" alt="Logo LSP">
                                                @endif
                                                @if($logoInfo['nama_perusahaan'])
                                                    <div style="font-size:7pt; color:#555; margin-top:3px;">
                                                        <strong>{{ $logoInfo['nama_perusahaan'] }}</strong>
                                                    </div>
                                                @endif
                                                @if($nomorLisensi)
                                                    <div style="font-size:6pt; color:#999;">
                                                        Lisensi: {{ $nomorLisensi }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td width="34%" align="center" valign="middle">
                                                <!-- CENTER SPACE -->
                                            </td>
                                            <td width="33%" align="right" valign="middle">
                                                @if($isBnspLicensed && file_exists($logoBnspPath))
                                                    <img src="{{ $logoBnspPath }}" class="logo-img" alt="Logo BNSP">
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <!-- TITLE -->
                                    <div class="title-main">Sertifikat Kompetensi</div>
                                    <div class="title-sub">Certificate of Competency</div>
                                    <div class="divider"></div>
                                    
                                    <!-- BODY: NAMA & INFO -->
                                    <div class="intro-text">Diberikan kepada:</div>
                                    
                                    <div class="name-peserta">{{ strtoupper($sertifikat->nama_peserta) }}</div>
                                    
                                    <div class="info-label">Nomor Sertifikat:</div>
                                    <div class="info-value">{{ $sertifikat->nomor_sertifikat }}</div>
                                    
                                    <div class="info-label">Skema Sertifikasi:</div>
                                    <div class="info-value">{{ $sertifikat->skema_sertifikasi }}</div>
                                    @if(isset($pendaftaran) && $pendaftaran->skemaSertifikasi)
                                        <div class="info-code">({{ $pendaftaran->skemaSertifikasi->kode_skema ?? '' }})</div>
                                    @endif
                                    
                                    <!-- DECLARATION BOX -->
                                    <div class="declaration-box">
                                        <div class="declaration-intro">Menyatakan bahwa yang bersangkutan</div>
                                        <div class="declaration-status">Telah Dinyatakan Kompeten</div>
                                    </div>
                                    
                                    <!-- VALIDITY TABLE -->
                                    <table class="validity-table" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td width="50%">
                                                <span class="validity-label">Tanggal Terbit</span>
                                                <span class="validity-value">{{ $sertifikat->tanggal_terbit->translatedFormat('d F Y') }}</span>
                                            </td>
                                            <td width="50%">
                                                <span class="validity-label">Berlaku Sampai</span>
                                                <span class="validity-value">{{ $sertifikat->tanggal_berlaku_sampai->translatedFormat('d F Y') }}</span>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <!-- FOOTER DIVIDER -->
                                    <div class="footer-divider"></div>
                                    
                                    <!-- FOOTER: QR + SIGNATURE + SECURE ID -->
                                    <table class="footer-table" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <!-- QR CODE -->
                                            <td width="28%" align="center">
                                                <img src="data:image/svg+xml;base64,{{ $qrCodeBase64 }}" 
                                                     class="qr-code" 
                                                     width="80" 
                                                     height="80" 
                                                     alt="QR Code">
                                                <div class="qr-text">Scan untuk verifikasi</div>
                                            </td>
                                            
                                            <!-- SIGNATURE -->
                                            <td width="44%" align="center">
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
                                            <td width="28%" align="center">
                                                <div class="secure-id-label">Secure ID</div>
                                                <div class="secure-id-value">
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
                        </td>
                    </tr>
                </table>
                
            </td>
        </tr>
    </table>
</body>
</html>

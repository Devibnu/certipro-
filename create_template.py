#!/usr/bin/env python3
# -*- coding: utf-8 -*-

template = r'''<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Sertifikat Kompetensi - {{ $sertifikat->nomor_sertifikat }}</title>
    <style>
        /* ================================================================
           TABLE-BASED LAYOUT - DomPDF Height Lock
           Version 5 - FULL PAGE NO EMPTY SPACE
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
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'DejaVu Serif', 'Times New Roman', Georgia, serif;
            background: #ffffff;
            color: #1a1a1a;
        }
        
        /* TABLE LAYOUT - WAJIB untuk DomPDF */
        .page-table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
            margin: 0;
            padding: 0;
        }
        
        .page-content {
            vertical-align: middle;
            text-align: center;
            padding: 0;
            position: relative;
        }
        
        .certificate-box {
            width: 100%;
            border: 3px solid #1a365d;
            padding: 12mm;
            background: #ffffff;
            position: relative;
        }
        
        .certificate-inner {
            border: 1px solid #b8860b;
            padding: 10mm;
            position: relative;
        }
        
        .watermark-bg {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 75mm;
            height: 75mm;
            margin-left: -37.5mm;
            margin-top: -37.5mm;
            opacity: 0.05;
            z-index: 0;
        }
        
        .watermark-logo {
            width: 100%;
            height: 100%;
        }
        
        .certificate-content {
            position: relative;
            z-index: 1;
            text-align: center;
        }
        
        /* HEADER */
        .header-logos {
            width: 100%;
            margin: 0 auto 4mm auto;
        }
        
        .header-table {
            width: 100%;
            border-collapse: collapse;
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
            max-height: 15mm;
            max-width: 40mm;
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
        
        /* TITLE */
        .title-section {
            text-align: center;
            margin: 3mm auto;
            padding-bottom: 2mm;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .title-main {
            font-size: 20pt;
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
        
        /* BODY */
        .body-content {
            text-align: center;
            padding: 0 5mm;
        }
        
        .intro-text {
            font-size: 9pt;
            color: #444;
            margin: 2mm 0 1mm 0;
        }
        
        .recipient-name {
            font-size: 18pt;
            font-weight: bold;
            color: #1a365d;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 2mm 8mm;
            margin: 0 auto 3mm auto;
            border-bottom: 2px solid #b8860b;
            display: inline-block;
        }
        
        .info-row {
            margin: 2mm auto;
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
            font-size: 11pt;
            font-weight: bold;
            color: #1a365d;
            line-height: 1.3;
        }
        
        .info-code {
            font-size: 7pt;
            color: #888;
            font-family: 'DejaVu Sans Mono', 'Courier New', monospace;
        }
        
        .declaration-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 3mm 4mm;
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
            letter-spacing: 1.5px;
        }
        
        .validity-section {
            width: 65%;
            margin: 3mm auto;
        }
        
        .validity-table {
            width: 100%;
            border: 1px solid #e0e0e0;
            border-collapse: collapse;
        }
        
        .validity-table td {
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
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 0.5mm;
        }
        
        .validity-value {
            font-size: 9pt;
            font-weight: bold;
            color: #1a365d;
            display: block;
        }
        
        /* FOOTER */
        .footer-section {
            margin-top: 4mm;
            padding-top: 2mm;
            border-top: 1px solid #e0e0e0;
        }
        
        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .footer-table td {
            vertical-align: top;
            padding: 1mm;
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
        
        .qr-wrapper {
            text-align: center;
            margin: 0 auto 1mm auto;
        }
        
        .qr-code-img {
            width: 20mm;
            height: 20mm;
            border: 1px solid #e0e0e0;
            display: inline-block;
        }
        
        .qr-text {
            font-size: 6pt;
            color: #888;
            line-height: 1.1;
            margin-top: 0.5mm;
        }
        
        .signature-wrapper {
            text-align: center;
        }
        
        .signature-location {
            font-size: 8pt;
            color: #444;
            margin-bottom: 8mm;
        }
        
        .signature-line {
            width: 50mm;
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
        
        .info-wrapper {
            text-align: center;
        }
        
        .secure-label {
            font-size: 6pt;
            color: #aaa;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5mm;
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
        
        .secure-footer {
            text-align: center;
            font-size: 5pt;
            color: #ccc;
            letter-spacing: 0.3px;
            font-family: 'DejaVu Sans Mono', 'Courier New', monospace;
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
    
    <table class="page-table">
        <tr>
            <td class="page-content">
                <div class="certificate-box">
                    <div class="certificate-inner">
                        @if($logoBase64)
                        <div class="watermark-bg">
                            <img src="{{ $logoBase64 }}" class="watermark-logo" alt="">
                        </div>
                        @endif
                        
                        <div class="certificate-content">
                            <div class="header-logos">
                                <table class="header-table">
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
                            
                            <div class="title-section">
                                <h1 class="title-main">Sertifikat Kompetensi</h1>
                                <p class="title-sub">Certificate of Competency</p>
                            </div>
                            
                            <div class="body-content">
                                <p class="intro-text">Diberikan kepada:</p>
                                <h2 class="recipient-name">{{ strtoupper($sertifikat->nama_peserta) }}</h2>
                                
                                <div class="info-row">
                                    <div class="info-label">Nomor Sertifikat:</div>
                                    <div class="info-value">{{ $sertifikat->nomor_sertifikat }}</div>
                                </div>
                                
                                <div class="info-row">
                                    <div class="info-label">Skema Sertifikasi:</div>
                                    <div class="info-value-large">{{ $sertifikat->skema_sertifikasi }}</div>
                                    @if(isset($pendaftaran) && $pendaftaran->skemaSertifikasi)
                                        <div class="info-code">({{ $pendaftaran->skemaSertifikasi->kode_skema ?? '' }})</div>
                                    @endif
                                </div>
                                
                                <div class="declaration-box">
                                    <div class="declaration-intro">Menyatakan bahwa yang bersangkutan</div>
                                    <div class="declaration-status">Telah Dinyatakan Kompeten</div>
                                </div>
                                
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
                            
                            <div class="footer-section">
                                <table class="footer-table">
                                    <tr>
                                        <td class="footer-qr">
                                            <div class="qr-wrapper">
                                                <img src="data:image/svg+xml;base64,{{ $qrCodeBase64 }}" class="qr-code-img" alt="QR">
                                                <div class="qr-text">Scan untuk<br>verifikasi</div>
                                            </div>
                                        </td>
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
</html>'''

with open('resources/views/pdf/sertifikat-bnsp.blade.php', 'w', encoding='utf-8') as f:
    f.write(template)

print("✅ Template TABLE-based V5 created successfully!")

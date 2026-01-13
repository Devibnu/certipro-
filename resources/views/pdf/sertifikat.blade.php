<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sertifikat - {{ $sertifikat->nomor_sertifikat }}</title>
    <style>
        @page {
            margin: 0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            background: #fff;
            color: #333;
        }
        
        .certificate-container {
            width: 100%;
            height: 100%;
            padding: 30px;
            position: relative;
        }
        
        .certificate-border {
            border: 8px solid #1a365d;
            border-radius: 10px;
            padding: 40px;
            height: calc(100% - 60px);
            position: relative;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        
        .certificate-border::before {
            content: '';
            position: absolute;
            top: 8px;
            left: 8px;
            right: 8px;
            bottom: 8px;
            border: 2px solid #c9a227;
            border-radius: 6px;
            pointer-events: none;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo-section {
            margin-bottom: 15px;
        }
        
        .logo-text {
            font-size: 28px;
            font-weight: bold;
            color: #1a365d;
            letter-spacing: 3px;
        }
        
        .logo-subtitle {
            font-size: 12px;
            color: #666;
            letter-spacing: 2px;
        }
        
        .certificate-title {
            font-size: 36px;
            font-weight: bold;
            color: #c9a227;
            text-transform: uppercase;
            letter-spacing: 8px;
            margin: 20px 0 10px 0;
        }
        
        .certificate-subtitle {
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 4px;
        }
        
        .certificate-number {
            font-size: 12px;
            color: #888;
            margin-top: 10px;
        }
        
        .content {
            text-align: center;
            margin: 30px 0;
        }
        
        .intro-text {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }
        
        .recipient-name {
            font-size: 32px;
            font-weight: bold;
            color: #1a365d;
            border-bottom: 3px solid #c9a227;
            display: inline-block;
            padding-bottom: 8px;
            margin-bottom: 20px;
        }
        
        .competency-text {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .scheme-name {
            font-size: 20px;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 25px;
            line-height: 1.4;
        }
        
        .validity-section {
            background: #f1f5f9;
            padding: 15px 30px;
            border-radius: 8px;
            display: inline-block;
            margin-bottom: 20px;
        }
        
        .validity-text {
            font-size: 12px;
            color: #666;
        }
        
        .validity-dates {
            font-size: 14px;
            font-weight: bold;
            color: #1a365d;
        }
        
        .footer {
            position: absolute;
            bottom: 50px;
            left: 50px;
            right: 50px;
            display: table;
            width: calc(100% - 100px);
        }
        
        .footer-left {
            display: table-cell;
            width: 25%;
            vertical-align: bottom;
            text-align: center;
        }
        
        .footer-center {
            display: table-cell;
            width: 50%;
            vertical-align: bottom;
            text-align: center;
        }
        
        .footer-right {
            display: table-cell;
            width: 25%;
            vertical-align: bottom;
            text-align: center;
        }
        
        .qr-code {
            margin-bottom: 5px;
        }
        
        .qr-code img {
            width: 80px;
            height: 80px;
        }
        
        .qr-text {
            font-size: 8px;
            color: #888;
        }
        
        .signature-section {
            text-align: center;
        }
        
        .signature-line {
            border-bottom: 2px solid #333;
            width: 180px;
            margin: 0 auto 8px auto;
        }
        
        .signature-name {
            font-size: 14px;
            font-weight: bold;
            color: #1a365d;
        }
        
        .signature-title {
            font-size: 11px;
            color: #666;
        }
        
        .issue-info {
            font-size: 10px;
            color: #888;
        }
        
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 100px;
            color: rgba(26, 54, 93, 0.03);
            font-weight: bold;
            letter-spacing: 20px;
            white-space: nowrap;
            pointer-events: none;
        }
        
        /* Security Watermark with UUID */
        .security-watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-35deg);
            font-size: 28px;
            color: rgba(100, 100, 100, 0.06);
            font-weight: bold;
            letter-spacing: 4px;
            white-space: nowrap;
            pointer-events: none;
            z-index: 1000;
            text-align: center;
        }
        
        .security-watermark-line {
            display: block;
            margin: 10px 0;
        }
        
        /* Secure ID footer label */
        .secure-id-label {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 8px;
            color: #aaa;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
    <!-- Security Watermark - appears on all pages -->
    @php
        $logoInfo = systemLogoInfo();
    @endphp
    <div class="security-watermark">
        @if($logoInfo['nama_perusahaan'])
            <span class="security-watermark-line">{{ strtoupper($logoInfo['nama_perusahaan']) }} | SECURE DOCUMENT</span>
        @else
            <span class="security-watermark-line">LSP | SECURE DOCUMENT</span>
        @endif
        <span class="security-watermark-line">{{ $sertifikat->uuid }}</span>
    </div>
    
    <div class="certificate-container">
        <div class="certificate-border">
            @if($logoInfo['nama_perusahaan'])
                <div class="watermark">{{ strtoupper($logoInfo['nama_perusahaan']) }}</div>
            @endif
            
            <!-- Secure ID Label at bottom -->
            <div class="secure-id-label">SECURE-ID: {{ $sertifikat->uuid }}</div>
            
            <div class="header">
                <div class="logo-section">
                    @if($logoInfo['nama_perusahaan'])
                        <div class="logo-text">{{ strtoupper($logoInfo['nama_perusahaan']) }}</div>
                    @endif
                    <div class="logo-subtitle">LEMBAGA SERTIFIKASI PROFESI</div>
                </div>
                <div class="certificate-title">SERTIFIKAT</div>
                <div class="certificate-subtitle">Certificate of Competency</div>
                <div class="certificate-number">No. {{ $sertifikat->nomor_sertifikat }}</div>
            </div>
            
            <div class="content">
                <p class="intro-text">Dengan ini menyatakan bahwa:</p>
                <div class="recipient-name">{{ $sertifikat->nama_peserta }}</div>
                <p class="competency-text">Telah dinyatakan <strong>KOMPETEN</strong> dalam:</p>
                <div class="scheme-name">{{ $sertifikat->skema_sertifikasi }}</div>
                
                <div class="validity-section">
                    <p class="validity-text">Masa Berlaku Sertifikat</p>
                    <p class="validity-dates">
                        {{ $sertifikat->tanggal_terbit->format('d F Y') }} - {{ $sertifikat->tanggal_berlaku_sampai->format('d F Y') }}
                    </p>
                </div>
            </div>
            
            <div class="footer">
                <div class="footer-left">
                    <div class="qr-code">
                        <img src="data:image/svg+xml;base64,{{ $qrCodeBase64 }}" alt="QR Code">
                    </div>
                    <p class="qr-text">Scan untuk verifikasi</p>
                </div>
                
                <div class="footer-center">
                    <div class="signature-section">
                        <div class="signature-line"></div>
                        <p class="signature-name">{{ $sertifikat->penerbit->name ?? 'Direktur LSP' }}</p>
                        @if($logoInfo['nama_perusahaan'])
                            <p class="signature-title">Direktur {{ $logoInfo['nama_perusahaan'] }}</p>
                        @else
                            <p class="signature-title">Direktur LSP</p>
                        @endif
                    </div>
                </div>
                
                <div class="footer-right">
                    <p class="issue-info">Diterbitkan di Jakarta</p>
                    <p class="issue-info">{{ $sertifikat->tanggal_terbit->format('d F Y') }}</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

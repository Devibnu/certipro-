<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Kompetensi Terbit</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #1a365d 0%, #2563eb 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            padding: 30px 20px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
            color: #1a365d;
        }
        .certificate-box {
            background-color: #f8fafc;
            border-left: 4px solid #2563eb;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .certificate-box h2 {
            margin: 0 0 15px 0;
            font-size: 18px;
            color: #1a365d;
        }
        .certificate-details {
            margin: 0;
            padding: 0;
        }
        .certificate-details li {
            list-style: none;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
        }
        .certificate-details li:last-child {
            border-bottom: none;
        }
        .certificate-details .label {
            font-weight: 600;
            color: #64748b;
            min-width: 150px;
        }
        .certificate-details .value {
            color: #1e293b;
            text-align: right;
        }
        .verification-section {
            background-color: #dcfce7;
            border: 2px solid #22c55e;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
        }
        .verification-section h3 {
            margin: 0 0 10px 0;
            color: #166534;
            font-size: 16px;
        }
        .verification-section p {
            margin: 10px 0;
            color: #15803d;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #2563eb;
            color: white !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 10px;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #1d4ed8;
        }
        .info-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box p {
            margin: 0;
            font-size: 14px;
            color: #78350f;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        .footer p {
            margin: 5px 0;
            font-size: 13px;
            color: #64748b;
        }
        .footer .contact {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
        }
        .qr-code {
            text-align: center;
            margin: 20px 0;
        }
        .qr-code img {
            max-width: 150px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px;
            background: white;
        }
        @media only screen and (max-width: 600px) {
            body {
                padding: 10px;
            }
            .content {
                padding: 20px 15px;
            }
            .certificate-details li {
                flex-direction: column;
                align-items: flex-start;
            }
            .certificate-details .value {
                text-align: left;
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>✅ Sertifikat Kompetensi Terbit</h1>
            <p>{{ $lspName }}</p>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Greeting -->
            <div class="greeting">
                <p>Kepada Yth. <strong>{{ $asesi->name }}</strong>,</p>
            </div>

            <!-- Main Message -->
            <p>
                Selamat! Kami dengan senang hati mengabarkan bahwa <strong>Sertifikat Kompetensi</strong> Anda 
                telah resmi diterbitkan oleh {{ $lspName }} sesuai dengan standar BNSP dan ISO/IEC 17024:2012.
            </p>

            <!-- Certificate Details -->
            <div class="certificate-box">
                <h2>📋 Detail Sertifikat</h2>
                <ul class="certificate-details">
                    <li>
                        <span class="label">Nomor Sertifikat:</span>
                        <span class="value"><strong>{{ $sertifikat->nomor_sertifikat }}</strong></span>
                    </li>
                    <li>
                        <span class="label">Nama Peserta:</span>
                        <span class="value">{{ $sertifikat->nama_peserta }}</span>
                    </li>
                    <li>
                        <span class="label">Skema Sertifikasi:</span>
                        <span class="value">{{ $skema->nama }}</span>
                    </li>
                    @if($skema->kode_skema)
                    <li>
                        <span class="label">Kode Skema:</span>
                        <span class="value">{{ $skema->kode_skema }}</span>
                    </li>
                    @endif
                    <li>
                        <span class="label">Tanggal Terbit:</span>
                        <span class="value">{{ $sertifikat->tanggal_terbit->format('d F Y') }}</span>
                    </li>
                    <li>
                        <span class="label">Berlaku Sampai:</span>
                        <span class="value">{{ $sertifikat->tanggal_berlaku_sampai->format('d F Y') }}</span>
                    </li>
                    <li>
                        <span class="label">Status:</span>
                        <span class="value"><strong style="color: #16a34a;">✓ VALID</strong></span>
                    </li>
                </ul>
            </div>

            <!-- Verification Section -->
            <div class="verification-section">
                <h3>🔐 Verifikasi Keaslian Sertifikat</h3>
                <p>Sertifikat Anda dapat diverifikasi secara online oleh siapa saja</p>
                <a href="{{ $verificationUrl }}" class="btn">Verifikasi Sekarang</a>
                <p style="margin-top: 15px; font-size: 12px;">
                    Atau scan QR Code yang terdapat pada sertifikat PDF
                </p>
            </div>

            <!-- Important Info -->
            <div class="info-box">
                <p>
                    <strong>📎 Lampiran:</strong> Sertifikat dalam format PDF telah dilampirkan pada email ini. 
                    Silakan simpan dengan baik untuk keperluan administrasi dan verifikasi.
                </p>
            </div>

            <!-- Additional Information -->
            <p style="margin-top: 25px; font-size: 14px; color: #64748b;">
                Sertifikat ini merupakan bukti bahwa Anda telah dinyatakan <strong>KOMPETEN</strong> 
                pada skema sertifikasi yang diujikan. Masa berlaku sertifikat adalah 
                <strong>{{ $sertifikat->tanggal_terbit->diffInYears($sertifikat->tanggal_berlaku_sampai) }} tahun</strong> 
                sejak tanggal terbit.
            </p>

            <!-- Call to Action -->
            <p style="margin-top: 25px; font-size: 14px;">
                Jika Anda memiliki pertanyaan atau memerlukan bantuan lebih lanjut, 
                jangan ragu untuk menghubungi kami melalui kontak di bawah ini.
            </p>

            <p style="margin-top: 30px; color: #1a365d;">
                <strong>Selamat dan sukses selalu!</strong><br>
                Salam Profesional,<br>
                <strong>{{ $lspName }}</strong>
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="font-weight: 600; color: #1a365d;">{{ $lspName }}</p>
            
            @if($lspAlamat)
            <p>📍 {{ $lspAlamat }}</p>
            @endif
            
            <div class="contact">
                @if($lspTelepon)
                <p>📞 {{ $lspTelepon }}</p>
                @endif
                
                @if($lspEmail)
                <p>📧 <a href="mailto:{{ $lspEmail }}" style="color: #2563eb; text-decoration: none;">{{ $lspEmail }}</a></p>
                @endif
                
                @if($lspWebsite)
                <p>🌐 <a href="{{ $lspWebsite }}" style="color: #2563eb; text-decoration: none;">{{ $lspWebsite }}</a></p>
                @endif
            </div>

            <p style="margin-top: 20px; font-size: 11px; color: #94a3b8;">
                Email ini dikirim secara otomatis. Mohon tidak membalas email ini.<br>
                Untuk pertanyaan, silakan hubungi kami melalui kontak resmi di atas.
            </p>
            
            <p style="margin-top: 15px; font-size: 11px; color: #94a3b8;">
                © {{ date('Y') }} {{ $lspName }}. All rights reserved.<br>
                Certified by BNSP (Badan Nasional Sertifikasi Profesi)
            </p>
        </div>
    </div>
</body>
</html>

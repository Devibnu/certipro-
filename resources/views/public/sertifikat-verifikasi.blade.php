<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php $logoInfo = systemLogoInfo(); $systemName = $logoInfo['nama_perusahaan']; $logoBase64 = systemLogoBase64(); @endphp
    <title>Verifikasi Sertifikat@if($systemName) - {{ $systemName }}@endif</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #1a365d 0%, #2d5a8b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            width: 100%;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            font-size: 32px;
            font-weight: 700;
            color: #fff;
            letter-spacing: 2px;
        }

        .logo-subtitle {
            color: rgba(255,255,255,0.7);
            font-size: 12px;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }

        .card-header {
            padding: 25px 30px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }

        .card-header h1 {
            font-size: 18px;
            color: #333;
            margin-bottom: 5px;
        }

        .card-header p {
            font-size: 13px;
            color: #888;
        }

        .card-body {
            padding: 30px;
        }

        /* Status Styles */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 25px;
        }

        .status-badge.valid {
            background: #d1fae5;
            color: #065f46;
        }

        .status-badge.invalid {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-badge.expired {
            background: #fef3c7;
            color: #92400e;
        }

        .status-badge.not-found {
            background: #f3f4f6;
            color: #6b7280;
        }

        .status-icon {
            font-size: 20px;
        }

        /* Certificate Info */
        .certificate-info {
            background: #f8fafc;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
        }

        .info-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            flex: 0 0 140px;
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            flex: 1;
            font-size: 14px;
            color: #1e293b;
            font-weight: 500;
        }

        .recipient-name {
            font-size: 22px;
            font-weight: 700;
            color: #1a365d;
            margin-bottom: 5px;
        }

        .scheme-name {
            font-size: 16px;
            color: #475569;
        }

        /* Alert Box */
        .alert-box {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            padding: 20px;
            border-radius: 12px;
            margin-top: 20px;
        }

        .alert-box.success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
        }

        .alert-box.warning {
            background: #fffbeb;
            border: 1px solid #fde68a;
        }

        .alert-box.error {
            background: #fef2f2;
            border: 1px solid #fecaca;
        }

        .alert-icon {
            font-size: 24px;
        }

        .alert-box.success .alert-icon { color: #10b981; }
        .alert-box.warning .alert-icon { color: #f59e0b; }
        .alert-box.error .alert-icon { color: #ef4444; }

        .alert-content h3 {
            font-size: 14px;
            margin-bottom: 4px;
        }

        .alert-content p {
            font-size: 13px;
            color: #64748b;
        }

        /* Not Found State */
        .not-found {
            text-align: center;
            padding: 40px 20px;
        }

        .not-found-icon {
            font-size: 64px;
            color: #e5e7eb;
            margin-bottom: 20px;
        }

        .not-found h2 {
            font-size: 20px;
            color: #374151;
            margin-bottom: 10px;
        }

        .not-found p {
            font-size: 14px;
            color: #6b7280;
            max-width: 400px;
            margin: 0 auto;
        }

        .searched-number {
            background: #f3f4f6;
            padding: 12px 20px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 14px;
            color: #374151;
            margin-top: 20px;
            display: inline-block;
        }

        /* Footer */
        .card-footer {
            padding: 20px 30px;
            background: #f8fafc;
            text-align: center;
            border-top: 1px solid #eee;
        }

        .card-footer p {
            font-size: 12px;
            color: #94a3b8;
        }

        .card-footer a {
            color: #3b82f6;
            text-decoration: none;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .info-row {
                flex-direction: column;
                gap: 4px;
            }

            .info-label {
                flex: none;
            }

            .recipient-name {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-section">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Logo" style="max-height: 50px; margin-bottom: 10px;">
            @elseif($systemName)
                <div class="logo">{{ strtoupper($systemName) }}</div>
            @else
                <div class="logo">LSP</div>
            @endif
            <div class="logo-subtitle">Lembaga Sertifikasi Profesi</div>
        </div>

        <div class="card">
            <div class="card-header">
                <h1><i class="fas fa-shield-halved me-2"></i>Verifikasi Sertifikat</h1>
                <p>Hasil verifikasi keaslian sertifikat</p>
            </div>

            <div class="card-body">
                @if($sertifikat)
                    <!-- Status Badge -->
                    <div style="text-align: center;">
                        @if($sertifikat->isValid())
                            <span class="status-badge valid">
                                <i class="fas fa-check-circle status-icon"></i>
                                SERTIFIKAT VALID
                            </span>
                        @else
                            <span class="status-badge expired">
                                <i class="fas fa-exclamation-triangle status-icon"></i>
                                SERTIFIKAT KADALUARSA
                            </span>
                        @endif
                    </div>

                    <!-- Certificate Info -->
                    <div class="certificate-info">
                        <div style="text-align: center; margin-bottom: 20px;">
                            <div class="recipient-name">{{ $sertifikat->nama_peserta }}</div>
                            <div class="scheme-name">{{ $sertifikat->skema_sertifikasi }}</div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">No. Sertifikat</div>
                            <div class="info-value">{{ $sertifikat->nomor_sertifikat }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Tanggal Terbit</div>
                            <div class="info-value">{{ $sertifikat->tanggal_terbit->format('d F Y') }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Berlaku Sampai</div>
                            <div class="info-value">{{ $sertifikat->tanggal_berlaku_sampai->format('d F Y') }}</div>
                        </div>
                        @if($sertifikat->pendaftaran?->skemaSertifikasi)
                        <div class="info-row">
                            <div class="info-label">Kode Skema</div>
                            <div class="info-value">{{ $sertifikat->pendaftaran->skemaSertifikasi->kode_skema }}</div>
                        </div>
                        @endif
                        <div class="info-row" style="background: #f0fdf4; margin: 0 -25px; padding: 12px 25px; border-radius: 8px;">
                            <div class="info-label" style="color: #166534;">Secure ID</div>
                            <div class="info-value" style="font-family: monospace; font-size: 12px; color: #166534; word-break: break-all;">{{ $sertifikat->uuid }}</div>
                        </div>
                    </div>

                    <!-- Alert Box -->
                    @if($sertifikat->isValid())
                        <div class="alert-box success">
                            <i class="fas fa-check-circle alert-icon"></i>
                            <div class="alert-content">
                                <h3>Sertifikat Terverifikasi</h3>
                                <p>Sertifikat ini terdaftar dan masih berlaku. Pemegang sertifikat telah dinyatakan KOMPETEN sesuai standar kompetensi yang berlaku.</p>
                            </div>
                        </div>
                    @else
                        <div class="alert-box warning">
                            <i class="fas fa-exclamation-triangle alert-icon"></i>
                            <div class="alert-content">
                                <h3>Sertifikat Sudah Kadaluarsa</h3>
                                <p>Sertifikat ini sudah melewati masa berlaku. Pemegang sertifikat perlu melakukan resertifikasi untuk memperbarui status kompetensinya.</p>
                            </div>
                        </div>
                    @endif

                @else
                    <!-- Not Found State -->
                    <div class="not-found">
                        <i class="fas fa-file-circle-xmark not-found-icon"></i>
                        <h2>Sertifikat Tidak Ditemukan</h2>
                        <p>Nomor sertifikat yang Anda cari tidak terdaftar dalam sistem kami. Pastikan nomor sertifikat sudah benar.</p>
                        <div class="searched-number">{{ $nomor_sertifikat }}</div>
                    </div>

                    <div class="alert-box error">
                        <i class="fas fa-times-circle alert-icon"></i>
                        <div class="alert-content">
                            <h3>Verifikasi Gagal</h3>
                            <p>Jika Anda yakin nomor sertifikat sudah benar, silakan hubungi kami untuk konfirmasi lebih lanjut.</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="card-footer">
                <p>
                    &copy; {{ date('Y') }} {{ $systemName ?? 'LSP' }} - Lembaga Sertifikasi Profesi<br>
                    <a href="{{ url('/') }}">{{ request()->getHost() }}</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php $logoInfo = systemLogoInfo(); $systemName = $logoInfo['nama_perusahaan']; $logoBase64 = systemLogoBase64(); @endphp
    <title>Cari & Verifikasi Sertifikat{{ $systemName ? ' - ' . $systemName : '' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
            padding: 40px 20px;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 32px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 10px;
        }

        .logo i {
            font-size: 40px;
            color: #fbbf24;
        }

        .logo-subtitle {
            color: rgba(255,255,255,0.7);
            font-size: 14px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .search-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
            padding: 40px;
            margin-bottom: 30px;
        }

        .search-card h2 {
            font-size: 24px;
            color: #1a365d;
            margin-bottom: 10px;
            text-align: center;
        }

        .search-card p {
            color: #666;
            text-align: center;
            margin-bottom: 30px;
        }

        .search-form {
            display: flex;
            gap: 10px;
        }

        .search-form input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
        }

        .search-form input:focus {
            outline: none;
            border-color: #5e72e4;
            box-shadow: 0 0 0 4px rgba(94, 114, 228, 0.1);
        }

        .search-form button {
            padding: 15px 30px;
            background: linear-gradient(135deg, #5e72e4 0%, #825ee4 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .search-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(94, 114, 228, 0.3);
        }

        .result-card {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
        }

        .result-header {
            padding: 25px 30px;
            text-align: center;
        }

        .result-header.valid {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .result-header.expired {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .result-header.not-found {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .result-header h3 {
            font-size: 20px;
            margin-bottom: 5px;
        }

        .result-body {
            padding: 30px;
        }

        .info-grid {
            display: grid;
            gap: 15px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #64748b;
            font-size: 14px;
        }

        .info-value {
            font-weight: 600;
            color: #1e293b;
            text-align: right;
        }

        .badge-valid {
            background: #d1fae5;
            color: #065f46;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-expired {
            background: #fef3c7;
            color: #92400e;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .footer-section {
            text-align: center;
            margin-top: 40px;
            color: rgba(255,255,255,0.7);
        }

        .footer-section a {
            color: #fbbf24;
            text-decoration: none;
        }

        .not-found-content {
            text-align: center;
            padding: 40px;
        }

        .not-found-content i {
            font-size: 60px;
            color: #ef4444;
            margin-bottom: 20px;
        }

        @media (max-width: 576px) {
            .search-form {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Logo -->
        <div class="logo-section">
            <div class="logo">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo" style="max-height: 50px;">
                @else
                    <i class="fas fa-certificate"></i>
                @endif
                @if($systemName)
                    {{ $systemName }}
                @endif
            </div>
            <div class="logo-subtitle">Lembaga Sertifikasi Profesi</div>
        </div>

        <!-- Search Card -->
        <div class="search-card">
            <h2><i class="fas fa-search me-2"></i>Verifikasi Sertifikat</h2>
            <p>Masukkan nomor sertifikat atau UUID untuk memverifikasi keaslian sertifikat</p>
            
            <form action="{{ route('sertifikat.search.public') }}" method="GET" class="search-form">
                <input type="text" name="nomor" placeholder="Masukkan nomor sertifikat atau UUID..." 
                       value="{{ $query ?? '' }}" required>
                <button type="submit">
                    <i class="fas fa-search me-2"></i>Cari
                </button>
            </form>
        </div>

        @if($searched ?? false)
            @if($sertifikat)
                @php
                    $isValid = $sertifikat->isValid();
                    $headerClass = $isValid ? 'valid' : 'expired';
                @endphp
                <!-- Result Found -->
                <div class="result-card">
                    <div class="result-header {{ $headerClass }}">
                        <i class="fas {{ $isValid ? 'fa-check-circle' : 'fa-clock' }} fa-2x mb-2"></i>
                        <h3>{{ $isValid ? 'Sertifikat Valid' : 'Sertifikat Kadaluarsa' }}</h3>
                        <p class="mb-0">Nomor: {{ $sertifikat->nomor_sertifikat }}</p>
                    </div>
                    <div class="result-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Nomor Sertifikat</span>
                                <span class="info-value">{{ $sertifikat->nomor_sertifikat }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Nama Pemegang</span>
                                <span class="info-value">{{ $sertifikat->nama_peserta }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Skema Sertifikasi</span>
                                <span class="info-value">{{ $sertifikat->skema_sertifikasi }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Tanggal Terbit</span>
                                <span class="info-value">{{ $sertifikat->tanggal_terbit->format('d F Y') }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Berlaku Sampai</span>
                                <span class="info-value">
                                    {{ $sertifikat->tanggal_berlaku_sampai->format('d F Y') }}
                                    <span class="{{ $isValid ? 'badge-valid' : 'badge-expired' }} ms-2">
                                        {{ $isValid ? 'Berlaku' : 'Kadaluarsa' }}
                                    </span>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Diterbitkan Oleh</span>
                                <span class="info-value">{{ $sertifikat->penerbit->name ?? ($systemName ?? 'LSP') }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">UUID</span>
                                <span class="info-value text-muted" style="font-size: 11px; word-break: break-all;">
                                    {{ $sertifikat->uuid }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Not Found -->
                <div class="result-card">
                    <div class="result-header not-found">
                        <i class="fas fa-times-circle fa-2x mb-2"></i>
                        <h3>Sertifikat Tidak Ditemukan</h3>
                        <p class="mb-0">Tidak ada data yang cocok</p>
                    </div>
                    <div class="not-found-content">
                        <i class="fas fa-file-circle-xmark"></i>
                        <h4>Tidak Ditemukan</h4>
                        <p class="text-muted">
                            Sertifikat dengan nomor/UUID <strong>"{{ $query }}"</strong> tidak ditemukan dalam database kami.
                        </p>
                        <div class="alert alert-warning text-start mt-4">
                            <strong><i class="fas fa-exclamation-triangle me-2"></i>Kemungkinan:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Nomor sertifikat salah ketik</li>
                                <li>Sertifikat belum diterbitkan</li>
                                <li>Sertifikat tidak valid / palsu</li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        <!-- Footer -->
        <div class="footer-section">
            <p>
                <i class="fas fa-shield-alt me-2"></i>
                Verifikasi resmi oleh <strong>{{ $systemName ?? 'LSP' }}</strong>
            </p>
            <p class="mt-2">
                <a href="{{ url('/') }}"><i class="fas fa-home me-1"></i>Kembali ke Beranda</a>
                &nbsp;|&nbsp;
                <a href="mailto:info@lsp.id"><i class="fas fa-envelope me-1"></i>Hubungi Kami</a>
            </p>
            <p class="mt-3 small">
                &copy; {{ date('Y') }} {{ $systemName ?? 'LSP' }}. All rights reserved.
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

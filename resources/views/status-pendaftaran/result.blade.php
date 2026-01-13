<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php $systemName = systemCompanyName(); $logoBase64 = systemLogoBase64(); @endphp
    <title>Status Pendaftaran - {{ $pendaftaran->nomor_pendaftaran }}{{ $systemName ? ' - ' . $systemName : '' }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1a365d 0%, #2c5282 50%, #1a365d 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .header {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #fff;
            text-decoration: none;
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            background: #fff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1a365d;
            font-weight: 700;
            font-size: 1.25rem;
        }
        
        .logo-text {
            font-weight: 700;
            font-size: 1.25rem;
        }
        
        .nav-links a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            margin-left: 2rem;
            font-size: 0.875rem;
            transition: color 0.2s;
        }
        
        .nav-links a:hover {
            color: #fff;
        }
        
        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .result-container {
            width: 100%;
            max-width: 600px;
        }
        
        .result-card {
            background: #fff;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        }
        
        /* Status Header */
        .status-header {
            padding: 2rem;
            text-align: center;
            color: #fff;
        }
        
        .status-header.DALAM_PROSES { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .status-header.DITERIMA { background: linear-gradient(135deg, #0ea5e9, #0284c7); }
        .status-header.DITOLAK { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .status-header.DIJADWALKAN_ASESMEN { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .status-header.SEDANG_ASESMEN { background: linear-gradient(135deg, #6366f1, #4f46e5); }
        .status-header.MENUNGGU_KEPUTUSAN { background: linear-gradient(135deg, #64748b, #475569); }
        .status-header.LULUS_SERTIFIKASI { background: linear-gradient(135deg, #10b981, #059669); }
        .status-header.TIDAK_LULUS { background: linear-gradient(135deg, #ef4444, #dc2626); }
        
        .status-icon {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
        }
        
        .status-label {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .status-message {
            font-size: 0.875rem;
            opacity: 0.9;
            line-height: 1.5;
            max-width: 400px;
            margin: 0 auto;
        }
        
        /* Content */
        .result-content {
            padding: 2rem;
        }
        
        .info-section {
            margin-bottom: 1.5rem;
        }
        
        .info-section-title {
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: #64748b;
            font-size: 0.875rem;
        }
        
        .info-value {
            color: #1e293b;
            font-weight: 500;
            font-size: 0.875rem;
            text-align: right;
        }
        
        /* Timeline */
        .timeline {
            position: relative;
            padding-left: 2rem;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 7px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e2e8f0;
        }
        
        .timeline-item {
            position: relative;
            padding-bottom: 1.5rem;
        }
        
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        
        .timeline-dot {
            position: absolute;
            left: -2rem;
            top: 0;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #e2e8f0;
            border: 3px solid #fff;
            box-shadow: 0 0 0 2px #e2e8f0;
        }
        
        .timeline-dot.active {
            background: #10b981;
            box-shadow: 0 0 0 2px #10b981;
        }
        
        .timeline-dot.current {
            background: #3b82f6;
            box-shadow: 0 0 0 2px #3b82f6;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 2px #3b82f6; }
            50% { box-shadow: 0 0 0 6px rgba(59,130,246,0.3); }
        }
        
        .timeline-content h4 {
            font-size: 0.875rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }
        
        .timeline-content p {
            font-size: 0.75rem;
            color: #64748b;
        }
        
        /* Actions */
        .result-actions {
            padding: 1.5rem 2rem;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 1rem;
        }
        
        .btn {
            flex: 1;
            padding: 0.875rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #1a365d, #2c5282);
            color: #fff;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(26,54,93,0.3);
        }
        
        .btn-outline {
            background: #fff;
            color: #1a365d;
            border: 2px solid #1a365d;
        }
        
        .btn-outline:hover {
            background: #1a365d;
            color: #fff;
        }
        
        /* Certificate Link */
        .certificate-link {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            padding: 1rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            margin-top: 1rem;
            transition: all 0.2s;
        }
        
        .certificate-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(16,185,129,0.3);
        }
        
        .certificate-link i {
            font-size: 1.5rem;
        }
        
        .certificate-link-text {
            flex: 1;
        }
        
        .certificate-link-text strong {
            display: block;
            margin-bottom: 0.25rem;
        }
        
        .certificate-link-text span {
            font-size: 0.75rem;
            opacity: 0.9;
        }
        
        .footer {
            background: rgba(0,0,0,0.2);
            padding: 1rem 2rem;
            text-align: center;
        }
        
        .footer p {
            color: rgba(255,255,255,0.6);
            font-size: 0.75rem;
        }
        
        .bnsp-badge {
            display: inline-block;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.625rem;
            color: #fff;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-left: 0.5rem;
        }
        
        @media (max-width: 640px) {
            .header {
                flex-direction: column;
                gap: 1rem;
            }
            
            .nav-links a {
                margin-left: 1rem;
            }
            
            .result-actions {
                flex-direction: column;
            }
            
            .info-row {
                flex-direction: column;
                gap: 0.25rem;
            }
            
            .info-value {
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="/" class="logo">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Logo" style="max-height: 40px;">
            @else
                <div class="logo-icon">LSP</div>
            @endif
            @if($systemName)
                <span class="logo-text">{{ $systemName }}</span>
            @endif
            <span class="bnsp-badge">BNSP</span>
        </a>
        <nav class="nav-links">
            <a href="/">Beranda</a>
            <a href="/verifikasi">Verifikasi Sertifikat</a>
            <a href="/login">Login</a>
        </nav>
    </header>
    
    <main class="main-content">
        <div class="result-container">
            <div class="result-card">
                <!-- Status Header -->
                <div class="status-header {{ $pendaftaran->status_peserta }}">
                    <div class="status-icon">
                        @switch($pendaftaran->status_peserta)
                            @case('DALAM_PROSES')
                                <i class="fas fa-clock"></i>
                                @break
                            @case('DITERIMA')
                                <i class="fas fa-check-circle"></i>
                                @break
                            @case('DITOLAK')
                                <i class="fas fa-times-circle"></i>
                                @break
                            @case('DIJADWALKAN_ASESMEN')
                                <i class="fas fa-calendar-check"></i>
                                @break
                            @case('SEDANG_ASESMEN')
                                <i class="fas fa-clipboard-list"></i>
                                @break
                            @case('MENUNGGU_KEPUTUSAN')
                                <i class="fas fa-hourglass-half"></i>
                                @break
                            @case('LULUS_SERTIFIKASI')
                                <i class="fas fa-trophy"></i>
                                @break
                            @case('TIDAK_LULUS')
                                <i class="fas fa-times-circle"></i>
                                @break
                            @default
                                <i class="fas fa-info-circle"></i>
                        @endswitch
                    </div>
                    <div class="status-label">{{ $pendaftaran->status_peserta_label }}</div>
                    <p class="status-message">
                        {{ $pendaftaran->pesan_status_peserta ?? \App\Models\PendaftaranSertifikasi::getStatusPesertaMessage($pendaftaran->status_peserta) }}
                    </p>
                </div>
                
                <!-- Content -->
                <div class="result-content">
                    <div class="info-section">
                        <h3 class="info-section-title">
                            <i class="fas fa-file-alt"></i> Informasi Pendaftaran
                        </h3>
                        <div class="info-row">
                            <span class="info-label">Nomor Pendaftaran</span>
                            <span class="info-value">{{ $pendaftaran->nomor_pendaftaran }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Nama Peserta</span>
                            <span class="info-value">{{ $pendaftaran->asesi_name }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Email</span>
                            <span class="info-value">{{ $pendaftaran->asesi_email }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Skema Sertifikasi</span>
                            <span class="info-value">{{ $pendaftaran->skemaSertifikasi->nama_skema ?? '-' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Tanggal Pendaftaran</span>
                            <span class="info-value">{{ $pendaftaran->tanggal_daftar->format('d F Y') }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Terakhir Diperbarui</span>
                            <span class="info-value">
                                {{ ($pendaftaran->status_peserta_updated_at ?? $pendaftaran->updated_at)->format('d F Y, H:i') }} WIB
                            </span>
                        </div>
                    </div>
                    
                    <!-- Progress Timeline -->
                    <div class="info-section">
                        <h3 class="info-section-title">
                            <i class="fas fa-tasks"></i> Progress Sertifikasi
                        </h3>
                        @php
                            $steps = [
                                ['status' => 'DALAM_PROSES', 'label' => 'Pendaftaran', 'desc' => 'Pengajuan diterima'],
                                ['status' => 'DITERIMA', 'label' => 'Verifikasi', 'desc' => 'Dokumen diverifikasi'],
                                ['status' => 'DIJADWALKAN_ASESMEN', 'label' => 'Penjadwalan', 'desc' => 'Asesmen dijadwalkan'],
                                ['status' => 'SEDANG_ASESMEN', 'label' => 'Asesmen', 'desc' => 'Proses asesmen'],
                                ['status' => 'MENUNGGU_KEPUTUSAN', 'label' => 'Keputusan', 'desc' => 'Menunggu keputusan'],
                                ['status' => 'LULUS_SERTIFIKASI', 'label' => 'Selesai', 'desc' => 'Sertifikasi selesai'],
                            ];
                            
                            $statusOrder = [
                                'DALAM_PROSES' => 1,
                                'DITERIMA' => 2,
                                'DITOLAK' => 0,
                                'DIJADWALKAN_ASESMEN' => 3,
                                'SEDANG_ASESMEN' => 4,
                                'MENUNGGU_KEPUTUSAN' => 5,
                                'LULUS_SERTIFIKASI' => 6,
                                'TIDAK_LULUS' => 5,
                            ];
                            
                            $currentOrder = $statusOrder[$pendaftaran->status_peserta] ?? 1;
                        @endphp
                        
                        <div class="timeline">
                            @foreach($steps as $index => $step)
                                @php
                                    $stepOrder = $statusOrder[$step['status']] ?? ($index + 1);
                                    $isActive = $currentOrder > $stepOrder;
                                    $isCurrent = $currentOrder == $stepOrder;
                                @endphp
                                <div class="timeline-item">
                                    <div class="timeline-dot {{ $isActive ? 'active' : '' }} {{ $isCurrent ? 'current' : '' }}"></div>
                                    <div class="timeline-content">
                                        <h4>{{ $step['label'] }}</h4>
                                        <p>{{ $step['desc'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Certificate Link (if passed) -->
                    @if($pendaftaran->status_peserta === 'LULUS_SERTIFIKASI' && $pendaftaran->sertifikat)
                        <a href="{{ route('verifikasi.show', $pendaftaran->sertifikat->nomor_sertifikat) }}" class="certificate-link">
                            <i class="fas fa-certificate"></i>
                            <div class="certificate-link-text">
                                <strong>Lihat Sertifikat Anda</strong>
                                <span>No. {{ $pendaftaran->sertifikat->nomor_sertifikat }}</span>
                            </div>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    @endif
                </div>
                
                <!-- Actions -->
                <div class="result-actions">
                    <a href="{{ route('status-pendaftaran.index') }}" class="btn btn-outline">
                        <i class="fas fa-search"></i> Cek Lagi
                    </a>
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fas fa-print"></i> Cetak
                    </button>
                </div>
            </div>
        </div>
    </main>
    
    <footer class="footer">
        <p>&copy; {{ date('Y') }} {{ $systemName ?? 'LSP' }}. Sistem Sertifikasi Profesi Terakreditasi BNSP.</p>
    </footer>
</body>
</html>

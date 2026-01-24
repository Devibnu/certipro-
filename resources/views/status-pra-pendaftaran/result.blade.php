<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $pageCompanyName = systemCompanyName();
        $pageFaviconUrl = systemLogoUrl();
    @endphp
    <title>Status Pra-Pendaftaran - {{ $praPendaftaran->nomor_pra_pendaftaran }} - {{ $pageCompanyName }}</title>
    @if($pageFaviconUrl)
    <link rel="icon" type="image/png" href="{{ $pageFaviconUrl }}">
    @endif
    
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
            background: linear-gradient(135deg, #0f766e 0%, #0d9488 50%, #14b8a6 100%);
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
            color: #0f766e;
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
            max-width: 650px;
        }
        
        .result-card {
            background: #fff;
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        }
        
        /* Status Header */
        .status-header {
            padding: 2rem 2.5rem;
            text-align: center;
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        
        .status-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 50%);
        }
        
        .status-header.baru { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .status-header.diproses { background: linear-gradient(135deg, #0ea5e9, #0284c7); }
        .status-header.diterima { background: linear-gradient(135deg, #10b981, #059669); }
        .status-header.ditolak { background: linear-gradient(135deg, #ef4444, #dc2626); }
        
        .status-icon {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            font-size: 2.25rem;
            position: relative;
            z-index: 1;
        }
        
        .status-label {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }
        
        .status-message {
            font-size: 0.875rem;
            opacity: 0.95;
            line-height: 1.6;
            max-width: 450px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }
        
        /* Timeline Stepper */
        .timeline-stepper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2rem 2.5rem;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            flex: 1;
            position: relative;
        }
        
        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 18px;
            left: calc(50% + 25px);
            width: calc(100% - 50px);
            height: 3px;
            background: #e2e8f0;
            z-index: 0;
        }
        
        .step.completed:not(:last-child)::after {
            background: #10b981;
        }
        
        .step.active:not(:last-child)::after {
            background: linear-gradient(90deg, #10b981 0%, #e2e8f0 100%);
        }
        
        .step-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
            background: #fff;
            border: 3px solid #e2e8f0;
            color: #94a3b8;
        }
        
        .step.completed .step-circle {
            background: #10b981;
            border-color: #10b981;
            color: #fff;
        }
        
        .step.active .step-circle {
            background: #fff;
            border-color: #f59e0b;
            color: #f59e0b;
            animation: pulse-ring 1.5s infinite;
        }
        
        .step.rejected .step-circle {
            background: #ef4444;
            border-color: #ef4444;
            color: #fff;
        }
        
        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
            70% { box-shadow: 0 0 0 8px rgba(245, 158, 11, 0); }
            100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
        }
        
        .step-label {
            font-size: 0.7rem;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .step.completed .step-label,
        .step.active .step-label {
            color: #334155;
        }
        
        .step.rejected .step-label {
            color: #dc2626;
        }
        
        /* Content */
        .result-content {
            padding: 2rem 2.5rem;
        }
        
        .info-section {
            margin-bottom: 1.75rem;
        }
        
        .info-section-title {
            font-size: 0.7rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .info-section-title i {
            color: #0f766e;
        }
        
        .info-grid {
            display: grid;
            gap: 1rem;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 0.875rem 1rem;
            background: #f8fafc;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
        }
        
        .info-label {
            color: #64748b;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .info-label i {
            color: #94a3b8;
            width: 16px;
            text-align: center;
        }
        
        .info-value {
            color: #1e293b;
            font-weight: 600;
            font-size: 0.875rem;
            text-align: right;
        }
        
        .info-value.mono {
            font-family: 'SF Mono', 'Fira Code', monospace;
            letter-spacing: 0.5px;
        }
        
        /* Rejection Reason Box */
        .rejection-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-left: 4px solid #ef4444;
            border-radius: 0.75rem;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }
        
        .rejection-box-title {
            font-size: 0.75rem;
            font-weight: 700;
            color: #dc2626;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .rejection-box-content {
            color: #7f1d1d;
            font-size: 0.875rem;
            line-height: 1.6;
            background: #fff;
            padding: 1rem;
            border-radius: 0.5rem;
            border: 1px solid #fecaca;
        }
        
        /* Action Buttons */
        .action-section {
            padding: 1.5rem 2.5rem 2rem;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
        }
        
        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
            width: 100%;
            margin-bottom: 0.75rem;
        }
        
        .btn:last-child {
            margin-bottom: 0;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 4px 14px -3px rgba(16, 185, 129, 0.4);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #059669, #047857);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px -3px rgba(16, 185, 129, 0.5);
        }
        
        .btn-secondary {
            background: #fff;
            color: #475569;
            border: 2px solid #e2e8f0;
        }
        
        .btn-secondary:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }
        
        .btn-search {
            background: linear-gradient(135deg, #0f766e, #14b8a6);
            color: white;
        }
        
        .btn-search:hover {
            background: linear-gradient(135deg, #0d9488, #0f766e);
            transform: translateY(-2px);
        }
        
        /* Success Box for Diterima */
        .success-box {
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-left: 4px solid #10b981;
            border-radius: 0.75rem;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }
        
        .success-box-title {
            font-size: 0.75rem;
            font-weight: 700;
            color: #059669;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .success-box-content {
            color: #166534;
            font-size: 0.875rem;
            line-height: 1.6;
        }
        
        /* Footer */
        .footer {
            text-align: center;
            padding: 1.5rem;
            color: rgba(255,255,255,0.7);
            font-size: 0.75rem;
        }
        
        /* Print Button */
        .print-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255,255,255,0.2);
            color: #fff;
            border: none;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }
        
        .print-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        @media print {
            body {
                background: #fff;
            }
            
            .header, .footer, .print-btn, .action-section {
                display: none !important;
            }
            
            .result-card {
                box-shadow: none;
                border: 1px solid #e2e8f0;
            }
        }
        
        @media (max-width: 640px) {
            .header {
                padding: 1rem;
            }
            
            .nav-links {
                display: none;
            }
            
            .main-content {
                padding: 1rem;
            }
            
            .status-header {
                padding: 1.5rem;
            }
            
            .status-icon {
                width: 60px;
                height: 60px;
                font-size: 1.75rem;
            }
            
            .status-label {
                font-size: 1.25rem;
            }
            
            .timeline-stepper {
                padding: 1.5rem 1rem;
            }
            
            .step-label {
                font-size: 0.6rem;
            }
            
            .result-content,
            .action-section {
                padding: 1.5rem;
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
    <!-- Header -->
    <header class="header">
        @php
            $logoUrl = systemLogoUrl();
            $companyName = systemCompanyName();
        @endphp
        <a href="{{ url('/') }}" class="logo">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="Logo" style="height: 40px; object-fit: contain;">
            @else
                <div class="logo-icon">L</div>
            @endif
            @if($companyName)
                <span class="logo-text">{{ $companyName }}</span>
            @endif
        </a>
        <nav class="nav-links">
            <a href="{{ url('/') }}">Beranda</a>
            <a href="{{ route('daftar') }}">Daftar</a>
            <a href="{{ route('verifikasi.index') }}">Verifikasi Sertifikat</a>
        </nav>
    </header>
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="result-container">
            <div class="result-card">
                <!-- Status Header -->
                <div class="status-header {{ $praPendaftaran->status }}" style="position: relative;">
                    <button class="print-btn" onclick="window.print()">
                        <i class="fas fa-print"></i>
                        <span>Cetak</span>
                    </button>
                    
                    <div class="status-icon">
                        @if($praPendaftaran->status === 'baru')
                            <i class="fas fa-clock"></i>
                        @elseif($praPendaftaran->status === 'diproses')
                            <i class="fas fa-spinner fa-spin"></i>
                        @elseif($praPendaftaran->status === 'diterima')
                            <i class="fas fa-check-circle"></i>
                        @elseif($praPendaftaran->status === 'ditolak')
                            <i class="fas fa-times-circle"></i>
                        @else
                            <i class="fas fa-question-circle"></i>
                        @endif
                    </div>
                    
                    <div class="status-label">{{ $praPendaftaran->status_public_label }}</div>
                    <p class="status-message">{{ $praPendaftaran->status_message }}</p>
                </div>
                
                <!-- Timeline Stepper -->
                <div class="timeline-stepper">
                    @php
                        $currentStep = $praPendaftaran->timeline_step;
                        $isRejected = $praPendaftaran->status === 'ditolak';
                    @endphp
                    
                    <!-- Step 1: Pra-Pendaftaran -->
                    <div class="step {{ $currentStep >= 1 ? 'completed' : '' }}">
                        <div class="step-circle">
                            @if($currentStep >= 1)
                                <i class="fas fa-check"></i>
                            @else
                                1
                            @endif
                        </div>
                        <span class="step-label">Pra-Pendaftaran</span>
                    </div>
                    
                    <!-- Step 2: Verifikasi -->
                    <div class="step {{ $currentStep >= 2 ? ($currentStep == 2 ? 'active' : 'completed') : '' }}">
                        <div class="step-circle">
                            @if($currentStep > 2)
                                <i class="fas fa-check"></i>
                            @elseif($currentStep == 2)
                                <i class="fas fa-spinner fa-spin"></i>
                            @else
                                2
                            @endif
                        </div>
                        <span class="step-label">Verifikasi</span>
                    </div>
                    
                    <!-- Step 3: Keputusan -->
                    <div class="step {{ $currentStep >= 3 ? ($isRejected ? 'rejected' : 'completed') : '' }}">
                        <div class="step-circle">
                            @if($isRejected)
                                <i class="fas fa-times"></i>
                            @elseif($currentStep >= 3)
                                <i class="fas fa-check"></i>
                            @else
                                3
                            @endif
                        </div>
                        <span class="step-label">Keputusan</span>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="result-content">
                    <!-- Info Section -->
                    <div class="info-section">
                        <div class="info-section-title">
                            <i class="fas fa-user"></i>
                            <span>Informasi Peserta</span>
                        </div>
                        
                        <div class="info-grid">
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-hashtag"></i>
                                    <span>Nomor Pra-Pendaftaran</span>
                                </div>
                                <div class="info-value mono">{{ $praPendaftaran->nomor_pra_pendaftaran }}</div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-user"></i>
                                    <span>Nama Lengkap</span>
                                </div>
                                <div class="info-value">{{ $praPendaftaran->nama_lengkap }}</div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-envelope"></i>
                                    <span>Email</span>
                                </div>
                                <div class="info-value">{{ $praPendaftaran->email }}</div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-calendar"></i>
                                    <span>Tanggal Daftar</span>
                                </div>
                                <div class="info-value">{{ $praPendaftaran->created_at->format('d F Y, H:i') }} WIB</div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-building"></i>
                                    <span>Tipe Peserta</span>
                                </div>
                                <div class="info-value">{{ $praPendaftaran->tipe_peserta_label }}</div>
                            </div>
                            
                            @if($praPendaftaran->institusi)
                            <div class="info-row">
                                <div class="info-label">
                                    <i class="fas fa-university"></i>
                                    <span>Institusi</span>
                                </div>
                                <div class="info-value">{{ $praPendaftaran->institusi }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Rejection Reason (if rejected) -->
                    @if($praPendaftaran->status === 'ditolak' && $praPendaftaran->alasan_penolakan)
                    <div class="rejection-box">
                        <div class="rejection-box-title">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Alasan Penolakan</span>
                        </div>
                        <div class="rejection-box-content">
                            {{ $praPendaftaran->alasan_penolakan }}
                        </div>
                    </div>
                    @endif
                    
                    <!-- Success Message (if accepted) -->
                    @if($praPendaftaran->status === 'diterima')
                    <div class="success-box">
                        <div class="success-box-title">
                            <i class="fas fa-info-circle"></i>
                            <span>Langkah Selanjutnya</span>
                        </div>
                        <div class="success-box-content">
                            @if($praPendaftaran->canProceedToSertifikasi())
                                Pendaftaran pra-sertifikasi Anda telah diterima. Silakan lanjutkan ke tahap <strong>Pendaftaran Sertifikasi</strong> untuk memilih skema kompetensi dan jadwal asesmen yang tersedia.
                            @else
                                Anda telah melanjutkan ke tahap Pendaftaran Sertifikasi. Silakan cek status pendaftaran sertifikasi Anda untuk informasi lebih lanjut.
                            @endif
                        </div>
                    </div>
                    @endif
                    
                    <!-- Waiting Info -->
                    @if(in_array($praPendaftaran->status, ['baru', 'diproses']))
                    <div class="success-box" style="background: #fefce8; border-color: #fde047; border-left-color: #eab308;">
                        <div class="success-box-title" style="color: #a16207;">
                            <i class="fas fa-clock"></i>
                            <span>Estimasi Waktu</span>
                        </div>
                        <div class="success-box-content" style="color: #854d0e;">
                            Proses verifikasi memerlukan waktu <strong>1-3 hari kerja</strong>. Anda akan menerima notifikasi melalui email setelah proses verifikasi selesai.
                        </div>
                    </div>
                    @endif
                </div>
                
                <!-- Action Section -->
                <div class="action-section">
                    @if($praPendaftaran->status === 'diterima')
                        @php
                            $pendaftaran = $praPendaftaran->pendaftaranSertifikasi;
                            $daftarUrl = $pendaftaran 
                                ? route('pendaftaran-sertifikasi.show', $pendaftaran->id) 
                                : route('daftar');
                            $daftarLabel = $pendaftaran 
                                ? 'Lengkapi Pendaftaran Sertifikasi' 
                                : 'Lanjut Daftar Sertifikasi';
                        @endphp
                        <a href="{{ $daftarUrl }}" class="btn btn-primary">
                            <i class="fas fa-arrow-right"></i>
                            <span>{{ $daftarLabel }}</span>
                        </a>
                    @endif
                    
                    <a href="{{ route('status-pra-pendaftaran.index') }}" class="btn btn-search">
                        <i class="fas fa-search"></i>
                        <span>Cek Status Lainnya</span>
                    </a>
                    
                    <a href="{{ url('/') }}" class="btn btn-secondary">
                        <i class="fas fa-home"></i>
                        <span>Kembali ke Beranda</span>
                    </a>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="footer">
        <p>
            &copy; {{ date('Y') }} @if(systemCompanyName()){{ systemCompanyName() }}.@endif 
            Sistem Sertifikasi Terakreditasi BNSP | ISO 17024
        </p>
    </footer>
</body>
</html>

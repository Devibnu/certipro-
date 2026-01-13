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
    <title>Cek Status Pra-Pendaftaran - {{ $pageCompanyName }}</title>
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
        
        .search-container {
            width: 100%;
            max-width: 500px;
        }
        
        .search-card {
            background: #fff;
            border-radius: 1.5rem;
            padding: 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        }
        
        .search-icon-wrapper {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #0f766e, #14b8a6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
        
        .search-icon-wrapper i {
            font-size: 2rem;
            color: #fff;
        }
        
        .search-title {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f766e;
            margin-bottom: 0.5rem;
        }
        
        .search-subtitle {
            text-align: center;
            color: #64748b;
            font-size: 0.875rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }
        
        .form-input {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            font-size: 1rem;
            transition: all 0.2s;
            font-family: 'SF Mono', 'Fira Code', monospace;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #14b8a6;
            box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.1);
        }
        
        .form-input::placeholder {
            color: #94a3b8;
            font-family: 'Inter', sans-serif;
        }
        
        .form-hint {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .form-hint i {
            font-size: 0.7rem;
        }
        
        .btn-search {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #0f766e, #14b8a6);
            color: #fff;
            border: none;
            border-radius: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(15, 118, 110, 0.4);
        }
        
        .btn-search:active {
            transform: translateY(0);
        }
        
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }
        
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }
        
        .alert-error i {
            color: #dc2626;
            margin-top: 0.125rem;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: #94a3b8;
            font-size: 0.75rem;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }
        
        .divider span {
            padding: 0 1rem;
        }
        
        .back-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            color: #64748b;
            text-decoration: none;
            font-size: 0.875rem;
            transition: color 0.2s;
        }
        
        .back-link:hover {
            color: #0f766e;
        }
        
        .footer {
            text-align: center;
            padding: 1.5rem;
            color: rgba(255,255,255,0.7);
            font-size: 0.75rem;
        }
        
        .footer a {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
        }
        
        /* Info Box */
        .info-box {
            background: #f0fdfa;
            border: 1px solid #99f6e4;
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .info-box-title {
            font-size: 0.75rem;
            font-weight: 600;
            color: #0f766e;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .info-box-content {
            font-size: 0.8rem;
            color: #115e59;
            line-height: 1.6;
        }
        
        @media (max-width: 640px) {
            .header {
                padding: 1rem;
            }
            
            .nav-links {
                display: none;
            }
            
            .search-card {
                padding: 1.5rem;
            }
            
            .search-title {
                font-size: 1.25rem;
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
        <div class="search-container">
            <div class="search-card">
                <div class="search-icon-wrapper">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                
                <h1 class="search-title">Cek Status Pra-Pendaftaran</h1>
                <p class="search-subtitle">
                    Pantau progres pendaftaran Anda secara transparan.<br>
                    Masukkan nomor pra-pendaftaran atau email yang terdaftar.
                </p>
                
                <!-- Info Box -->
                <div class="info-box">
                    <div class="info-box-title">
                        <i class="fas fa-info-circle"></i>
                        <span>Informasi</span>
                    </div>
                    <div class="info-box-content">
                        Nomor pra-pendaftaran diberikan saat Anda menyelesaikan formulir pendaftaran awal. Format: <strong>PRA2026XXXXXXXX</strong>
                    </div>
                </div>
                
                @if(session('error'))
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif
                
                <form action="{{ route('status-pra-pendaftaran.search') }}" method="GET">
                    <div class="form-group">
                        <label class="form-label">Nomor Pra-Pendaftaran / Email</label>
                        <input 
                            type="text" 
                            name="search" 
                            class="form-input"
                            placeholder="Contoh: PRA202601120001 atau email@domain.com"
                            value="{{ old('search') }}"
                            required
                            autofocus
                        >
                        <div class="form-hint">
                            <i class="fas fa-lock"></i>
                            <span>Data Anda dilindungi dan tidak dibagikan ke pihak manapun</span>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-search">
                        <i class="fas fa-search"></i>
                        <span>Cek Status Sekarang</span>
                    </button>
                </form>
                
                <div class="divider">
                    <span>atau</span>
                </div>
                
                <a href="{{ url('/') }}" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali ke Beranda</span>
                </a>
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

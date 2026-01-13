<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php $systemName = systemCompanyName(); $logoBase64 = systemLogoBase64(); @endphp
    <title>Cek Status Pendaftaran{{ $systemName ? ' - ' . $systemName : '' }}</title>
    
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
        
        .search-container {
            width: 100%;
            max-width: 500px;
        }
        
        .search-card {
            background: #fff;
            border-radius: 1rem;
            padding: 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        }
        
        .search-icon-wrapper {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #1a365d, #2c5282);
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
            color: #1a365d;
            margin-bottom: 0.5rem;
        }
        
        .search-subtitle {
            text-align: center;
            color: #64748b;
            font-size: 0.875rem;
            margin-bottom: 2rem;
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
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: all 0.2s;
            outline: none;
        }
        
        .form-input:focus {
            border-color: #1a365d;
            box-shadow: 0 0 0 3px rgba(26,54,93,0.1);
        }
        
        .form-input::placeholder {
            color: #a0aec0;
        }
        
        .btn-search {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #1a365d, #2c5282);
            color: #fff;
            border: none;
            border-radius: 0.5rem;
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
            box-shadow: 0 10px 20px rgba(26,54,93,0.3);
        }
        
        .btn-search:active {
            transform: translateY(0);
        }
        
        .info-box {
            background: #f0f9ff;
            border-left: 4px solid #0ea5e9;
            padding: 1rem;
            border-radius: 0 0.5rem 0.5rem 0;
            margin-top: 1.5rem;
        }
        
        .info-box p {
            font-size: 0.75rem;
            color: #0369a1;
            margin: 0;
        }
        
        .info-box i {
            margin-right: 0.5rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }
        
        .alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
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
            
            .search-card {
                padding: 1.5rem;
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
        <div class="search-container">
            <div class="search-card">
                <div class="search-icon-wrapper">
                    <i class="fas fa-search"></i>
                </div>
                
                <h1 class="search-title">Cek Status Pendaftaran</h1>
                <p class="search-subtitle">Masukkan nomor pendaftaran atau email untuk melihat status pendaftaran sertifikasi Anda.</p>
                
                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ session('error') }}
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $errors->first() }}
                    </div>
                @endif
                
                <form action="{{ route('status-pendaftaran.search') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Nomor Pendaftaran atau Email</label>
                        <input type="text" 
                               name="search" 
                               class="form-input" 
                               placeholder="Contoh: REG2026010001 atau email@contoh.com"
                               value="{{ old('search') }}"
                               required>
                    </div>
                    
                    <button type="submit" class="btn-search">
                        <i class="fas fa-search"></i>
                        Cek Status
                    </button>
                </form>
                
                <div class="info-box">
                    <p>
                        <i class="fas fa-info-circle"></i>
                        Sistem ini menyediakan informasi status pendaftaran secara transparan sesuai standar BNSP dan ISO 17024.
                    </p>
                </div>
            </div>
        </div>
    </main>
    
    <footer class="footer">
        <p>&copy; {{ date('Y') }} {{ $systemName ?? 'LSP' }}. Sistem Sertifikasi Profesi Terakreditasi BNSP.</p>
    </footer>
</body>
</html>

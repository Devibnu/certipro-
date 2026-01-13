<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $pageCompanyName = systemCompanyName();
        $pageFaviconUrl = systemLogoUrl();
    @endphp
    <title>Pendaftaran Berhasil - {{ $pageCompanyName }}</title>
    @if($pageFaviconUrl)
    <link rel="icon" type="image/png" href="{{ $pageFaviconUrl }}">
    @endif
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #f0fdf4 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        /* Header Logo */
        .logo-section {
            margin-bottom: 2rem;
            text-align: center;
        }

        .logo-section img {
            height: 60px;
            margin-bottom: 0.5rem;
        }

        .logo-section h1 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
        }

        /* Success Card */
        .success-card {
            background: white;
            border-radius: 24px;
            box-shadow: 
                0 4px 6px -1px rgba(0, 0, 0, 0.1),
                0 2px 4px -2px rgba(0, 0, 0, 0.1),
                0 20px 25px -5px rgba(0, 0, 0, 0.05);
            max-width: 480px;
            width: 100%;
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Success Header */
        .success-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            padding: 2.5rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .success-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 50%);
            animation: pulse 3s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.3; }
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 1;
        }

        .success-icon svg {
            width: 45px;
            height: 45px;
            color: #10b981;
            animation: checkmark 0.6s ease-out 0.3s both;
        }

        @keyframes checkmark {
            from {
                stroke-dashoffset: 100;
                opacity: 0;
            }
            to {
                stroke-dashoffset: 0;
                opacity: 1;
            }
        }

        .success-header h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .success-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
            position: relative;
            z-index: 1;
        }

        /* Card Body */
        .card-body {
            padding: 2rem;
        }

        /* Nomor Pendaftaran Section */
        .registration-number {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 2px dashed #cbd5e1;
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .registration-number label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 0.75rem;
        }

        .registration-number .number {
            font-family: 'SF Mono', 'Fira Code', 'Consolas', monospace;
            font-size: 1.75rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: 0.05em;
            word-break: break-all;
        }

        .copy-hint {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
        }

        .copy-hint svg {
            width: 14px;
            height: 14px;
        }

        /* Status Badge */
        .status-section {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 1rem;
            background: #fffbeb;
            border: 1px solid #fcd34d;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        .status-icon {
            font-size: 1.25rem;
        }

        .status-text {
            font-size: 0.875rem;
            font-weight: 600;
            color: #92400e;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.75rem;
            background: #f8fafc;
            border-radius: 10px;
        }

        .info-item .icon {
            width: 36px;
            height: 36px;
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .info-item .icon svg {
            width: 18px;
            height: 18px;
            color: #64748b;
        }

        .info-item .content label {
            display: block;
            font-size: 0.7rem;
            font-weight: 500;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.125rem;
        }

        .info-item .content span {
            font-size: 0.9rem;
            font-weight: 600;
            color: #1e293b;
        }

        /* CTA Buttons */
        .cta-section {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: white;
            box-shadow: 0 4px 14px -3px rgba(14, 165, 233, 0.4);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px -3px rgba(14, 165, 233, 0.5);
        }

        .btn-primary svg {
            width: 20px;
            height: 20px;
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
            color: #334155;
        }

        .btn-secondary svg {
            width: 18px;
            height: 18px;
        }

        /* Footer Note */
        .footer-note {
            margin-top: 2rem;
            padding: 1rem;
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 12px;
        }

        .footer-note p {
            font-size: 0.75rem;
            color: #0369a1;
            text-align: center;
            line-height: 1.6;
        }

        .footer-note .highlight {
            font-weight: 600;
        }

        /* BNSP Badge */
        .bnsp-badge {
            margin-top: 2rem;
            text-align: center;
        }

        .bnsp-badge p {
            font-size: 0.7rem;
            color: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .bnsp-badge svg {
            width: 14px;
            height: 14px;
            color: #10b981;
        }

        /* Confetti Animation */
        .confetti {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 100;
            overflow: hidden;
        }

        .confetti-piece {
            position: absolute;
            width: 10px;
            height: 10px;
            background: #10b981;
            animation: confetti-fall 3s linear forwards;
            opacity: 0;
        }

        .confetti-piece:nth-child(odd) {
            background: #0ea5e9;
        }

        .confetti-piece:nth-child(3n) {
            background: #f59e0b;
        }

        @keyframes confetti-fall {
            0% {
                opacity: 1;
                transform: translateY(-100vh) rotate(0deg);
            }
            100% {
                opacity: 0;
                transform: translateY(100vh) rotate(720deg);
            }
        }

        /* Responsive */
        @media (max-width: 480px) {
            .success-header {
                padding: 2rem 1.5rem;
            }

            .success-icon {
                width: 70px;
                height: 70px;
            }

            .success-icon svg {
                width: 38px;
                height: 38px;
            }

            .success-header h2 {
                font-size: 1.5rem;
            }

            .registration-number .number {
                font-size: 1.4rem;
            }

            .card-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Confetti Animation -->
    <div class="confetti" id="confetti"></div>

    <!-- Logo Section -->
    <div class="logo-section">
        @php
            $logoUrl = systemLogoUrl();
            $companyName = systemCompanyName();
        @endphp
        @if($logoUrl)
            <img src="{{ $logoUrl }}" alt="Logo">
        @endif
        @if($companyName)
            <h1>{{ $companyName }}</h1>
        @endif
    </div>

    <!-- Success Card -->
    <div class="success-card">
        <!-- Success Header -->
        <div class="success-header">
            <div class="success-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h2>Pendaftaran Berhasil!</h2>
            <p>Data Anda telah kami terima dengan baik</p>
        </div>

        <!-- Card Body -->
        <div class="card-body">
            <!-- Nomor Pendaftaran -->
            <div class="registration-number">
                <label>Nomor Pendaftaran Anda</label>
                <div class="number" id="nomorPendaftaran">{{ $pendaftaran['nomor'] ?? 'N/A' }}</div>
                <div class="copy-hint">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <span>Simpan nomor ini untuk pengecekan status</span>
                </div>
            </div>

            <!-- Status Badge -->
            <div class="status-section">
                <span class="status-icon">⏳</span>
                <span class="status-text">MENUNGGU VERIFIKASI</span>
            </div>

            <!-- Info Grid -->
            <div class="info-grid">
                <div class="info-item">
                    <div class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div class="content">
                        <label>Nama Lengkap</label>
                        <span>{{ $pendaftaran['nama'] ?? '-' }}</span>
                    </div>
                </div>

                <div class="info-item">
                    <div class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="content">
                        <label>Email</label>
                        <span>{{ $pendaftaran['email'] ?? '-' }}</span>
                    </div>
                </div>

                <div class="info-item">
                    <div class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="content">
                        <label>Tanggal Pendaftaran</label>
                        <span>{{ $pendaftaran['created_at'] ?? now()->format('d F Y, H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- CTA Buttons -->
            <div class="cta-section">
                <a href="{{ route('status-pendaftaran') }}" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <span>Cek Status Pendaftaran</span>
                </a>
                <a href="{{ url('/') }}" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Kembali ke Beranda</span>
                </a>
            </div>

            <!-- Footer Note -->
            <div class="footer-note">
                <p>
                    <span class="highlight">📋 Apa selanjutnya?</span><br>
                    Tim kami akan memverifikasi data Anda dalam <strong>1-3 hari kerja</strong>.
                    Anda akan menerima notifikasi melalui email setelah verifikasi selesai.
                </p>
            </div>

            <!-- UX Hint -->
            <div class="footer-note" style="background: #f0fdf4; border-color: #86efac; margin-top: 1rem;">
                <p style="color: #166534;">
                    <span class="highlight">💡 Tips:</span><br>
                    <strong>Simpan nomor pra-pendaftaran Anda.</strong><br>
                    Gunakan menu <strong>"Cek Status"</strong> di website kapan saja untuk memantau proses pendaftaran Anda.
                </p>
            </div>
        </div>
    </div>

    <!-- BNSP Badge -->
    <div class="bnsp-badge">
        <p>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            Sistem Sertifikasi Terakreditasi BNSP | ISO 17024
        </p>
    </div>

    <script>
        // Create confetti effect
        function createConfetti() {
            const container = document.getElementById('confetti');
            const colors = ['#10b981', '#0ea5e9', '#f59e0b', '#8b5cf6', '#ec4899'];
            
            for (let i = 0; i < 50; i++) {
                const piece = document.createElement('div');
                piece.className = 'confetti-piece';
                piece.style.left = Math.random() * 100 + 'vw';
                piece.style.animationDelay = Math.random() * 2 + 's';
                piece.style.animationDuration = (Math.random() * 2 + 2) + 's';
                piece.style.background = colors[Math.floor(Math.random() * colors.length)];
                piece.style.borderRadius = Math.random() > 0.5 ? '50%' : '0';
                piece.style.width = (Math.random() * 8 + 6) + 'px';
                piece.style.height = (Math.random() * 8 + 6) + 'px';
                container.appendChild(piece);
            }

            // Remove confetti after animation
            setTimeout(() => {
                container.innerHTML = '';
            }, 5000);
        }

        // Run confetti on load
        window.addEventListener('load', createConfetti);

        // Copy to clipboard functionality
        document.getElementById('nomorPendaftaran').addEventListener('click', function() {
            const text = this.textContent;
            navigator.clipboard.writeText(text).then(() => {
                // Show toast notification
                const toast = document.createElement('div');
                toast.textContent = '✓ Nomor disalin ke clipboard';
                toast.style.cssText = `
                    position: fixed;
                    bottom: 20px;
                    left: 50%;
                    transform: translateX(-50%);
                    background: #1e293b;
                    color: white;
                    padding: 0.75rem 1.5rem;
                    border-radius: 8px;
                    font-size: 0.875rem;
                    z-index: 1000;
                    animation: fadeInUp 0.3s ease;
                `;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 2000);
            });
        });
    </script>
</body>
</html>

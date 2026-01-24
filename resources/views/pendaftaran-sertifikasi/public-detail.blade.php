<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pendaftaran Sertifikasi - {{ config('app.name', 'CertiPro LSP') }}</title>
    
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar-brand {
            font-weight: bold;
            color: #10b981 !important;
        }
    </style>
</head>
<body>
    <!-- Simple Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <i class="fas fa-certificate text-success"></i> {{ config('app.name', 'CertiPro LSP') }}
            </a>
        </div>
    </nav>
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            <!-- Header -->
            <div class="card shadow-sm mb-4" style="border-top: 4px solid #10b981;">
                <div class="card-body text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-check-circle" style="font-size: 48px; color: #10b981;"></i>
                    </div>
                    <h2 class="text-success mb-2">Pendaftaran Sertifikasi Aktif</h2>
                    <p class="text-muted mb-0">Nomor Pendaftaran: <strong class="text-dark">{{ $pendaftaran->nomor_pendaftaran }}</strong></p>
                </div>
            </div>

            <!-- Detail Pendaftaran -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="fas fa-info-circle text-primary mr-2"></i>Informasi Pendaftaran</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td width="40%" class="text-muted">Nomor Pendaftaran</td>
                            <td><strong>{{ $pendaftaran->nomor_pendaftaran }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Nama Lengkap</td>
                            <td>{{ $pendaftaran->nama_lengkap }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Email</td>
                            <td>{{ $pendaftaran->email }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">No. HP/WA</td>
                            <td>{{ $pendaftaran->no_hp }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tanggal Daftar</td>
                            <td>{{ $pendaftaran->tanggal_daftar->format('d F Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                @switch($pendaftaran->status)
                                    @case('diajukan')
                                        <span class="badge badge-warning">Diajukan</span>
                                        @break
                                    @case('diverifikasi')
                                        <span class="badge badge-info">Diverifikasi</span>
                                        @break
                                    @case('siap_asesmen')
                                        <span class="badge badge-primary">Siap Asesmen</span>
                                        @break
                                    @default
                                        <span class="badge badge-secondary">{{ ucfirst($pendaftaran->status) }}</span>
                                @endswitch
                            </td>
                        </tr>
                        @if($pendaftaran->skemaSertifikasi)
                        <tr>
                            <td class="text-muted">Skema Sertifikasi</td>
                            <td>
                                <strong>{{ $pendaftaran->skemaSertifikasi->kode_skema }}</strong><br>
                                <small class="text-muted">{{ $pendaftaran->skemaSertifikasi->nama_skema }}</small>
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Status Progress -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="fas fa-tasks text-success mr-2"></i>Langkah Selanjutnya</h5>
                </div>
                <div class="card-body">
                    @if(!$pendaftaran->skemaSertifikasi)
                        <div class="alert alert-warning mb-3">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>Perhatian:</strong> Anda belum memilih skema sertifikasi.
                        </div>
                        <p class="mb-3">Untuk melanjutkan proses sertifikasi, Anda perlu:</p>
                        <ol class="pl-4">
                            <li>Login atau daftar akun (jika belum punya)</li>
                            <li>Pilih skema kompetensi yang sesuai</li>
                            <li>Lengkapi dokumen persyaratan</li>
                            <li>Pilih jadwal asesmen</li>
                        </ol>
                    @else
                        <div class="alert alert-success mb-3">
                            <i class="fas fa-check-circle mr-2"></i>
                            <strong>Skema sudah dipilih:</strong> {{ $pendaftaran->skemaSertifikasi->nama_skema }}
                        </div>
                        <p class="mb-3">Langkah selanjutnya:</p>
                        <ol class="pl-4">
                            <li>Login ke akun Anda untuk melanjutkan proses</li>
                            <li>Lengkapi dokumen yang diperlukan</li>
                            <li>Tunggu verifikasi admin</li>
                            <li>Pilih jadwal asesmen yang tersedia</li>
                        </ol>
                    @endif
                </div>
            </div>

            <!-- CTA Buttons -->
            <div class="card shadow-sm mb-4 border-0" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);">
                <div class="card-body text-center py-4">
                    <h5 class="text-success mb-3">Siap Melanjutkan?</h5>
                    
                    @auth
                        <!-- User sudah login -->
                        <a href="{{ route('pendaftaran-sertifikasi.index') }}" class="btn btn-success btn-lg">
                            <i class="fas fa-arrow-right mr-2"></i>Lanjutkan Pendaftaran
                        </a>
                    @else
                        <!-- User belum login -->
                        <a href="{{ route('login') }}?redirect={{ urlencode(route('pendaftaran-sertifikasi.index')) }}" class="btn btn-success btn-lg mr-2">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-outline-success btn-lg">
                            <i class="fas fa-user-plus mr-2"></i>Daftar Akun
                        </a>
                        
                        <p class="text-muted mt-3 mb-0 small">
                            <i class="fas fa-info-circle mr-1"></i>
                            Gunakan email <strong>{{ $pendaftaran->email }}</strong> saat membuat akun
                        </p>
                    @endauth
                </div>
            </div>

            <!-- Info Bantuan -->
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body text-center">
                    <p class="mb-2"><i class="fas fa-question-circle mr-1"></i> <strong>Butuh Bantuan?</strong></p>
                    <p class="text-muted mb-0 small">
                        Hubungi kami di <strong>{{ config('app.contact_email', 'info@certipro.id') }}</strong>
                        atau WhatsApp <strong>{{ config('app.contact_phone', '0812-3456-7890') }}</strong>
                    </p>
                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
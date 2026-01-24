@extends('layouts.app')

@section('title', 'Cari Sertifikat')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            
            {{-- Search Form Card --}}
            <div class="card shadow-lg">
                <div class="card-header bg-gradient-primary text-center pb-4 pt-5">
                    <i class="fas fa-search text-white mb-3" style="font-size: 3rem;"></i>
                    <h3 class="text-white mb-2">Verifikasi Sertifikat</h3>
                    <p class="text-white opacity-8 mb-0">
                        Masukkan nomor sertifikat untuk memverifikasi keaslian
                    </p>
                </div>
                
                <div class="card-body p-4">
                    
                    {{-- Info Alert --}}
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Cara Verifikasi:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Scan QR Code pada sertifikat, ATAU</li>
                            <li>Masukkan nomor sertifikat secara manual</li>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    
                    {{-- Search Form --}}
                    <form action="{{ route('public.sertifikat.search') }}" method="POST" class="mt-4">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Nomor Sertifikat</label>
                            <input 
                                type="text" 
                                name="nomor_sertifikat" 
                                class="form-control form-control-lg @error('nomor_sertifikat') is-invalid @enderror" 
                                placeholder="Contoh: CERT/CTP/2026/000123"
                                value="{{ old('nomor_sertifikat') }}"
                                required
                                autofocus>
                            
                            @error('nomor_sertifikat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            <small class="form-text text-muted">
                                Format nomor sertifikat: CERT/CTP/TAHUN/NOMOR
                            </small>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-search"></i> Verifikasi Sekarang
                            </button>
                        </div>
                    </form>
                    
                </div>
            </div>
            
            {{-- Features Info --}}
            <div class="row mt-4">
                <div class="col-md-4 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-shield-alt text-success mb-3" style="font-size: 2rem;"></i>
                            <h6 class="mb-2">Terverifikasi</h6>
                            <small class="text-muted">
                                Sertifikat resmi LSP terakreditasi BNSP
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-qrcode text-primary mb-3" style="font-size: 2rem;"></i>
                            <h6 class="mb-2">QR Code</h6>
                            <small class="text-muted">
                                Scan untuk verifikasi instan
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-download text-info mb-3" style="font-size: 2rem;"></i>
                            <h6 class="mb-2">Download PDF</h6>
                            <small class="text-muted">
                                Unduh sertifikat digital resmi
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Help Section --}}
            <div class="card mt-4">
                <div class="card-body">
                    <h6 class="mb-3">
                        <i class="fas fa-question-circle text-warning"></i> Butuh Bantuan?
                    </h6>
                    
                    <div class="accordion" id="faqAccordion">
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    Di mana saya bisa menemukan nomor sertifikat?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Nomor sertifikat terletak di bagian atas sertifikat, di bawah judul "SERTIFIKAT KOMPETENSI".
                                    Format: <code>CERT/CTP/2026/000123</code>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    Bagaimana cara scan QR Code?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    1. Buka aplikasi kamera atau QR scanner di smartphone<br>
                                    2. Arahkan ke QR Code di sertifikat<br>
                                    3. Tap notifikasi yang muncul<br>
                                    4. Browser akan otomatis membuka halaman verifikasi
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Sertifikat tidak ditemukan, bagaimana?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Pastikan nomor sertifikat diinput dengan benar (termasuk tanda "/").
                                    Jika masih tidak ditemukan, hubungi administrator LSP di:
                                    <strong>{{ config('certipro.email') }}</strong>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
            
            {{-- Contact Info --}}
            <div class="text-center mt-4">
                <p class="text-muted mb-1">
                    <i class="fas fa-envelope"></i> 
                    {{ config('certipro.email', 'info@certipro.id') }}
                </p>
                <p class="text-muted mb-3">
                    <i class="fas fa-phone"></i> 
                    {{ config('certipro.telepon', '021-12345678') }}
                </p>
                <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-home"></i> Kembali ke Beranda
                </a>
            </div>
            
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 10px;
    overflow: hidden;
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
}

.accordion-button:not(.collapsed) {
    background-color: #f8f9fa;
    color: #1a365d;
}

.accordion-button:focus {
    box-shadow: none;
    border-color: rgba(0,0,0,.125);
}
</style>
@endsection

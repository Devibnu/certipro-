@extends('layouts.app')

@section('title', 'Verifikasi Sertifikat')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            @if(!$found)
                {{-- Certificate NOT FOUND --}}
                <div class="card border-danger">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-exclamation-triangle text-danger mb-3" style="font-size: 4rem;"></i>
                        <h3 class="text-danger mb-3">Sertifikat Tidak Ditemukan</h3>
                        <p class="text-muted mb-4">{{ $message }}</p>
                        
                        <a href="{{ route('public.sertifikat.search') }}" class="btn btn-primary">
                            <i class="fas fa-search"></i> Cari Sertifikat Lain
                        </a>
                        <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-home"></i> Kembali ke Beranda
                        </a>
                    </div>
                </div>
            @else
                {{-- Certificate FOUND --}}
                
                {{-- Status Badge --}}
                <div class="card {{ $isValid ? 'border-success' : 'border-danger' }} mb-4">
                    <div class="card-body text-center py-4">
                        @if($isValid)
                            <i class="fas fa-check-circle text-success mb-3" style="font-size: 4rem;"></i>
                            <h3 class="text-success mb-2">Sertifikat VALID</h3>
                            <p class="text-muted mb-0">Sertifikat ini sah dan masih berlaku</p>
                        @else
                            <i class="fas fa-times-circle text-danger mb-3" style="font-size: 4rem;"></i>
                            <h3 class="text-danger mb-2">Sertifikat KADALUARSA</h3>
                            <p class="text-muted mb-0">Sertifikat ini valid namun sudah melewati masa berlaku</p>
                        @endif
                    </div>
                </div>
                
                {{-- Certificate Details --}}
                <div class="card mb-4">
                    <div class="card-header bg-gradient-primary">
                        <h5 class="text-white mb-0">
                            <i class="fas fa-certificate"></i> Detail Sertifikat
                        </h5>
                    </div>
                    <div class="card-body">
                        
                        {{-- Nomor Sertifikat --}}
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Nomor Sertifikat:</div>
                            <div class="col-md-8">
                                <span class="badge bg-dark px-3 py-2" style="font-size: 1rem;">
                                    {{ $sertifikat->nomor_sertifikat }}
                                </span>
                            </div>
                        </div>
                        
                        <hr>
                        
                        {{-- Nama Pemegang --}}
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Nama Pemegang:</div>
                            <div class="col-md-8">
                                <h4 class="mb-0 text-primary">{{ $sertifikat->nama_peserta }}</h4>
                            </div>
                        </div>
                        
                        {{-- NIK / Identitas --}}
                        @if($sertifikat->pendaftaran->nik)
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">NIK:</div>
                            <div class="col-md-8">{{ $sertifikat->pendaftaran->nik }}</div>
                        </div>
                        @endif
                        
                        <hr>
                        
                        {{-- Skema Sertifikasi --}}
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Skema Sertifikasi:</div>
                            <div class="col-md-8">
                                <strong class="text-dark">{{ $sertifikat->skema_sertifikasi }}</strong>
                                @if($sertifikat->pendaftaran->skemaSertifikasi)
                                <br>
                                <small class="text-muted">
                                    Kode: {{ $sertifikat->pendaftaran->skemaSertifikasi->kode_skema }}
                                </small>
                                @endif
                            </div>
                        </div>
                        
                        <hr>
                        
                        {{-- Tanggal Terbit --}}
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Tanggal Terbit:</div>
                            <div class="col-md-8">{{ $sertifikat->tanggal_terbit->isoFormat('D MMMM Y') }}</div>
                        </div>
                        
                        {{-- Masa Berlaku --}}
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Masa Berlaku:</div>
                            <div class="col-md-8">
                                <strong class="{{ $isValid ? 'text-success' : 'text-danger' }}">
                                    s.d. {{ $sertifikat->tanggal_berlaku_sampai->isoFormat('D MMMM Y') }}
                                </strong>
                                <br>
                                <small class="text-muted">
                                    @if($isValid)
                                        ({{ $sertifikat->tanggal_berlaku_sampai->diffForHumans() }})
                                    @else
                                        (Kadaluarsa {{ $sertifikat->tanggal_berlaku_sampai->diffForHumans() }})
                                    @endif
                                </small>
                            </div>
                        </div>
                        
                        <hr>
                        
                        {{-- Keputusan Reference --}}
                        @if($sertifikat->pendaftaran->keputusan)
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Dasar Keputusan:</div>
                            <div class="col-md-8">
                                <div class="alert alert-info mb-0">
                                    <strong>Keputusan Komite Teknis</strong><br>
                                    Tanggal: {{ $sertifikat->pendaftaran->keputusan->tanggal_keputusan->isoFormat('D MMMM Y') }}<br>
                                    Status: <span class="badge bg-success">{{ strtoupper($sertifikat->pendaftaran->keputusan->keputusan) }}</span><br>
                                    Ditetapkan oleh: {{ $sertifikat->pendaftaran->keputusan->penetap->name ?? '-' }}
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        {{-- Diterbitkan Oleh --}}
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Diterbitkan Oleh:</div>
                            <div class="col-md-8">
                                {{ config('certipro.nama_lsp', 'LSP CertiPro') }}<br>
                                <small class="text-muted">
                                    Lisensi BNSP: {{ config('certipro.nomor_lisensi', 'LSP-XXXXX-ID') }}
                                </small>
                            </div>
                        </div>
                        
                        <hr>
                        
                        {{-- Security Hash --}}
                        <div class="row">
                            <div class="col-md-4 fw-bold">Security Hash:</div>
                            <div class="col-md-8">
                                <code style="font-size: 0.75rem; word-break: break-all;">
                                    {{ $securityHash }}
                                </code>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    Hash ini digunakan untuk verifikasi keaslian dokumen
                                </small>
                            </div>
                        </div>
                        
                    </div>
                </div>
                
                {{-- Actions --}}
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <h6 class="mb-3">Unduh Sertifikat</h6>
                        
                        @if($sertifikat->file_pdf && \Storage::disk('public')->exists($sertifikat->file_pdf))
                            <a href="{{ route('public.sertifikat.download', $sertifikat->uuid) }}" 
                               class="btn btn-success btn-lg mb-2"
                               target="_blank">
                                <i class="fas fa-download"></i> Download PDF Sertifikat
                            </a>
                            <br>
                            <small class="text-muted">
                                File PDF original yang ditandatangani secara digital
                            </small>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> 
                                File PDF tidak tersedia. Silakan hubungi administrator LSP.
                            </div>
                        @endif
                    </div>
                </div>
                
                {{-- QR Code Info --}}
                <div class="card border-info">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="text-info mb-2">
                                    <i class="fas fa-qrcode"></i> Verifikasi via QR Code
                                </h6>
                                <p class="mb-0 small">
                                    Scan QR code pada sertifikat fisik untuk memverifikasi keaslian.
                                    Setiap sertifikat dilengkapi dengan QR code unik yang mengarah ke halaman verifikasi ini.
                                </p>
                            </div>
                            <div class="col-md-4 text-center">
                                @if($sertifikat->qr_code && \Storage::disk('public')->exists($sertifikat->qr_code))
                                    <img src="{{ \Storage::disk('public')->url($sertifikat->qr_code) }}" 
                                         alt="QR Code" 
                                         class="img-fluid"
                                         style="max-width: 120px;">
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Navigation --}}
                <div class="text-center mt-4">
                    <a href="{{ route('public.sertifikat.search') }}" class="btn btn-outline-primary">
                        <i class="fas fa-search"></i> Verifikasi Sertifikat Lain
                    </a>
                    <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-home"></i> Kembali ke Beranda
                    </a>
                </div>
                
            @endif
            
        </div>
    </div>
</div>

{{-- Styles --}}
<style>
.card {
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-radius: 10px;
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
}

hr {
    border-top: 1px solid #e0e0e0;
    margin: 1rem 0;
}

.badge {
    font-weight: 500;
}

code {
    background: #f5f5f5;
    padding: 0.5rem;
    border-radius: 4px;
    display: inline-block;
}
</style>
@endsection

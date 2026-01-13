@extends('adminui.layouts.auth')

@section('title', 'Detail Sertifikat' . (systemCompanyName() ? ' - ' . systemCompanyName() . ' Admin' : ' - Admin'))

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Header Card -->
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h6><i class="fas fa-certificate me-2 text-success"></i>Detail Sertifikat</h6>
                        <p class="text-sm mb-0">Nomor: <strong>{{ $sertifikat->nomor_sertifikat }}</strong></p>
                    </div>
                    <div class="d-flex gap-2">
                        @if($sertifikat->pendaftaran_id)
                        <a href="{{ route('adminui.keputusan.audit-pdf', $sertifikat->pendaftaran_id) }}" 
                           class="btn btn-outline-primary btn-sm"
                           title="Download Audit Evidence PDF"
                           target="_blank">
                            <i class="fas fa-file-pdf me-1"></i> Audit PDF
                        </a>
                        @endif
                        <a href="{{ route('adminui.sertifikat.download', $sertifikat->id) }}" 
                           class="btn bg-gradient-success btn-sm">
                            <i class="fas fa-download me-1"></i> Download PDF
                        </a>
                        <a href="{{ route('adminui.sertifikat.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            <span class="text-white">{{ session('success') }}</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        <!-- Sertifikat Info -->
                        <div class="col-lg-8">
                            <div class="card bg-gradient-light mb-4">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <label class="text-xs text-uppercase text-muted">Nomor Sertifikat</label>
                                            <h5 class="mb-0 font-weight-bold">{{ $sertifikat->nomor_sertifikat }}</h5>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <label class="text-xs text-uppercase text-muted">Status Validitas</label>
                                            <div>
                                                <span class="badge badge-lg {{ $sertifikat->status_validitas_badge }}">
                                                    {{ $sertifikat->status_validitas }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mb-4">
                                            <label class="text-xs text-uppercase text-muted">Nama Pemegang Sertifikat</label>
                                            <h4 class="mb-0 font-weight-bold text-primary">{{ $sertifikat->nama_peserta }}</h4>
                                        </div>
                                        <div class="col-md-12 mb-4">
                                            <label class="text-xs text-uppercase text-muted">Skema Sertifikasi</label>
                                            <h5 class="mb-0">{{ $sertifikat->skema_sertifikasi }}</h5>
                                            @if($sertifikat->pendaftaran?->skemaSertifikasi)
                                            <span class="badge badge-sm bg-gradient-info mt-1">
                                                {{ $sertifikat->pendaftaran->skemaSertifikasi->kode_skema }}
                                            </span>
                                            @endif
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <label class="text-xs text-uppercase text-muted">Tanggal Terbit</label>
                                            <p class="mb-0 font-weight-bold">
                                                <i class="fas fa-calendar-check text-success me-1"></i>
                                                {{ $sertifikat->tanggal_terbit->format('d F Y') }}
                                            </p>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <label class="text-xs text-uppercase text-muted">Berlaku Sampai</label>
                                            <p class="mb-0 font-weight-bold">
                                                <i class="fas fa-calendar-times text-warning me-1"></i>
                                                {{ $sertifikat->tanggal_berlaku_sampai->format('d F Y') }}
                                            </p>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <label class="text-xs text-uppercase text-muted">Diterbitkan Oleh</label>
                                            <p class="mb-0">{{ $sertifikat->penerbit->name ?? '-' }}</p>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <label class="text-xs text-uppercase text-muted">No. Pendaftaran</label>
                                            <p class="mb-0">{{ $sertifikat->pendaftaran->nomor_pendaftaran ?? '-' }}</p>
                                        </div>
                                        <div class="col-md-12 mb-4">
                                            <label class="text-xs text-uppercase text-muted">
                                                <i class="fas fa-shield-alt text-success me-1"></i>Secure ID (UUID)
                                            </label>
                                            <p class="mb-0 font-weight-bold text-success" style="font-family: monospace; font-size: 12px; word-break: break-all;">
                                                {{ $sertifikat->uuid }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- QR Code Section -->
                        <div class="col-lg-4">
                            <div class="card text-center">
                                <div class="card-header pb-0">
                                    <h6 class="mb-0"><i class="fas fa-qrcode me-2"></i>QR Code Verifikasi</h6>
                                </div>
                                <div class="card-body">
                                    @if($sertifikat->qr_code)
                                    <img src="{{ Storage::url($sertifikat->qr_code) }}" 
                                         alt="QR Code" class="img-fluid mb-3" style="max-width: 200px;">
                                    @else
                                    <div class="text-muted mb-3">
                                        <i class="fas fa-qrcode fa-5x"></i>
                                        <p class="text-sm mt-2">QR Code tidak tersedia</p>
                                    </div>
                                    @endif
                                    <p class="text-xs text-muted mb-2">Scan untuk verifikasi keaslian sertifikat</p>
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control form-control-sm text-xs" 
                                               value="{{ $sertifikat->getVerificationUrl() }}" 
                                               id="verification-url" readonly>
                                        <button class="btn btn-outline-primary btn-sm mb-0" type="button" 
                                                onclick="copyVerificationUrl()">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Info Pendaftaran -->
                            @if($sertifikat->pendaftaran)
                            <div class="card mt-4">
                                <div class="card-header pb-0">
                                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Info Pendaftaran</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <span class="text-xs text-muted">Email:</span>
                                            <p class="text-sm mb-0">{{ $sertifikat->pendaftaran->user->email ?? '-' }}</p>
                                        </li>
                                        <li class="mb-2">
                                            <span class="text-xs text-muted">Tanggal Daftar:</span>
                                            <p class="text-sm mb-0">{{ $sertifikat->pendaftaran->tanggal_daftar?->format('d M Y') ?? '-' }}</p>
                                        </li>
                                        @if($sertifikat->pendaftaran->asesmen)
                                        <li class="mb-2">
                                            <span class="text-xs text-muted">Tanggal Asesmen:</span>
                                            <p class="text-sm mb-0">{{ $sertifikat->pendaftaran->asesmen->tanggal_asesmen?->format('d M Y') ?? '-' }}</p>
                                        </li>
                                        <li class="mb-2">
                                            <span class="text-xs text-muted">Asesor:</span>
                                            <p class="text-sm mb-0">{{ $sertifikat->pendaftaran->asesmen->asesor->name ?? '-' }}</p>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Preview PDF Button -->
                    <div class="text-center mt-4">
                        <a href="{{ route('adminui.sertifikat.download', $sertifikat->id) }}" 
                           class="btn bg-gradient-success btn-lg" target="_blank">
                            <i class="fas fa-file-pdf me-2"></i> Download Sertifikat PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyVerificationUrl() {
    var input = document.getElementById('verification-url');
    input.select();
    document.execCommand('copy');
    alert('URL verifikasi berhasil disalin!');
}
</script>
@endsection

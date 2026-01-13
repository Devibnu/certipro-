@extends('adminui.layouts.auth')
@section('title', '403 - Akses Ditolak')
@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-lg border-0">
                <!-- Header -->
                <div class="card-header bg-gradient-danger text-white text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-shield-alt" style="font-size: 4rem; opacity: 0.9;"></i>
                    </div>
                    <h2 class="mb-0 text-white">Akses Ditolak</h2>
                    <p class="text-white-50 mb-0 mt-2">Error 403 - Forbidden</p>
                </div>
                
                <!-- Body -->
                <div class="card-body p-4">
                    <!-- Main Message -->
                    <div class="text-center mb-4">
                        <h4 class="text-dark mb-3">
                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                            Anda tidak memiliki izin untuk mengakses halaman ini
                        </h4>
                        <p class="text-muted mb-0">
                            Fitur ini memerlukan izin khusus yang tidak dimiliki oleh akun Anda.
                        </p>
                    </div>
                    
                    <!-- Detail Box -->
                    <div class="alert alert-light border mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Pengguna</small>
                                        <strong>{{ $user->name ?? Auth::user()->name }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <i class="fas fa-user-tag"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Role</small>
                                        <strong>{{ $user->role ?? Auth::user()->role ?? 'N/A' }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Required Permission -->
                    @if(isset($required_permissions) && count($required_permissions) > 0)
                    <div class="alert alert-warning border-0 mb-4">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-key text-warning me-3 mt-1" style="font-size: 1.2rem;"></i>
                            <div>
                                <strong class="d-block mb-1">Izin yang Diperlukan:</strong>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    @foreach($required_permissions as $perm)
                                    <span class="badge bg-secondary">{{ $perm }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Audit Info -->
                    <div class="bg-light rounded p-3 mb-4">
                        <div class="row g-2 small text-muted">
                            <div class="col-md-4">
                                <i class="fas fa-clock me-1"></i>
                                <strong>Waktu:</strong> {{ isset($timestamp) ? $timestamp->format('d M Y H:i:s') : now()->format('d M Y H:i:s') }}
                            </div>
                            <div class="col-md-4">
                                <i class="fas fa-globe me-1"></i>
                                <strong>IP:</strong> {{ request()->ip() }}
                            </div>
                            <div class="col-md-4">
                                <i class="fas fa-hashtag me-1"></i>
                                <strong>Request ID:</strong> {{ $request_id ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
                        <a href="{{ route('adminui.dashboard') }}" class="btn bg-gradient-primary">
                            <i class="fas fa-home me-2"></i>Kembali ke Dashboard
                        </a>
                        <a href="javascript:history.back()" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Halaman Sebelumnya
                        </a>
                    </div>
                    
                    <!-- Contact Admin -->
                    <div class="text-center mt-4 pt-4 border-top">
                        <p class="text-muted mb-2">
                            <i class="fas fa-info-circle me-1"></i>
                            Jika Anda memerlukan akses ke fitur ini, hubungi administrator sistem.
                        </p>
                        <small class="text-muted">
                            Semua percobaan akses dicatat untuk keperluan audit keamanan (ISO 17024).
                        </small>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="card-footer bg-light text-center py-3">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt me-1"></i>
                        CertiPro LSP - ISO 17024 & BNSP Compliant
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
.icon-box {
    min-width: 40px;
    min-height: 40px;
}
.badge {
    font-weight: 500;
    padding: 0.5em 0.8em;
}
</style>
@endpush

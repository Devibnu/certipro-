@extends('adminui.layouts.auth')

@section('title', 'Error - Asesmen Detail' . (systemCompanyName() ? ' - ' . systemCompanyName() . ' Admin' : ' - Admin'))

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-lg">
                <div class="card-body text-center py-5">
                    <!-- Error Icon -->
                    <div class="mb-4">
                        <i class="fas fa-exclamation-triangle fa-5x text-warning"></i>
                    </div>

                    <!-- Error Title -->
                    <h3 class="text-dark mb-3">{{ $title ?? 'Terjadi Kesalahan' }}</h3>

                    <!-- Error Message -->
                    <p class="text-muted mb-4">
                        {{ $message ?? 'Terjadi kesalahan saat memuat data asesmen.' }}
                    </p>

                    <!-- Error Details (if any) -->
                    @if(isset($details) && is_array($details) && count($details) > 0)
                    <div class="alert alert-warning text-start mb-4" role="alert">
                        <strong><i class="fas fa-info-circle me-2"></i>Detail Masalah:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($details as $detail)
                            <li>{{ $detail }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <!-- Technical Details (only in debug mode) -->
                    @if(isset($technical) && $technical && config('app.debug'))
                    <div class="alert alert-danger text-start mb-4" role="alert">
                        <strong><i class="fas fa-bug me-2"></i>Technical Error (Debug Mode):</strong>
                        <pre class="mb-0 mt-2 text-start"><code>{{ $technical }}</code></pre>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 justify-content-center">
                        <a href="{{ $backUrl ?? route('adminui.asesmen.index') }}" 
                           class="btn bg-gradient-primary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar Asesmen
                        </a>
                        
                        <a href="{{ route('adminui.dashboard') }}" 
                           class="btn btn-outline-secondary">
                            <i class="fas fa-home me-2"></i>Ke Dashboard
                        </a>
                    </div>

                    <!-- Help Text -->
                    <div class="mt-4">
                        <p class="text-sm text-muted mb-0">
                            <i class="fas fa-question-circle me-1"></i>
                            Jika masalah terus terjadi, silakan hubungi administrator sistem.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Information Card -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6 class="text-uppercase text-secondary text-xs font-weight-bolder mb-3">
                        <i class="fas fa-lightbulb me-2"></i>Kemungkinan Penyebab
                    </h6>
                    <ul class="text-sm mb-0">
                        <li>Data asesmen belum lengkap (proses pendaftaran belum selesai)</li>
                        <li>Data unit kompetensi atau KUK belum tersinkronisasi</li>
                        <li>Relasi data tidak konsisten di database</li>
                        <li>Asesmen dalam proses migrasi data</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto refresh setelah 30 detik jika user ingin mencoba lagi
    let countdown = 30;
    
    function updateCountdown() {
        countdown--;
        if (countdown <= 0) {
            console.log('Auto-refresh timeout completed');
        }
    }
    
    setInterval(updateCountdown, 1000);
</script>
@endpush

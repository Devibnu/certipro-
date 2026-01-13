{{--
================================================================================
CertiPro LSP - Email Section Card Component
================================================================================
File: resources/views/adminui/components/email-section-card.blade.php

Komponen kartu untuk menampilkan informasi email di halaman detail.
Menampilkan: status email, tombol preview, tombol history

Usage:
@include('adminui.components.email-section-card', [
    'type' => 'pra-diterima',
    'id' => $praPendaftaran->id,
    'model' => $praPendaftaran,
    'title' => 'Email Penerimaan',  // optional
])
================================================================================
--}}

@php
    $type = $type ?? '';
    $id = $id ?? 0;
    $model = $model ?? null;
    $title = $title ?? 'Status Email';
    
    // Get email status via service
    $emailService = app(\App\Services\EmailNotificationService::class);
    $hasBeenSent = $model ? $emailService->hasBeenSent($model, $type) : false;
    $lastSent = $model ? $emailService->getLastSentTime($model, $type) : null;
    $resendCount = $model ? $emailService->getResendCount($model, $type) : 0;
    $maxResend = \App\Services\EmailNotificationService::MAX_RESEND_ATTEMPTS;
    $canResend = $resendCount < $maxResend;
@endphp

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">
                <i class="fas fa-envelope text-primary me-2"></i>{{ $title }}
            </h6>
            @if($hasBeenSent)
                <span class="badge bg-success-subtle text-success">
                    <i class="fas fa-check-circle me-1"></i>Terkirim
                </span>
            @else
                <span class="badge bg-secondary-subtle text-secondary">
                    <i class="fas fa-clock me-1"></i>Belum Dikirim
                </span>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-7">
                @if($hasBeenSent)
                    <div class="d-flex flex-column gap-1">
                        <div>
                            <small class="text-muted">Terakhir Dikirim:</small>
                            <span class="fw-medium ms-1">{{ $lastSent ?? '-' }}</span>
                        </div>
                        @if($resendCount > 0)
                        <div>
                            <small class="text-muted">Pengiriman Ulang:</small>
                            <span class="text-info fw-medium ms-1">{{ $resendCount }}x</span>
                            <small class="text-muted">(maks {{ $maxResend }}x)</small>
                        </div>
                        @endif
                    </div>
                @else
                    <p class="text-muted mb-0 small">
                        <i class="fas fa-info-circle me-1"></i>
                        Email akan dikirim otomatis saat status diubah ke status yang sesuai.
                    </p>
                @endif
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                <div class="btn-group" role="group">
                    <button type="button" 
                            class="btn btn-outline-primary btn-sm" 
                            onclick="openEmailPreview('{{ $type }}', {{ $id }})"
                            title="Preview Email">
                        <i class="fas fa-eye me-1"></i>Preview
                    </button>
                    <button type="button" 
                            class="btn btn-outline-info btn-sm" 
                            onclick="openEmailHistory('{{ $type }}', {{ $id }})"
                            title="Riwayat Email">
                        <i class="fas fa-history me-1"></i>Riwayat
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

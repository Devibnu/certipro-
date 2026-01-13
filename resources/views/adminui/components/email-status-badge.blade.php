{{--
================================================================================
CertiPro LSP - Email Status Badge Component
================================================================================
File: resources/views/adminui/components/email-status-badge.blade.php

Usage:
@include('adminui.components.email-status-badge', [
    'type' => 'pra-diterima',
    'model' => $praPendaftaran,
])
================================================================================
--}}

@php
    $type = $type ?? '';
    $model = $model ?? null;
    
    // Get email status via service
    $emailService = app(\App\Services\EmailNotificationService::class);
    $hasBeenSent = $model ? $emailService->hasBeenSent($model, $type) : false;
    $lastSent = $model ? $emailService->getLastSentTime($model, $type) : null;
    $resendCount = $model ? $emailService->getResendCount($model, $type) : 0;
@endphp

<div class="email-status-badge">
    @if($hasBeenSent)
        <span class="badge bg-success-subtle text-success border border-success-subtle">
            <i class="fas fa-check-circle me-1"></i>
            Email Terkirim
        </span>
        @if($lastSent)
            <small class="text-muted d-block mt-1">
                <i class="fas fa-clock me-1"></i>{{ $lastSent }}
            </small>
        @endif
        @if($resendCount > 0)
            <small class="text-info d-block">
                <i class="fas fa-redo me-1"></i>Resend: {{ $resendCount }}x
            </small>
        @endif
    @else
        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
            <i class="fas fa-clock me-1"></i>
            Belum Dikirim
        </span>
    @endif
</div>

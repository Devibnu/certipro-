{{--
================================================================================
CertiPro LSP - Email Action Buttons Component
================================================================================
File: resources/views/adminui/components/email-actions.blade.php

Usage:
@include('adminui.components.email-actions', [
    'type' => 'pra-diterima',  // Email type
    'id' => $praPendaftaran->id,
    'size' => 'sm',  // Optional: sm, md (default: sm)
    'showHistory' => true,  // Optional: show history button
])
================================================================================
--}}

@php
    $type = $type ?? '';
    $id = $id ?? 0;
    $size = $size ?? 'sm';
    $showHistory = $showHistory ?? true;
    $buttonClass = $size === 'sm' ? 'btn-sm' : '';
@endphp

<div class="email-actions d-inline-flex gap-1" data-email-type="{{ $type }}" data-email-id="{{ $id }}">
    {{-- Preview Button --}}
    <button type="button" 
            class="btn btn-outline-primary {{ $buttonClass }}" 
            onclick="openEmailPreview('{{ $type }}', {{ $id }})"
            title="Preview Email">
        <i class="fas fa-eye"></i>
        @if($size !== 'sm')
            <span class="ms-1">Preview</span>
        @endif
    </button>

    @if($showHistory)
    {{-- History Button --}}
    <button type="button" 
            class="btn btn-outline-info {{ $buttonClass }}" 
            onclick="openEmailHistory('{{ $type }}', {{ $id }})"
            title="Riwayat Email">
        <i class="fas fa-history"></i>
        @if($size !== 'sm')
            <span class="ms-1">Riwayat</span>
        @endif
    </button>
    @endif
</div>

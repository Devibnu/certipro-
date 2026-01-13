{{-- 
    PDF Logo Header Partial
    Untuk digunakan di semua dokumen PDF audit evidence
    Logo diambil dari database (LogoAdmin) via helper systemLogoBase64()
--}}
@php
    $logoInfo = systemLogoInfo();
    $logoBase64 = systemLogoBase64();
    $companyName = $logoInfo['nama_perusahaan'];
    $tagline = $logoInfo['tagline'] ?? null;
@endphp

<div class="logo-section">
    @if($logoBase64)
        <img src="{{ $logoBase64 }}" alt="Logo" class="logo-img" style="max-width: 60px; max-height: 60px; height: auto;">
    @else
        <div class="logo-placeholder">LSP</div>
    @endif
</div>

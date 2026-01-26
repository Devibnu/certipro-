@extends('adminui.layouts.auth')

@section('title', 'Detail Asesmen' . (systemCompanyName() ? ' - ' . systemCompanyName() . ' Admin' : ' - Admin'))

@php
    // ============================================================
    // DEFENSIVE MODE: Validate all required data before rendering
    // ============================================================
    
    // Check permissions for evidence (with fallback)
    try {
        $canUploadEvidence = auth()->check() && auth()->user()->can('evidence.upload');
        $canViewEvidence = auth()->check() && auth()->user()->can('evidence.view');
        $canDeleteEvidence = auth()->check() && auth()->user()->can('evidence.delete');
    } catch (\Throwable $e) {
        $canUploadEvidence = false;
        $canViewEvidence = false;
        $canDeleteEvidence = false;
        \Log::error('[BLADE ERROR] Permission check failed', ['error' => $e->getMessage()]);
    }
    
    // Safe access to isLocked method
    try {
        $isLocked = $asesmen && method_exists($asesmen, 'isLocked') ? $asesmen->isLocked() : false;
    } catch (\Throwable $e) {
        $isLocked = false;
        \Log::error('[BLADE ERROR] isLocked check failed', ['error' => $e->getMessage()]);
    }
    
    // Preload evidence per KUK with error handling
    try {
        $evidenceByKuk = ($asesmen && $asesmen->evidences) 
            ? $asesmen->evidences->groupBy('kuk_id') 
            : collect();
    } catch (\Throwable $e) {
        $evidenceByKuk = collect();
        \Log::error('[BLADE ERROR] Evidence grouping failed', ['error' => $e->getMessage()]);
    }
@endphp

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Info Asesmen -->
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Detail Asesmen</h6>
                            <p class="text-sm mb-0">Hasil penilaian asesmen kompetensi</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('adminui.asesmen.audit-pdf', $asesmen->id) }}" 
                               class="btn btn-outline-primary btn-sm" 
                               title="Download Audit Evidence PDF"
                               target="_blank">
                                <i class="fas fa-file-pdf me-1"></i> Audit PDF
                            </a>
                            <a href="{{ route('adminui.asesmen.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Kembali
                            </a>
                        </div>
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
                        <div class="col-md-6">
                            <h6 class="text-uppercase text-secondary text-xs font-weight-bolder mb-3">Informasi Pendaftaran</h6>
                            @if($asesmen->pendaftaran)
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-secondary" width="150">No. Pendaftaran</td>
                                    <td><strong>{{ $asesmen->pendaftaran->nomor_pendaftaran ?? '-' }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Nama Asesi</td>
                                    <td><strong>{{ $asesmen->pendaftaran->asesi_name ?? '-' }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Email</td>
                                    <td>{{ $asesmen->pendaftaran->asesi_email ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Skema</td>
                                    <td>
                                        @if(optional($asesmen->pendaftaran)->skemaSertifikasi)
                                            <span class="badge bg-gradient-info">{{ $asesmen->pendaftaran->skemaSertifikasi->kode_skema ?? '-' }}</span>
                                            {{ $asesmen->pendaftaran->skemaSertifikasi->nama_skema ?? '-' }}
                                        @else
                                            <span class="text-muted">Skema belum ditentukan</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Status Pendaftaran</td>
                                    <td>
                                        <span class="badge badge-sm {{ $asesmen->pendaftaran->status_badge ?? 'bg-gradient-secondary' }}">
                                            {{ $asesmen->pendaftaran->status_label ?? 'N/A' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                            @else
                            <div class="alert alert-warning" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Data pendaftaran tidak tersedia.
                            </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-uppercase text-secondary text-xs font-weight-bolder mb-3">Informasi Asesmen</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-secondary" width="150">Asesor</td>
                                    <td><strong>{{ optional($asesmen->asesor)->name ?? '-' }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Tanggal Asesmen</td>
                                    <td>{{ optional($asesmen->tanggal_asesmen)->format('d F Y') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Metode Asesmen</td>
                                    <td>
                                        @php
                                            try {
                                                $metodeLabel = $asesmen->metode_label ?? 'N/A';
                                            } catch (\Throwable $e) {
                                                $metodeLabel = 'N/A';
                                            }
                                        @endphp
                                        <span class="badge bg-gradient-secondary">{{ $metodeLabel }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Status Asesmen</td>
                                    <td>
                                        @php
                                            try {
                                                $isSelesai = method_exists($asesmen, 'isSelesai') && $asesmen->isSelesai();
                                                $statusLabel = $asesmen->status_label ?? 'N/A';
                                                $badgeClass = $isSelesai ? 'bg-gradient-success' : 'bg-gradient-warning';
                                            } catch (\Throwable $e) {
                                                $isSelesai = false;
                                                $statusLabel = 'N/A';
                                                $badgeClass = 'bg-gradient-secondary';
                                            }
                                        @endphp
                                        <span class="badge badge-sm {{ $badgeClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Hasil</td>
                                    <td>
                                        @php
                                            try {
                                                $isAllKompeten = method_exists($asesmen, 'isAllKompeten') && $asesmen->isAllKompeten();
                                            } catch (\Throwable $e) {
                                                $isAllKompeten = false;
                                                \Log::error('[BLADE ERROR] isAllKompeten check failed', ['error' => $e->getMessage()]);
                                            }
                                        @endphp
                                        @if($isAllKompeten)
                                            <span class="badge badge-lg bg-gradient-success">
                                                <i class="fas fa-check-circle me-1"></i> KOMPETEN
                                            </span>
                                        @else
                                            <span class="badge badge-lg bg-gradient-danger">
                                                <i class="fas fa-times-circle me-1"></i> BELUM KOMPETEN
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                            @if($asesmen->catatan_asesor)
                            <div class="mt-3">
                                <h6 class="text-uppercase text-secondary text-xs font-weight-bolder mb-2">Catatan Asesor</h6>
                                <p class="text-sm bg-light p-3 rounded">{{ $asesmen->catatan_asesor }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sampling Audit Section (Quality Control) -->
            @include('adminui.asesmen.partials.sampling-section', ['asesmen' => $asesmen])

            <!-- Hasil Penilaian per Unit -->
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Hasil Penilaian per Unit Kompetensi</h6>
                    <p class="text-sm text-secondary mb-0">Detail penilaian untuk setiap Kriteria Unjuk Kerja (KUK)</p>
                </div>
                <div class="card-body">
                    @if($detailsByUnit && $detailsByUnit->count() > 0)
                    <div class="accordion" id="accordionUnits">
                        @php $unitIndex = 0; @endphp
                        @foreach($detailsByUnit as $unitId => $details)
                        @php 
                            try {
                                $unit = optional($details->first())->unitKompetensi;
                                
                                // Safe check for allKompeten
                                $allKompeten = false;
                                try {
                                    $allKompeten = $details->every(function($d) {
                                        return method_exists($d, 'isKompeten') && $d->isKompeten();
                                    });
                                } catch (\Throwable $e) {
                                    \Log::error('[BLADE ERROR] Failed to check kompeten status', [
                                        'unit_id' => $unitId,
                                        'error' => $e->getMessage()
                                    ]);
                                }
                            } catch (\Throwable $e) {
                                $unit = null;
                                $allKompeten = false;
                                \Log::error('[BLADE ERROR] Failed to process unit', [
                                    'unit_id' => $unitId,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        @endphp
                        @if($unit)
                        <div class="accordion-item border mb-3 rounded">
                            <h2 class="accordion-header" id="heading{{ $unitId }}">
                                <button class="accordion-button {{ $unitIndex > 0 ? 'collapsed' : '' }}" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#collapse{{ $unitId }}" 
                                        aria-expanded="{{ $unitIndex == 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $unitId }}">
                                    <div class="d-flex align-items-center w-100">
                                        <div>
                                            <span class="badge bg-gradient-primary me-2">{{ $unit->kode_unit ?? 'N/A' }}</span>
                                            <strong>{{ $unit->judul_unit ?? 'Unit Kompetensi' }}</strong>
                                        </div>
                                        <div class="ms-auto me-3">
                                            @if($allKompeten)
                                                <span class="badge bg-gradient-success">
                                                    <i class="fas fa-check"></i> Kompeten
                                                </span>
                                            @else
                                                <span class="badge bg-gradient-danger">
                                                    <i class="fas fa-times"></i> Belum Kompeten
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse{{ $unitId }}" class="accordion-collapse collapse {{ $unitIndex == 0 ? 'show' : '' }}" 
                                 aria-labelledby="heading{{ $unitId }}" data-bs-parent="#accordionUnits">
                                <div class="accordion-body">
                                    @if($isLocked)
                                    <div class="alert alert-info alert-sm py-2 mb-3" role="alert">
                                        <i class="fas fa-lock me-1"></i>
                                        <span class="text-sm">Asesmen telah dikunci. Evidence tidak dapat ditambah/dihapus.</span>
                                    </div>
                                    @endif
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder" width="100">Kode KUK</th>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Kriteria Unjuk Kerja</th>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-center" width="120">Hasil</th>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder" width="150">Catatan</th>
                                                    @if($canViewEvidence)
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder" width="250">Evidence</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($details as $detail)
                                                @php
                                                    try {
                                                        $kuk = $detail->kuk ?? null;
                                                        $kukEvidence = $evidenceByKuk->get($detail->kuk_id ?? 0, collect());
                                                        
                                                        // Safe badge access
                                                        $hasilBadge = 'bg-gradient-secondary';
                                                        $hasilLabel = 'N/A';
                                                        $isKompetenCheck = false;
                                                        
                                                        try {
                                                            if (isset($detail->hasil_badge)) {
                                                                $hasilBadge = $detail->hasil_badge;
                                                            }
                                                            if (isset($detail->hasil_label)) {
                                                                $hasilLabel = $detail->hasil_label;
                                                            }
                                                            if (method_exists($detail, 'isKompeten')) {
                                                                $isKompetenCheck = $detail->isKompeten();
                                                            }
                                                        } catch (\Throwable $e) {
                                                            \Log::error('[BLADE ERROR] Detail badge access failed', [
                                                                'detail_id' => $detail->id ?? 'unknown',
                                                                'error' => $e->getMessage()
                                                            ]);
                                                        }
                                                    } catch (\Throwable $e) {
                                                        $kuk = null;
                                                        $kukEvidence = collect();
                                                        \Log::error('[BLADE ERROR] Detail processing failed', [
                                                            'error' => $e->getMessage()
                                                        ]);
                                                    }
                                                @endphp
                                                @if($kuk)
                                                <tr>
                                                    <td class="align-middle">
                                                        <span class="text-sm font-weight-bold">{{ $kuk->kode_kuk ?? 'N/A' }}</span>
                                                    </td>
                                                    <td class="align-middle">
                                                        <p class="text-sm mb-0">{{ $kuk->deskripsi ?? 'Deskripsi tidak tersedia' }}</p>
                                                    </td>
                                                    <td class="align-middle text-center">
                                                        <span class="badge {{ $hasilBadge }}">
                                                            @if($isKompetenCheck)
                                                                <i class="fas fa-check me-1"></i>
                                                            @else
                                                                <i class="fas fa-times me-1"></i>
                                                            @endif
                                                            {{ $hasilLabel }}
                                                        </span>
                                                    </td>
                                                    <td class="align-middle">
                                                        <span class="text-xs text-secondary">{{ $detail->catatan ?? '-' }}</span>
                                                    </td>
                                                    @if($canViewEvidence)
                                                    <td class="align-middle">
                                                        @php
                                                            try {
                                                                $includeData = [
                                                                    'asesmenId' => $asesmen->id ?? 0,
                                                                    'kukId' => $detail->kuk_id ?? 0,
                                                                    'kukKode' => optional($kuk)->kode_kuk ?? 'N/A',
                                                                    'evidences' => $kukEvidence,
                                                                    'isLocked' => $isLocked,
                                                                    'canUpload' => $canUploadEvidence,
                                                                    'canDelete' => $canDeleteEvidence,
                                                                ];
                                                            } catch (\Throwable $e) {
                                                                $includeData = [];
                                                            }
                                                        @endphp
                                                        @if(!empty($includeData))
                                                            @include('adminui.asesmen.partials.evidence-kuk', $includeData)
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    @endif
                                                </tr>
                                                @else
                                                <tr>
                                                    <td colspan="{{ $canViewEvidence ? 5 : 4 }}" class="text-center text-muted py-2">
                                                        <small><i class="fas fa-exclamation-circle me-1"></i> Data KUK tidak tersedia</small>
                                                    </td>
                                                </tr>
                                                @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @php $unitIndex++; @endphp
                        @else
                        <div class="alert alert-warning mb-3" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Data unit kompetensi tidak tersedia untuk detail penilaian ini.
                        </div>
                        @endif
                        @endforeach
                    </div>
                    @else
                    {{-- Empty state jika tidak ada details --}}
                    <div class="text-center py-5">
                        <i class="fas fa-inbox text-secondary fa-4x mb-3"></i>
                        <h5 class="text-secondary">Belum Ada Detail Penilaian</h5>
                        <p class="text-sm text-muted">
                            Detail penilaian KUK belum tersedia atau belum lengkap untuk asesmen ini.
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Summary -->
            <div class="card">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Ringkasan Penilaian</h6>
                    <p class="text-sm text-secondary mb-0">Rekapitulasi hasil asesmen kompetensi</p>
                </div>
                <div class="card-body">
                    @php
                        // Hitung statistik penilaian
                        $totalKuk = $asesmen->details->count();
                        $kompetenCount = $asesmen->details->where('hasil', 'kompeten')->count();
                        $belumKompetenCount = $asesmen->details->where('hasil', 'belum_kompeten')->count();
                        $kompetenPercentage = $totalKuk > 0 ? round(($kompetenCount / $totalKuk) * 100) : 0;
                        
                        // Tentukan warna dan label berdasarkan persentase
                        if ($kompetenPercentage == 100) {
                            $statusColor = 'success';
                            $statusGradient = 'bg-gradient-success';
                            $statusLabel = 'KOMPETEN';
                            $statusIcon = 'fa-check-circle';
                            $statusBg = '#e6f9f0';
                        } elseif ($kompetenPercentage > 0) {
                            $statusColor = 'warning';
                            $statusGradient = 'bg-gradient-warning';
                            $statusLabel = 'SEBAGIAN KOMPETEN';
                            $statusIcon = 'fa-exclamation-circle';
                            $statusBg = '#fff9e6';
                        } else {
                            $statusColor = 'danger';
                            $statusGradient = 'bg-gradient-danger';
                            $statusLabel = 'BELUM KOMPETEN';
                            $statusIcon = 'fa-times-circle';
                            $statusBg = '#ffeef0';
                        }
                    @endphp
                    
                    <div class="row">
                        <!-- Statistik Kiri -->
                        <div class="col-md-6">
                            <div class="row g-3">
                                <!-- Card Total KUK -->
                                <div class="col-md-4">
                                    <div class="card border shadow-none h-100">
                                        <div class="card-body text-center py-3">
                                            <i class="fas fa-list-check text-primary fa-2x mb-2"></i>
                                            <h3 class="mb-0 font-weight-bold">{{ $totalKuk }}</h3>
                                            <p class="text-xs text-secondary mb-0">Total KUK</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Card Kompeten -->
                                <div class="col-md-4">
                                    <div class="card border border-success shadow-none h-100" style="background: #e6f9f0;">
                                        <div class="card-body text-center py-3">
                                            <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                                            <h3 class="mb-0 font-weight-bold text-success">{{ $kompetenCount }}</h3>
                                            <p class="text-xs text-success mb-0">Kompeten</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Card Belum Kompeten -->
                                <div class="col-md-4">
                                    <div class="card border border-danger shadow-none h-100" style="background: #ffeef0;">
                                        <div class="card-body text-center py-3">
                                            <i class="fas fa-times-circle text-danger fa-2x mb-2"></i>
                                            <h3 class="mb-0 font-weight-bold text-danger">{{ $belumKompetenCount }}</h3>
                                            <p class="text-xs text-danger mb-0">Belum Kompeten</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Progress Bar Persentase -->
                            <div class="mt-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-sm font-weight-bold">Persentase Kompeten</span>
                                    <span class="badge {{ $statusGradient }} px-3">{{ $kompetenPercentage }}%</span>
                                </div>
                                <div class="progress" style="height: 24px; border-radius: 12px; background: #e9ecef;">
                                    <div class="progress-bar {{ $statusGradient }}" role="progressbar" 
                                         style="width: {{ $kompetenPercentage }}%; border-radius: 12px; font-weight: 600; font-size: 13px;" 
                                         aria-valuenow="{{ $kompetenPercentage }}" aria-valuemin="0" aria-valuemax="100">
                                        @if($kompetenPercentage >= 15)
                                            {{ $kompetenPercentage }}%
                                        @endif
                                    </div>
                                </div>
                                <p class="text-xs text-secondary mt-1 mb-0">
                                    {{ $kompetenCount }} dari {{ $totalKuk }} kriteria dinilai kompeten
                                </p>
                            </div>
                        </div>
                        
                        <!-- Hasil Akhir Kanan -->
                        <div class="col-md-6">
                            <div class="card border border-{{ $statusColor }} shadow-none h-100" style="background: {{ $statusBg }};">
                                <div class="card-body text-center d-flex flex-column justify-content-center">
                                    <p class="text-xs text-secondary text-uppercase mb-2">Hasil Asesmen</p>
                                    <i class="fas {{ $statusIcon }} text-{{ $statusColor }} fa-4x mb-3"></i>
                                    <h2 class="font-weight-bold text-{{ $statusColor }} mb-2">
                                        {{ $kompetenPercentage }}%
                                    </h2>
                                    <span class="badge {{ $statusGradient }} badge-lg px-4 py-2" style="font-size: 16px;">
                                        {{ $statusLabel }}
                                    </span>
                                    <p class="text-sm text-secondary mt-3 mb-0">
                                        @if($kompetenPercentage == 100)
                                            Seluruh kriteria unjuk kerja telah dipenuhi
                                        @elseif($kompetenPercentage > 0)
                                            {{ $belumKompetenCount }} kriteria masih perlu perbaikan
                                        @else
                                            Seluruh kriteria belum memenuhi standar
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                        <div>
                            <span class="text-xs text-secondary">
                                <i class="fas fa-info-circle me-1"></i>
                                Asesmen dilakukan pada {{ $asesmen->tanggal_asesmen->format('d F Y') }} oleh {{ $asesmen->asesor->name ?? '-' }}
                            </span>
                        </div>
                        <div>
                            <a href="{{ route('adminui.asesmen.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
                            </a>
                            @if($kompetenPercentage == 100 && $asesmen->pendaftaran->status == 'menunggu_keputusan')
                            <a href="#" class="btn bg-gradient-success">
                                <i class="fas fa-gavel me-1"></i> Lanjut ke Keputusan
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Evidence Modals --}}
@if($canViewEvidence)
    @include('adminui.asesmen.partials.evidence-modals', ['asesmenId' => $asesmen->id])
@endif
@endsection

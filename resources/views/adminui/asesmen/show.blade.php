@extends('adminui.layouts.auth')

@section('title', 'Detail Asesmen' . (systemCompanyName() ? ' - ' . systemCompanyName() . ' Admin' : ' - Admin'))

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
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-secondary" width="150">No. Pendaftaran</td>
                                    <td><strong>{{ $asesmen->pendaftaran->nomor_pendaftaran }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Nama Asesi</td>
                                    <td><strong>{{ $asesmen->pendaftaran->asesi_name }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Email</td>
                                    <td>{{ $asesmen->pendaftaran->asesi_email }}</td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Skema</td>
                                    <td>
                                        <span class="badge bg-gradient-info">{{ $asesmen->pendaftaran->skemaSertifikasi->kode_skema ?? '-' }}</span>
                                        {{ $asesmen->pendaftaran->skemaSertifikasi->nama_skema ?? '-' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Status Pendaftaran</td>
                                    <td>
                                        <span class="badge badge-sm {{ $asesmen->pendaftaran->status_badge }}">
                                            {{ $asesmen->pendaftaran->status_label }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-uppercase text-secondary text-xs font-weight-bolder mb-3">Informasi Asesmen</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-secondary" width="150">Asesor</td>
                                    <td><strong>{{ $asesmen->asesor->name ?? '-' }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Tanggal Asesmen</td>
                                    <td>{{ $asesmen->tanggal_asesmen->format('d F Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Metode Asesmen</td>
                                    <td>
                                        <span class="badge bg-gradient-secondary">{{ $asesmen->metode_label }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Status Asesmen</td>
                                    <td>
                                        <span class="badge badge-sm {{ $asesmen->isSelesai() ? 'bg-gradient-success' : 'bg-gradient-warning' }}">
                                            {{ $asesmen->status_label }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Hasil</td>
                                    <td>
                                        @if($asesmen->isAllKompeten())
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

            <!-- Hasil Penilaian per Unit -->
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Hasil Penilaian per Unit Kompetensi</h6>
                    <p class="text-sm text-secondary mb-0">Detail penilaian untuk setiap Kriteria Unjuk Kerja (KUK)</p>
                </div>
                <div class="card-body">
                    <div class="accordion" id="accordionUnits">
                        @php $unitIndex = 0; @endphp
                        @foreach($detailsByUnit as $unitId => $details)
                        @php 
                            $unit = $details->first()->unitKompetensi;
                            $allKompeten = $details->every(fn($d) => $d->isKompeten());
                        @endphp
                        <div class="accordion-item border mb-3 rounded">
                            <h2 class="accordion-header" id="heading{{ $unitId }}">
                                <button class="accordion-button {{ $unitIndex > 0 ? 'collapsed' : '' }}" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#collapse{{ $unitId }}" 
                                        aria-expanded="{{ $unitIndex == 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $unitId }}">
                                    <div class="d-flex align-items-center w-100">
                                        <div>
                                            <span class="badge bg-gradient-primary me-2">{{ $unit->kode_unit }}</span>
                                            <strong>{{ $unit->judul_unit }}</strong>
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
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder" width="120">Kode KUK</th>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Kriteria Unjuk Kerja</th>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-center" width="150">Hasil</th>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder" width="200">Catatan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($details as $detail)
                                                <tr>
                                                    <td class="align-middle">
                                                        <span class="text-sm font-weight-bold">{{ $detail->kuk->kode_kuk }}</span>
                                                    </td>
                                                    <td class="align-middle">
                                                        <p class="text-sm mb-0">{{ $detail->kuk->deskripsi }}</p>
                                                    </td>
                                                    <td class="align-middle text-center">
                                                        <span class="badge {{ $detail->hasil_badge }}">
                                                            @if($detail->isKompeten())
                                                                <i class="fas fa-check me-1"></i>
                                                            @else
                                                                <i class="fas fa-times me-1"></i>
                                                            @endif
                                                            {{ $detail->hasil_label }}
                                                        </span>
                                                    </td>
                                                    <td class="align-middle">
                                                        <span class="text-sm text-secondary">{{ $detail->catatan ?? '-' }}</span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @php $unitIndex++; @endphp
                        @endforeach
                    </div>

                    @if($detailsByUnit->isEmpty())
                    <div class="text-center py-4">
                        <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                        <p class="text-sm">Tidak ada detail penilaian.</p>
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
@endsection

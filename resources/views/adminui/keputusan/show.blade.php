@extends('adminui.layouts.auth')

@section('title', 'Review Keputusan Sertifikasi' . (systemCompanyName() ? ' - ' . systemCompanyName() . ' Admin' : ' - Admin'))

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Review Keputusan Sertifikasi</h6>
                            <p class="text-sm mb-0">Tetapkan keputusan akhir berdasarkan hasil asesmen</p>
                        </div>
                        <div class="d-flex gap-2">
                            @if($keputusan)
                            <a href="{{ route('adminui.keputusan.audit-pdf', $pendaftaran->id) }}" 
                               class="btn btn-outline-primary btn-sm" 
                               title="Download Audit Evidence PDF"
                               target="_blank">
                                <i class="fas fa-file-pdf me-1"></i> Audit PDF
                            </a>
                            @endif
                            <a href="{{ route('adminui.keputusan.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    <span class="text-white">{{ session('success') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <span class="text-white">{{ session('error') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Lock Warning -->
            @if($isLocked)
            <div class="alert alert-warning" role="alert">
                <i class="fas fa-lock me-2"></i>
                <strong>Keputusan Terkunci!</strong> Keputusan ini sudah ditetapkan dan tidak dapat diubah.
            </div>
            @endif

            <!-- Info Pendaftaran & Asesi -->
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6 class="text-uppercase text-secondary text-xs font-weight-bolder mb-0">Informasi Pendaftaran</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-secondary" width="150">No. Pendaftaran</td>
                                    <td><strong>{{ $pendaftaran->nomor_pendaftaran }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Nama Asesi</td>
                                    <td><strong>{{ $pendaftaran->user->name ?? '-' }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Email</td>
                                    <td>{{ $pendaftaran->user->email ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Tanggal Daftar</td>
                                    <td>{{ $pendaftaran->tanggal_daftar->format('d F Y') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-secondary" width="150">Skema</td>
                                    <td>
                                        <span class="badge bg-gradient-info">{{ $pendaftaran->skemaSertifikasi->kode_skema ?? '-' }}</span>
                                        {{ $pendaftaran->skemaSertifikasi->nama_skema ?? '-' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Status</td>
                                    <td>
                                        <span class="badge badge-sm {{ $pendaftaran->status_badge }}">
                                            {{ $pendaftaran->status_label }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Asesor</td>
                                    <td><strong>{{ $asesmen->asesor->name ?? '-' }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Tanggal Asesmen</td>
                                    <td>{{ $asesmen->tanggal_asesmen ? $asesmen->tanggal_asesmen->format('d F Y') : '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ringkasan Asesmen -->
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="text-uppercase text-secondary text-xs font-weight-bolder mb-0">Ringkasan Hasil Asesmen</h6>
                        @php
                            $totalKuk = $asesmen->details->count();
                            $kompetenCount = $asesmen->details->where('hasil', 'kompeten')->count();
                            $belumKompetenCount = $asesmen->details->where('hasil', 'belum_kompeten')->count();
                            $kompetenPercentage = $totalKuk > 0 ? round(($kompetenCount / $totalKuk) * 100) : 0;
                        @endphp
                        <div>
                            @if($asesmen->isAllKompeten())
                                <span class="badge badge-lg bg-gradient-success">
                                    <i class="fas fa-check-circle me-1"></i> Rekomendasi: KOMPETEN
                                </span>
                            @else
                                <span class="badge badge-lg bg-gradient-danger">
                                    <i class="fas fa-times-circle me-1"></i> Rekomendasi: BELUM KOMPETEN
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-gradient-secondary">
                                <div class="card-body p-3 text-center text-white">
                                    <h3 class="mb-0">{{ $totalKuk }}</h3>
                                    <p class="mb-0 text-sm">Total KUK</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-gradient-success">
                                <div class="card-body p-3 text-center text-white">
                                    <h3 class="mb-0">{{ $kompetenCount }}</h3>
                                    <p class="mb-0 text-sm">Kompeten</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-gradient-danger">
                                <div class="card-body p-3 text-center text-white">
                                    <h3 class="mb-0">{{ $belumKompetenCount }}</h3>
                                    <p class="mb-0 text-sm">Belum Kompeten</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-gradient-info">
                                <div class="card-body p-3 text-center text-white">
                                    <h3 class="mb-0">{{ $kompetenPercentage }}%</h3>
                                    <p class="mb-0 text-sm">Persentase Kompeten</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($asesmen->catatan_asesor)
                    <div class="mb-3">
                        <h6 class="text-uppercase text-secondary text-xs font-weight-bolder mb-2">Catatan Asesor</h6>
                        <p class="text-sm bg-light p-3 rounded">{{ $asesmen->catatan_asesor }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Detail Penilaian per Unit Kompetensi (READ-ONLY) -->
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6 class="text-uppercase text-secondary text-xs font-weight-bolder mb-0">Detail Penilaian per Unit Kompetensi</h6>
                    <p class="text-sm text-secondary mb-0">Data ini bersifat read-only dan tidak dapat diubah</p>
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
                </div>
            </div>

            <!-- Form Keputusan atau Detail Keputusan (jika sudah locked) -->
            @if($isLocked && $keputusan)
            <!-- Keputusan yang sudah dikunci -->
            <div class="card border border-2 {{ $keputusan->isKompeten() ? 'border-success' : 'border-danger' }}">
                <div class="card-header pb-0 {{ $keputusan->isKompeten() ? 'bg-gradient-success' : 'bg-gradient-danger' }}">
                    <h6 class="text-white mb-0">
                        <i class="fas fa-lock me-2"></i> Keputusan Final
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-secondary" width="180">Keputusan</td>
                                    <td>
                                        <span class="badge badge-lg {{ $keputusan->keputusan_badge }}">
                                            @if($keputusan->isKompeten())
                                                <i class="fas fa-check-circle me-1"></i>
                                            @else
                                                <i class="fas fa-times-circle me-1"></i>
                                            @endif
                                            {{ $keputusan->keputusan_label }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Tanggal Keputusan</td>
                                    <td><strong>{{ $keputusan->tanggal_keputusan->format('d F Y') }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary">Ditetapkan Oleh</td>
                                    <td><strong>{{ $keputusan->penetap->name ?? '-' }}</strong></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            @if($keputusan->catatan_komite)
                            <h6 class="text-uppercase text-secondary text-xs font-weight-bolder mb-2">Catatan Komite</h6>
                            <p class="text-sm bg-light p-3 rounded">{{ $keputusan->catatan_komite }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @else
            {{-- Form Keputusan - Only show if user has keputusan.approve permission --}}
            @if(auth()->user()->hasPermission('keputusan.approve'))
            <form action="{{ route('adminui.keputusan.simpan', $pendaftaran->id) }}" method="POST" id="formKeputusan">
                @csrf
                <div class="card border border-2 border-danger">
                    <div class="card-header pb-0 bg-gradient-danger">
                        <h6 class="text-white mb-0">
                            <i class="fas fa-gavel me-2"></i> Keputusan Komite Teknis
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning mb-4" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Perhatian!</strong> Keputusan yang disimpan akan <strong>TERKUNCI</strong> dan tidak dapat diubah. Pastikan keputusan sudah tepat.
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label class="form-label">Keputusan Akhir <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="keputusan" 
                                                   id="kompeten" value="kompeten" 
                                                   {{ old('keputusan') == 'kompeten' ? 'checked' : '' }} required>
                                            <label class="form-check-label text-success fw-bold" for="kompeten">
                                                <i class="fas fa-check-circle me-1"></i> KOMPETEN
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="keputusan" 
                                                   id="belum_kompeten" value="belum_kompeten"
                                                   {{ old('keputusan') == 'belum_kompeten' ? 'checked' : '' }}>
                                            <label class="form-check-label text-danger fw-bold" for="belum_kompeten">
                                                <i class="fas fa-times-circle me-1"></i> BELUM KOMPETEN
                                            </label>
                                        </div>
                                    </div>
                                    @error('keputusan')
                                        <div class="text-danger text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Catatan Komite Teknis (Opsional)</label>
                                    <textarea name="catatan_komite" class="form-control @error('catatan_komite') is-invalid @enderror" 
                                              rows="4" placeholder="Catatan atau pertimbangan komite...">{{ old('catatan_komite') }}</textarea>
                                    @error('catatan_komite')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between align-items-center">
                            <p class="text-sm text-secondary mb-0">
                                <i class="fas fa-info-circle me-1"></i>
                                Keputusan ini akan menjadi dasar penerbitan sertifikat (jika kompeten).
                            </p>
                            <div>
                                <a href="{{ route('adminui.keputusan.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> Batal
                                </a>
                                <button type="submit" class="btn bg-gradient-danger" onclick="return confirmKeputusan()">
                                    <i class="fas fa-lock me-1"></i> Simpan & Kunci Keputusan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            @else
            {{-- No permission to approve - show info only --}}
            <div class="card border border-2 border-secondary">
                <div class="card-header pb-0 bg-gradient-secondary">
                    <h6 class="text-white mb-0">
                        <i class="fas fa-info-circle me-2"></i> Menunggu Keputusan Komite Teknis
                    </h6>
                </div>
                <div class="card-body text-center py-4">
                    <i class="fas fa-hourglass-half fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">Keputusan sertifikasi belum ditetapkan oleh Komite Teknis.</p>
                    <p class="text-xs text-muted">Anda tidak memiliki izin untuk membuat keputusan sertifikasi.</p>
                </div>
            </div>
            @endif
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmKeputusan() {
    const keputusan = document.querySelector('input[name="keputusan"]:checked');
    
    if (!keputusan) {
        Swal.fire({
            icon: 'warning',
            title: 'Keputusan Belum Dipilih',
            html: `
                <div style="text-align: center;">
                    <p style="color: #666; margin-bottom: 15px;">Anda belum memilih keputusan sertifikasi.</p>
                    <div style="background: #fff3cd; border-radius: 10px; padding: 15px; border-left: 4px solid #ffc107;">
                        <p style="margin: 0; font-size: 14px; color: #856404;">
                            <strong>Silakan pilih salah satu:</strong><br>
                            ✅ KOMPETEN atau ❌ BELUM KOMPETEN
                        </p>
                    </div>
                </div>
            `,
            confirmButtonColor: '#5e72e4',
            confirmButtonText: 'Mengerti'
        });
        return false;
    }
    
    const isKompeten = keputusan.value === 'kompeten';
    const keputusanLabel = isKompeten ? 'KOMPETEN' : 'BELUM KOMPETEN';
    const iconType = isKompeten ? 'success' : 'error';
    const badgeColor = isKompeten ? '#2dce89' : '#f5365c';
    const bgColor = isKompeten ? '#e6f9f0' : '#ffeef0';
    const emoji = isKompeten ? '✅' : '❌';
    
    Swal.fire({
        title: '<strong>Konfirmasi Keputusan Sertifikasi</strong>',
        html: `
            <div style="text-align: center; padding: 10px 0;">
                <p style="margin-bottom: 15px; color: #555;">Anda akan menetapkan keputusan:</p>
                
                <div style="background: ${bgColor}; border-radius: 15px; padding: 20px; margin-bottom: 20px;">
                    <span style="font-size: 40px;">${emoji}</span>
                    <h3 style="color: ${badgeColor}; margin: 10px 0 0 0; font-weight: 700;">${keputusanLabel}</h3>
                </div>
                
                <div style="background: #f8d7da; border-radius: 10px; padding: 15px; border-left: 4px solid #dc3545;">
                    <p style="margin: 0; font-size: 13px; color: #721c24;">
                        <strong>⚠️ PERINGATAN:</strong><br>
                        Keputusan ini akan <strong>TERKUNCI</strong> dan tidak dapat diubah setelah disimpan.
                    </p>
                </div>
            </div>
        `,
        icon: 'question',
        iconColor: '#5e72e4',
        showCancelButton: true,
        confirmButtonColor: badgeColor,
        cancelButtonColor: '#6c757d',
        confirmButtonText: `${emoji} Ya, Tetapkan ${keputusanLabel}`,
        cancelButtonText: '✗ Batal',
        reverseButtons: true,
        width: 480
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Menyimpan Keputusan...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            document.getElementById('formKeputusan').submit();
        }
    });
    
    return false;
}
</script>
@endpush

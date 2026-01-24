@extends('adminui.layouts.auth')

@section('title', 'Sertifikat' . (systemCompanyName() ? ' - ' . systemCompanyName() . ' Admin' : ' - Admin'))

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h6>Sertifikat Sertifikasi</h6>
                        <p class="text-sm mb-0">Kelola sertifikat yang sudah diterbitkan dan terbitkan sertifikat baru</p>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible mx-4 mt-3" role="alert">
                            <span class="text-white">{{ session('success') }}</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible mx-4 mt-3" role="alert">
                            <span class="text-white">{{ session('error') }}</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if(session('warning'))
                        <div class="alert alert-warning alert-dismissible mx-4 mt-3" role="alert">
                            <span class="text-white">{{ session('warning') }}</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Tabs Navigation -->
                    <ul class="nav nav-tabs mx-4 mt-3" id="sertifikatTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="terbit-tab" data-bs-toggle="tab" 
                                    data-bs-target="#terbit" type="button" role="tab">
                                <i class="fas fa-certificate me-2"></i>Sertifikat Diterbitkan 
                                <span class="badge bg-success ms-1">{{ $sertifikats->total() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pending-tab" data-bs-toggle="tab" 
                                    data-bs-target="#pending" type="button" role="tab">
                                <i class="fas fa-clock me-2"></i>Menunggu Penerbitan 
                                <span class="badge bg-warning ms-1">{{ $pendaftaranKompeten->count() }}</span>
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="sertifikatTabContent">
                        <!-- Tab: Sertifikat Diterbitkan -->
                        <div class="tab-pane fade show active" id="terbit" role="tabpanel">
                            <!-- Search -->
                            <div class="px-4 pt-3 pb-2">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-6">
                                        <input type="text" name="search" class="form-control" 
                                               placeholder="Cari nomor sertifikat, nama, atau skema..." 
                                               value="{{ request('search') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <button type="submit" class="btn bg-gradient-primary btn-sm">
                                            <i class="fas fa-search"></i> Cari
                                        </button>
                                        <a href="{{ route('adminui.sertifikat.index') }}" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-times"></i> Reset
                                        </a>
                                    </div>
                                </form>
                            </div>

                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nomor Sertifikat</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Pemegang</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Skema Sertifikasi</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tanggal Terbit</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Berlaku Sampai</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($sertifikats as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-3 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm font-weight-bold">{{ $item->nomor_sertifikat }}</h6>
                                                        <p class="text-xs text-secondary mb-0">{{ $item->pendaftaran->nomor_pendaftaran ?? '-' }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0">{{ $item->nama_peserta }}</p>
                                                <p class="text-xs text-secondary mb-0">{{ $item->pendaftaran->user->email ?? '-' }}</p>
                                            </td>
                                            <td>
                                                <span class="badge badge-sm bg-gradient-info">
                                                    {{ $item->pendaftaran->skemaSertifikasi->kode_skema ?? '-' }}
                                                </span>
                                                <p class="text-xs text-secondary mb-0 mt-1">{{ $item->skema_sertifikasi }}</p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-sm">{{ $item->tanggal_terbit->format('d M Y') }}</span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-sm">{{ $item->tanggal_berlaku_sampai->format('d M Y') }}</span>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <span class="badge badge-sm {{ $item->status_validitas_badge }}">
                                                    {{ $item->status_validitas }}
                                                </span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <a href="{{ route('adminui.sertifikat.show', $item->id) }}" 
                                                   class="btn btn-link text-info px-2 mb-0" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('adminui.sertifikat.download', $item->id) }}" 
                                                   class="btn btn-link text-success px-2 mb-0" title="Download PDF">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <i class="fas fa-certificate fa-3x text-muted mb-3"></i>
                                                <p class="text-muted mb-0">Belum ada sertifikat yang diterbitkan</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            @if($sertifikats->hasPages())
                            <div class="px-4 pt-3">
                                {{ $sertifikats->appends(request()->query())->links() }}
                            </div>
                            @endif
                        </div>

                        <!-- Tab: Menunggu Penerbitan -->
                        <div class="tab-pane fade" id="pending" role="tabpanel">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No. Pendaftaran</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Asesi</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Skema Sertifikasi</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tgl Keputusan</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($pendaftaranKompeten as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-3 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm font-weight-bold">{{ $item->nomor_pendaftaran }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0">{{ $item->user->name ?? '-' }}</p>
                                                <p class="text-xs text-secondary mb-0">{{ $item->user->email ?? '-' }}</p>
                                            </td>
                                            <td>
                                                <span class="badge badge-sm bg-gradient-info">
                                                    {{ $item->skemaSertifikasi->kode_skema ?? '-' }}
                                                </span>
                                                <p class="text-xs text-secondary mb-0 mt-1">{{ $item->skemaSertifikasi->nama_skema ?? '-' }}</p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-sm">{{ $item->keputusan->tanggal_keputusan ? $item->keputusan->tanggal_keputusan->format('d M Y') : '-' }}</span>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <span class="badge badge-sm bg-gradient-success">
                                                    <i class="fas fa-check me-1"></i> KOMPETEN FINAL
                                                </span>
                                            </td>
                                            <td class="align-middle text-center">
                                                @if(auth()->user()->hasPermission('sertifikat.generate'))
                                                    @if($item->keputusan)
                                                        {{-- Keputusan ada - tombol aktif --}}
                                                        <form action="{{ route('adminui.sertifikat.terbit', $item->id) }}" 
                                                              method="POST" 
                                                              class="d-inline sertifikat-terbit-form"
                                                              data-peserta-nama="{{ $item->user?->name ?? 'peserta ini' }}"
                                                              data-nomor-pendaftaran="{{ $item->nomor_pendaftaran }}"
                                                              data-skema="{{ $item->skemaSertifikasi?->nama_skema ?? '-' }}"
                                                              data-tanggal-keputusan="{{ $item->keputusan->tanggal_keputusan?->format('d M Y') ?? '-' }}"
                                                              data-penetap="{{ $item->keputusan->penetap?->name ?? 'Komite Teknis' }}"
                                                              data-has-keputusan="true">
                                                            @csrf
                                                            <button type="button" class="btn bg-gradient-success btn-sm btn-terbitkan-sertifikat">
                                                                <i class="fas fa-certificate me-1"></i> Terbitkan Sertifikat
                                                            </button>
                                                        </form>
                                                    @else
                                                        {{-- Keputusan belum ada - tombol disabled dengan tooltip --}}
                                                        <button type="button" 
                                                                class="btn btn-secondary btn-sm" 
                                                                disabled
                                                                data-bs-toggle="tooltip" 
                                                                data-bs-placement="top"
                                                                data-bs-html="true"
                                                                title="<strong>Keputusan Sertifikasi Belum Ditetapkan</strong><br/>Sertifikat hanya dapat diterbitkan setelah Komite Teknis menetapkan keputusan kompetensi.">
                                                            <i class="fas fa-exclamation-triangle me-1"></i> Belum Dapat Diterbitkan
                                                        </button>
                                                    @endif
                                                @else
                                                    <span class="badge bg-secondary" title="Anda tidak memiliki izin untuk menerbitkan sertifikat">
                                                        <i class="fas fa-lock me-1"></i> Menunggu
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="fas fa-clipboard-check fa-4x text-muted mb-3"></i>
                                                    <h6 class="text-muted mb-2">Tidak Ada Data Siap Diterbitkan</h6>
                                                    <p class="text-sm text-secondary mb-0 px-4" style="max-width: 600px;">
                                                        Sertifikat dapat diterbitkan setelah proses asesmen selesai dan Keputusan Kompetensi 
                                                        telah ditetapkan oleh Komite Teknis sesuai alur ISO 17024:
                                                    </p>
                                                    <div class="mt-3">
                                                        <span class="badge bg-info me-2">
                                                            <i class="fas fa-file-alt me-1"></i> Asesmen
                                                        </span>
                                                        <i class="fas fa-arrow-right text-secondary mx-2"></i>
                                                        <span class="badge bg-warning me-2">
                                                            <i class="fas fa-gavel me-1"></i> Keputusan
                                                        </span>
                                                        <i class="fas fa-arrow-right text-secondary mx-2"></i>
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-certificate me-1"></i> Sertifikat
                                                        </span>
                                                    </div>
                                                    <p class="text-xs text-muted mt-3 mb-0">
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        Pastikan status pendaftaran <strong>KOMPETEN FINAL</strong> dan semua data relasi lengkap
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Handle Terbitkan Sertifikat with Professional Modal
    document.querySelectorAll('.btn-terbitkan-sertifikat').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const form = this.closest('.sertifikat-terbit-form');
            const pesertaNama = form.dataset.pesertaNama;
            const nomorPendaftaran = form.dataset.nomorPendaftaran;
            const skema = form.dataset.skema;
            const tanggalKeputusan = form.dataset.tanggalKeputusan;
            const penetap = form.dataset.penetap;
            const hasKeputusan = form.dataset.hasKeputusan === 'true';
            
            // Build keputusan info section if exists
            let keputusanInfoHtml = '';
            if (hasKeputusan && tanggalKeputusan && tanggalKeputusan !== '-') {
                keputusanInfoHtml = `
                    <hr class="my-3">
                    <div class="row mb-2">
                        <div class="col-12">
                            <p class="text-success text-sm mb-2">
                                <i class="fas fa-check-circle me-1"></i>
                                <strong>Keputusan Kompetensi Telah Ditetapkan</strong>
                            </p>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-secondary text-sm">
                            <i class="fas fa-calendar-check me-1"></i> Tanggal
                        </div>
                        <div class="col-7">
                            <strong class="text-sm">${tanggalKeputusan}</strong>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-5 text-secondary text-sm">
                            <i class="fas fa-user-tie me-1"></i> Penetap
                        </div>
                        <div class="col-7">
                            <strong class="text-sm">${penetap}</strong>
                        </div>
                    </div>
                `;
            }
            
            Swal.fire({
                title: '<strong>Terbitkan Sertifikat?</strong>',
                html: `
                    <div class="text-start">
                        <p class="text-muted mb-3">
                            <i class="fas fa-exclamation-circle text-warning me-1"></i>
                            Sertifikat akan diterbitkan secara resmi dan <strong>tidak dapat dibatalkan</strong>.
                        </p>
                        <div class="card bg-light border-0 mb-0">
                            <div class="card-body py-3">
                                <div class="row mb-2">
                                    <div class="col-5 text-secondary text-sm">
                                        <i class="fas fa-user me-1"></i> Nama Asesi
                                    </div>
                                    <div class="col-7">
                                        <strong class="text-sm">${pesertaNama}</strong>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-5 text-secondary text-sm">
                                        <i class="fas fa-id-card me-1"></i> No. Pendaftaran
                                    </div>
                                    <div class="col-7">
                                        <strong class="text-sm">${nomorPendaftaran}</strong>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-5 text-secondary text-sm">
                                        <i class="fas fa-certificate me-1"></i> Skema
                                    </div>
                                    <div class="col-7">
                                        <strong class="text-sm">${skema}</strong>
                                    </div>
                                </div>
                                ${keputusanInfoHtml}
                            </div>
                        </div>
                    </div>
                `,
                icon: 'warning',
                iconColor: '#f39c12',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-check-circle me-1"></i> Ya, Terbitkan Sertifikat',
                cancelButtonText: '<i class="fas fa-times me-1"></i> Batal',
                customClass: {
                    confirmButton: 'btn bg-gradient-success btn-lg px-4 me-2',
                    cancelButton: 'btn bg-gradient-secondary btn-lg px-4',
                    popup: 'swal-wide'
                },
                buttonsStyling: false,
                reverseButtons: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                focusConfirm: false,
                width: '650px',
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    Swal.fire({
                        title: 'Memproses...',
                        html: 'Sedang menerbitkan sertifikat. Mohon tunggu sebentar.',
                        icon: 'info',
                        iconColor: '#17c1e8',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Submit form
                    form.submit();
                }
            });
        });
    });
});
</script>

<style>
/* Custom Modal Styling for Professional Look */
.swal-wide {
    border-radius: 1rem !important;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15) !important;
}

.swal2-popup .swal2-title {
    color: #344767;
    font-size: 1.5rem;
    font-weight: 700;
    padding: 1rem 1.5rem 0.5rem;
}

.swal2-popup .swal2-html-container {
    color: #67748e;
    font-size: 0.9rem;
    padding: 0.5rem 1.5rem 1.5rem;
}

.swal2-popup .card {
    box-shadow: none !important;
}

/* Button Hover Effects */
.btn.bg-gradient-success:hover {
    transform: scale(1.02);
    box-shadow: 0 8px 26px -4px rgba(20, 164, 77, 0.5), 0 8px 9px -5px rgba(20, 164, 77, 0.3);
}

.btn.bg-gradient-secondary:hover {
    transform: scale(1.02);
}

/* Animation for modal */
.swal2-show {
    animation: swal2-show 0.3s;
}

@keyframes swal2-show {
    from {
        transform: scale(0.7);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}
</style>
@endpush

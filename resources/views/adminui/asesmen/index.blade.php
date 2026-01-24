@extends('adminui.layouts.auth')

@section('title', 'Asesmen' . (systemCompanyName() ? ' - ' . systemCompanyName() . ' Admin' : ' - Admin'))

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Alert Messages -->
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

            @if(session('info'))
                <div class="alert alert-info alert-dismissible" role="alert">
                    <span class="text-white">{{ session('info') }}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs mb-3" id="asesmenTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="siap-tab" data-bs-toggle="tab" data-bs-target="#siap-asesmen" 
                            type="button" role="tab" aria-controls="siap-asesmen" aria-selected="true">
                        <i class="fas fa-clipboard-list me-1"></i> Siap Asesmen
                        @if($pendaftarans->total() > 0)
                            <span class="badge bg-warning ms-1">{{ $pendaftarans->total() }}</span>
                        @endif
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="riwayat-tab" data-bs-toggle="tab" data-bs-target="#riwayat-asesmen" 
                            type="button" role="tab" aria-controls="riwayat-asesmen" aria-selected="false">
                        <i class="fas fa-history me-1"></i> Riwayat Asesmen
                        @if($asesmens->total() > 0)
                            <span class="badge bg-success ms-1">{{ $asesmens->total() }}</span>
                        @endif
                    </button>
                </li>
            </ul>

            <!-- Tabs Content -->
            <div class="tab-content" id="asesmenTabsContent">
                <!-- Tab: Siap Asesmen -->
                <div class="tab-pane fade show active" id="siap-asesmen" role="tabpanel" aria-labelledby="siap-tab">
                    <div class="card mb-4">
                        <div class="card-header pb-0">
                            <h6>Daftar Pendaftaran Siap Asesmen</h6>
                            <p class="text-sm mb-0">Kelola asesmen untuk pendaftaran yang siap dinilai</p>
                        </div>
                        <div class="card-body px-0 pt-0 pb-2">
                            <!-- Search -->
                            <div class="px-4 pt-3 pb-2">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-6">
                                        <input type="text" name="search" class="form-control" 
                                               placeholder="Cari kode pendaftaran, nama asesi, atau skema..." 
                                               value="{{ request('search') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <button type="submit" class="btn bg-gradient-primary btn-sm">
                                            <i class="fas fa-search"></i> Cari
                                        </button>
                                        <a href="{{ route('adminui.asesmen.index') }}" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-times"></i> Reset
                                        </a>
                                    </div>
                                </form>
                            </div>

                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No. Pendaftaran</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Asesi</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Skema Sertifikasi</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tgl Daftar</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($pendaftarans as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-3 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm font-weight-bold">{{ $item->nomor_pendaftaran }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0">{{ $item->asesi_name }}</p>
                                                <p class="text-xs text-secondary mb-0">{{ $item->asesi_email }}</p>
                                            </td>
                                            <td>
                                                <span class="badge badge-sm bg-gradient-info">
                                                    {{ $item->skemaSertifikasi->kode_skema ?? '-' }}
                                                </span>
                                                <p class="text-xs text-secondary mb-0 mt-1">{{ $item->skemaSertifikasi->nama_skema ?? '-' }}</p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-sm">{{ $item->tanggal_daftar->format('d M Y') }}</span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <a href="{{ route('adminui.asesmen.mulai', $item->id) }}" 
                                                   class="btn bg-gradient-warning btn-sm" title="Mulai Asesmen">
                                                    <i class="fas fa-clipboard-check me-1"></i> Mulai Asesmen
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4">
                                                <i class="fas fa-inbox text-secondary fa-3x mb-3"></i>
                                                <p class="text-sm mb-0">Tidak ada pendaftaran yang siap untuk asesmen.</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            @if($pendaftarans->hasPages())
                            <div class="px-4 pt-3">
                                {{ $pendaftarans->links() }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Tab: Riwayat Asesmen -->
                <div class="tab-pane fade" id="riwayat-asesmen" role="tabpanel" aria-labelledby="riwayat-tab">
                    <div class="card mb-4">
                        <div class="card-header pb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>Riwayat Asesmen</h6>
                                    <p class="text-sm mb-0">Daftar asesmen yang sudah dilakukan</p>
                                </div>
                                @can('sampling.view')
                                <div>
                                    {{-- Sampling Filter --}}
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('adminui.asesmen.index') }}#riwayat-asesmen" 
                                           class="btn btn-sm {{ !request('sampling') ? 'bg-gradient-primary' : 'btn-outline-primary' }}">
                                            Semua
                                        </a>
                                        <a href="{{ route('adminui.asesmen.index', ['sampling' => 'sampled']) }}#riwayat-asesmen" 
                                           class="btn btn-sm {{ request('sampling') === 'sampled' ? 'bg-gradient-info' : 'btn-outline-info' }}">
                                            <i class="fas fa-clipboard-check me-1"></i> Sampling
                                        </a>
                                        <a href="{{ route('adminui.asesmen.index', ['sampling' => 'not_sampled']) }}#riwayat-asesmen" 
                                           class="btn btn-sm {{ request('sampling') === 'not_sampled' ? 'bg-gradient-secondary' : 'btn-outline-secondary' }}">
                                            Non-Sampling
                                        </a>
                                    </div>
                                </div>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body px-0 pt-0 pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No. Pendaftaran</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Asesi</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Skema</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tanggal Asesmen</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Hasil</th>
                                            @can('sampling.view')
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Sampling</th>
                                            @endcan
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Asesor</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($asesmens as $asesmen)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-3 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm font-weight-bold">{{ $asesmen->pendaftaran->nomor_pendaftaran ?? '-' }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0">{{ $asesmen->pendaftaran->asesi_name ?? '-' }}</p>
                                                <p class="text-xs text-secondary mb-0">{{ $asesmen->pendaftaran->asesi_email ?? '-' }}</p>
                                            </td>
                                            <td>
                                                <span class="badge badge-sm bg-gradient-info">
                                                    {{ $asesmen->pendaftaran->skemaSertifikasi->kode_skema ?? '-' }}
                                                </span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-sm">{{ $asesmen->tanggal_asesmen->format('d M Y') }}</span>
                                            </td>
                                            <td class="align-middle text-center">
                                                @if($asesmen->isAllKompeten())
                                                    <span class="badge badge-sm bg-gradient-success">
                                                        <i class="fas fa-check me-1"></i> KOMPETEN
                                                    </span>
                                                @else
                                                    <span class="badge badge-sm bg-gradient-danger">
                                                        <i class="fas fa-times me-1"></i> BELUM KOMPETEN
                                                    </span>
                                                @endif
                                            </td>
                                            @can('sampling.view')
                                            <td class="align-middle text-center">
                                                @if($asesmen->isSampled())
                                                    <span class="badge badge-sm bg-gradient-info" title="Ditandai: {{ $asesmen->sampled_at?->format('d M Y') }}">
                                                        <i class="fas fa-clipboard-check me-1"></i> SAMPLING
                                                    </span>
                                                @else
                                                    <span class="text-xs text-muted">-</span>
                                                @endif
                                            </td>
                                            @endcan
                                            <td class="align-middle text-center">
                                                <span class="text-sm">{{ $asesmen->asesor->name ?? '-' }}</span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <a href="{{ route('adminui.asesmen.show', $asesmen->id) }}" 
                                                   class="btn bg-gradient-info btn-sm" title="Lihat Detail">
                                                    <i class="fas fa-eye me-1"></i> Detail
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="{{ auth()->user()->can('sampling.view') ? 8 : 7 }}" class="text-center py-4">
                                                <i class="fas fa-inbox text-secondary fa-3x mb-3"></i>
                                                <p class="text-sm mb-0">
                                                    @if(request('sampling'))
                                                        Tidak ada data asesmen {{ request('sampling') === 'sampled' ? 'sampling' : 'non-sampling' }}.
                                                    @else
                                                        Belum ada riwayat asesmen.
                                                    @endif
                                                </p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            @if($asesmens->hasPages())
                            <div class="px-4 pt-3">
                                {{ $asesmens->appends(['asesmen_page' => request('asesmen_page')])->links() }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.nav-tabs .nav-link {
    color: #344767;
    font-weight: 600;
    border: none;
    padding: 12px 20px;
}
.nav-tabs .nav-link.active {
    color: #5e72e4;
    border-bottom: 2px solid #5e72e4;
    background: transparent;
}
.nav-tabs .nav-link:hover:not(.active) {
    color: #5e72e4;
    border-color: transparent;
}
</style>
@endpush

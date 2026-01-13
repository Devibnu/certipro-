@extends('adminui.layouts.auth')

@section('title', 'Keputusan Sertifikasi' . (systemCompanyName() ? ' - ' . systemCompanyName() . ' Admin' : ' - Admin'))

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h6>Keputusan Sertifikasi</h6>
                        <p class="text-sm mb-0">Review hasil asesmen dan tetapkan keputusan akhir</p>
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

                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible mx-4 mt-3" role="alert">
                            <span class="text-white">{{ session('info') }}</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Search -->
                    <div class="px-4 pt-3 pb-2">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Cari nomor pendaftaran, nama asesi, atau skema..." 
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn bg-gradient-primary btn-sm">
                                    <i class="fas fa-search"></i> Cari
                                </button>
                                <a href="{{ route('adminui.keputusan.index') }}" class="btn btn-outline-secondary btn-sm">
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
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Asesor</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tgl Asesmen</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
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
                                        <p class="text-sm font-weight-bold mb-0">{{ $item->user->name ?? '-' }}</p>
                                        <p class="text-xs text-secondary mb-0">{{ $item->user->email ?? '-' }}</p>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-gradient-info">
                                            {{ $item->skemaSertifikasi->kode_skema ?? '-' }}
                                        </span>
                                        <p class="text-xs text-secondary mb-0 mt-1">{{ $item->skemaSertifikasi->nama_skema ?? '-' }}</p>
                                    </td>
                                    <td>
                                        <p class="text-sm mb-0">{{ $item->asesmen->asesor->name ?? '-' }}</p>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-sm">{{ $item->asesmen->tanggal_asesmen ? $item->asesmen->tanggal_asesmen->format('d M Y') : '-' }}</span>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <span class="badge badge-sm {{ $item->status_badge }}">
                                            {{ $item->status_label }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <a href="{{ route('adminui.keputusan.show', $item->id) }}" 
                                           class="btn bg-gradient-danger btn-sm" title="Review & Keputusan">
                                            <i class="fas fa-gavel me-1"></i> Review
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-inbox text-secondary fa-3x mb-3"></i>
                                        <p class="text-sm mb-0">Tidak ada pendaftaran yang menunggu keputusan.</p>
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
    </div>
</div>
@endsection

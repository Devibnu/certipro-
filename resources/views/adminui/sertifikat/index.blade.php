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
                                                <form action="{{ route('adminui.sertifikat.terbit', $item->id) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn bg-gradient-success btn-sm" 
                                                            onclick="return confirm('Terbitkan sertifikat untuk {{ $item->user->name ?? 'peserta ini' }}?')">
                                                        <i class="fas fa-certificate me-1"></i> Terbitkan Sertifikat
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <i class="fas fa-hourglass-half fa-3x text-muted mb-3"></i>
                                                <p class="text-muted mb-0">Tidak ada pendaftaran yang siap diterbitkan sertifikatnya</p>
                                                <p class="text-xs text-muted">Pastikan status pendaftaran sudah "Kompeten Final"</p>
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

@extends('adminui.layouts.auth')

@section('title', 'Unit Kompetensi' . (systemCompanyName() ? ' - ' . systemCompanyName() . ' Admin' : ' - Admin'))

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h6>Master Data Unit Kompetensi</h6>
                        <p class="text-sm mb-0">Kelola unit kompetensi berdasarkan skema sertifikasi</p>
                    </div>
                    <a href="{{ route('adminui.unit-kompetensi.create', request()->only('skema_sertifikasi_id')) }}" class="btn bg-gradient-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Unit
                    </a>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible mx-4 mt-3" role="alert">
                            <span class="text-white">{{ session('success') }}</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Filters -->
                    <div class="px-4 pt-3 pb-2">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <select name="skema_sertifikasi_id" class="form-select">
                                    <option value="">Semua Skema</option>
                                    @foreach($skemaList as $skema)
                                        <option value="{{ $skema->id }}" {{ request('skema_sertifikasi_id') == $skema->id ? 'selected' : '' }}>
                                            {{ $skema->kode_skema }} - {{ $skema->nama_skema }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="aktif" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="1" {{ request('aktif') === '1' ? 'selected' : '' }}>Aktif</option>
                                    <option value="0" {{ request('aktif') === '0' ? 'selected' : '' }}>Nonaktif</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Cari kode atau nama unit..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn bg-gradient-primary btn-sm">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('adminui.unit-kompetensi.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-times"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Kode Unit</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama Unit</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Skema</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($unitKompetensi as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex px-3 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm font-weight-bold">{{ $item->kode_unit }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-sm font-weight-bold mb-0">{{ $item->nama_unit }}</p>
                                        @if($item->deskripsi)
                                            <p class="text-xs text-secondary mb-0">{{ Str::limit($item->deskripsi, 60) }}</p>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-gradient-info">
                                            {{ $item->skemaSertifikasi->kode_skema ?? '-' }}
                                        </span>
                                        <p class="text-xs text-secondary mb-0">{{ $item->skemaSertifikasi->nama_skema ?? '-' }}</p>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        @if($item->aktif)
                                            <span class="badge badge-sm bg-gradient-success">Aktif</span>
                                        @else
                                            <span class="badge badge-sm bg-gradient-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                        <a href="{{ route('adminui.unit-kompetensi.edit', $item) }}" 
                                           class="btn btn-link text-dark px-2 mb-0" title="Edit">
                                            <i class="fas fa-pencil-alt text-dark"></i>
                                        </a>
                                        <form action="{{ route('adminui.unit-kompetensi.destroy', $item) }}" 
                                              method="POST" class="d-inline" 
                                              onsubmit="return confirm('Yakin ingin menghapus unit {{ $item->kode_unit }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link text-danger px-2 mb-0" title="Hapus">
                                                <i class="fas fa-trash text-danger"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <p class="text-sm mb-0">
                                            Belum ada unit kompetensi. 
                                            <a href="{{ route('adminui.unit-kompetensi.create', request()->only('skema_sertifikasi_id')) }}">Tambah unit baru</a>
                                        </p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($unitKompetensi->hasPages())
                    <div class="px-4 pt-3">
                        {{ $unitKompetensi->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

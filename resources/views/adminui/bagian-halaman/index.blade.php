@extends('adminui.layouts.auth')

@section('title', 'Kelola Bagian Halaman - CMS Landing Page')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="d-flex align-items-center">
                            <a href="{{ route('adminui.halaman.index') }}" class="btn btn-outline-secondary btn-sm me-3">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                            <h6 class="mb-0">Daftar Bagian Halaman</h6>
                        </div>
                        <div class="d-flex align-items-center mt-2 mt-md-0">
                            <!-- Filter by Halaman -->
                            <form method="GET" action="{{ route('adminui.bagian-halaman.index') }}" class="me-2">
                                <select name="halaman_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">-- Semua Halaman --</option>
                                    @foreach($halamanList as $h)
                                        <option value="{{ $h->id }}" {{ request('halaman_id') == $h->id ? 'selected' : '' }}>
                                            {{ $h->judul }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                            <a href="{{ route('adminui.bagian-halaman.create', request('halaman_id') ? ['halaman_id' => request('halaman_id')] : []) }}" 
                               class="btn bg-gradient-primary btn-sm">
                                <i class="fas fa-plus"></i> Tambah Bagian
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible mx-4 mt-3" role="alert">
                            <span class="text-white">{{ session('success') }}</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 60px;">Urutan</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Judul Bagian</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Halaman</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tipe</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bagianHalaman as $item)
                                <tr>
                                    <td class="align-middle text-center">
                                        <span class="badge bg-gradient-secondary">{{ $item->urutan }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex px-3 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $item->judul }}</h6>
                                                @if($item->isi)
                                                    <p class="text-xs text-secondary mb-0">{{ Str::limit(strip_tags($item->isi), 50) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('adminui.halaman.edit', $item->halaman) }}" class="text-sm font-weight-bold mb-0 text-primary">
                                            {{ $item->halaman->judul ?? '-' }}
                                        </a>
                                    </td>
                                    <td class="align-middle text-center">
                                        @php
                                            $tipeColors = [
                                                'hero' => 'primary',
                                                'teks' => 'info',
                                                'daftar' => 'warning',
                                                'faq' => 'success',
                                            ];
                                            $color = $tipeColors[$item->tipe] ?? 'secondary';
                                        @endphp
                                        <span class="badge badge-sm bg-gradient-{{ $color }}">{{ ucfirst($item->tipe) }}</span>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        @if($item->aktif)
                                            <span class="badge badge-sm bg-gradient-success">Aktif</span>
                                        @else
                                            <span class="badge badge-sm bg-gradient-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                        <a href="{{ route('adminui.bagian-halaman.edit', $item) }}" 
                                           class="btn btn-link text-dark px-3 mb-0" title="Edit">
                                            <i class="fas fa-pencil-alt text-dark me-2"></i>
                                        </a>
                                        <form action="{{ route('adminui.bagian-halaman.destroy', $item) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link text-danger px-3 mb-0" 
                                                    onclick="return confirm('Yakin ingin menghapus bagian ini?')" title="Hapus">
                                                <i class="fas fa-trash text-danger me-2"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <p class="text-sm mb-0">Belum ada bagian halaman. Silakan tambah bagian baru.</p>
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

    <!-- Info Card -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Tipe Bagian yang Tersedia</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-gradient-primary me-2">Hero</span>
                                <small class="text-muted">Banner utama dengan judul besar</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-gradient-info me-2">Teks</span>
                                <small class="text-muted">Konten teks paragraf biasa</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-gradient-warning me-2">Daftar</span>
                                <small class="text-muted">Daftar item dengan ikon</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-gradient-success me-2">FAQ</span>
                                <small class="text-muted">Pertanyaan & jawaban</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

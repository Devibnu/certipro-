@extends('adminui.layouts.auth')

@section('title', 'Kelola Halaman - CMS Landing Page')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>Daftar Halaman</h6>
                    <a href="{{ route('adminui.halaman.create') }}" class="btn bg-gradient-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Halaman
                    </a>
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
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Judul</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Slug</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Bagian</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($halaman as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex px-3 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $item->judul }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-sm font-weight-bold mb-0">{{ $item->slug }}</p>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="badge badge-sm bg-gradient-info">
                                            {{ $item->bagian_halaman_count }} bagian
                                        </span>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        @if($item->aktif)
                                            <span class="badge badge-sm bg-gradient-success">Aktif</span>
                                        @else
                                            <span class="badge badge-sm bg-gradient-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                        <a href="{{ route('adminui.bagian-halaman.index', ['halaman_id' => $item->id]) }}" 
                                           class="btn btn-link text-info px-3 mb-0" title="Kelola Bagian">
                                            <i class="fas fa-list me-2"></i>
                                        </a>
                                        <a href="{{ route('adminui.halaman.edit', $item) }}" 
                                           class="btn btn-link text-dark px-3 mb-0" title="Edit">
                                            <i class="fas fa-pencil-alt text-dark me-2"></i>
                                        </a>
                                        <form action="{{ route('adminui.halaman.destroy', $item) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link text-danger px-3 mb-0" 
                                                    onclick="return confirm('Yakin ingin menghapus?')" title="Hapus">
                                                <i class="fas fa-trash text-danger me-2"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <p class="text-sm mb-0">Belum ada halaman. Silakan tambah halaman baru.</p>
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
@endsection

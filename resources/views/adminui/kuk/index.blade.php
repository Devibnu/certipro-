@extends('adminui.layouts.auth')

@section('title', 'KUK (Kriteria Unjuk Kerja)' . (systemCompanyName() ? ' - ' . systemCompanyName() . ' Admin' : ' - Admin'))

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h6>Master Data KUK (Kriteria Unjuk Kerja)</h6>
                        <p class="text-sm mb-0">Kelola kriteria unjuk kerja berdasarkan unit kompetensi</p>
                    </div>
                    <a href="{{ route('adminui.kuk.create', request()->only(['skema_sertifikasi_id', 'unit_kompetensi_id'])) }}" class="btn bg-gradient-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah KUK
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
                        <form method="GET" class="row g-3" id="filterForm">
                            <div class="col-md-3">
                                <select name="skema_sertifikasi_id" id="filter_skema" class="form-select">
                                    <option value="">Semua Skema</option>
                                    @foreach($skemaList as $skema)
                                        <option value="{{ $skema->id }}" {{ request('skema_sertifikasi_id') == $skema->id ? 'selected' : '' }}>
                                            {{ $skema->kode_skema }} - {{ $skema->nama_skema }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="unit_kompetensi_id" id="filter_unit" class="form-select">
                                    <option value="">Semua Unit</option>
                                    @foreach($unitList as $unit)
                                        <option value="{{ $unit->id }}" {{ request('unit_kompetensi_id') == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->kode_unit }}
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
                            <div class="col-md-2">
                                <input type="text" name="search" class="form-control" placeholder="Cari..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn bg-gradient-primary btn-sm">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('adminui.kuk.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 80px;">Urutan</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Kode KUK</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Pernyataan Unjuk Kerja</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Unit Kompetensi</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kukList as $item)
                                <tr>
                                    <td class="align-middle text-center">
                                        <span class="badge bg-gradient-secondary">{{ $item->urutan }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex px-3 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm font-weight-bold">{{ $item->kode_kuk }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-sm mb-0">{{ Str::limit($item->pernyataan_unjuk_kerja, 80) }}</p>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-gradient-info">
                                            {{ $item->unitKompetensi->kode_unit ?? '-' }}
                                        </span>
                                        <p class="text-xs text-secondary mb-0">{{ $item->unitKompetensi->skemaSertifikasi->kode_skema ?? '-' }}</p>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        @if($item->aktif)
                                            <span class="badge badge-sm bg-gradient-success">Aktif</span>
                                        @else
                                            <span class="badge badge-sm bg-gradient-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                        <a href="{{ route('adminui.kuk.edit', $item) }}" 
                                           class="btn btn-link text-dark px-2 mb-0" title="Edit">
                                            <i class="fas fa-pencil-alt text-dark"></i>
                                        </a>
                                        <form action="{{ route('adminui.kuk.destroy', $item) }}" 
                                              method="POST" class="d-inline" 
                                              onsubmit="return confirm('Yakin ingin menghapus KUK {{ $item->kode_kuk }}?')">
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
                                    <td colspan="6" class="text-center py-4">
                                        <p class="text-sm mb-0">
                                            Belum ada KUK. 
                                            <a href="{{ route('adminui.kuk.create', request()->only(['skema_sertifikasi_id', 'unit_kompetensi_id'])) }}">Tambah KUK baru</a>
                                        </p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($kukList->hasPages())
                    <div class="px-4 pt-3">
                        {{ $kukList->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterSkema = document.getElementById('filter_skema');
    const filterUnit = document.getElementById('filter_unit');
    
    filterSkema.addEventListener('change', function() {
        const skemaId = this.value;
        filterUnit.innerHTML = '<option value="">Semua Unit</option>';
        
        if (skemaId) {
            fetch(`{{ url('adminui/api/unit-kompetensi-by-skema') }}/${skemaId}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(unit => {
                        const option = document.createElement('option');
                        option.value = unit.id;
                        option.textContent = `${unit.kode_unit} - ${unit.nama_unit}`;
                        filterUnit.appendChild(option);
                    });
                });
        }
    });
});
</script>
@endpush
